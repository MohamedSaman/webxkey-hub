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

        // Verify by running a harmless privileged command
        $output = shell_exec("echo {$safePass} | sudo -S -p '' whoami 2>&1");
        $result = trim($output ?? '');

        \Log::debug('ServerAuth sudo check', ['output' => $output, 'result' => $result]);

        if ($result !== 'root') {
            return back()->withErrors([
                'sudo_password' => 'Incorrect server password. Please try again. [debug: ' . addslashes($result) . ']',
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
