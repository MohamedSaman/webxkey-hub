<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

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

        // Validate against the bcrypt hash stored in DASHBOARD_PASSWORD env var.
        // www-data runs with NOPASSWD sudo, so no password needs to be piped
        // to sudo for actual server commands.
        $storedHash = config('app.dashboard_password');

        if (!$storedHash || !Hash::check($password, $storedHash)) {
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
