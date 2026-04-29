<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ServerAuthController extends Controller
{
    public function show()
    {
        // Already unlocked — go straight to dashboard
        if (session()->has('server_sudo_password')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sudo_password' => ['required', 'string'],
        ]);

        $password = $request->input('sudo_password');
        $safePass = escapeshellarg($password);

        // Verify by running a harmless privileged command via proc_open so the
        // password is properly written to sudo's stdin (shell_exec pipes do not
        // reliably feed sudo -S in a non-interactive PHP context).
        $process = proc_open(
            "sudo -S -p '' whoami",
            [
                0 => ['pipe', 'r'],  // stdin  – we write the password here
                1 => ['pipe', 'w'],  // stdout – "root" on success
                2 => ['pipe', 'w'],  // stderr – sudo error messages
            ],
            $pipes
        );

        if (!is_resource($process)) {
            return back()->withErrors([
                'sudo_password' => 'Server error: could not spawn sudo process.',
            ]);
        }

        fwrite($pipes[0], $password . "\n");
        fclose($pipes[0]);

        $stdout = trim(stream_get_contents($pipes[1]));
        $stderr = trim(stream_get_contents($pipes[2]));
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        \Log::debug('ServerAuth sudo check', ['stdout' => $stdout, 'stderr' => $stderr]);

        if ($stdout !== 'root') {
            return back()->withErrors([
                'sudo_password' => 'Incorrect server password. Please try again.',
            ]);
        }

        session(['server_sudo_password' => Crypt::encryptString($password)]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        session()->forget('server_sudo_password');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
