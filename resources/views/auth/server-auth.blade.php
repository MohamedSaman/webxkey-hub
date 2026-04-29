<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebXKey — Server Authentication</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #0f0f0e; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .card { background: #1c1c1a; border: 0.5px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 40px; width: 380px; }
        .logo { text-align: center; margin-bottom: 28px; }
        .logo-mark { font-size: 18px; font-weight: 600; color: #fff; letter-spacing: -0.4px; }
        .logo-sub { font-size: 12px; color: #6b6b68; margin-top: 4px; }
        .step-indicator { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; padding: 10px 14px; background: rgba(255,255,255,0.04); border-radius: 8px; }
        .step-done { width: 20px; height: 20px; background: #3B6D11; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #EAF3DE; flex-shrink: 0; }
        .step-active { width: 20px; height: 20px; background: #185FA5; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #E6F1FB; flex-shrink: 0; }
        .step-line { flex: 1; height: 1px; background: rgba(255,255,255,0.1); }
        .step-text { font-size: 12px; color: #9a9a97; }
        .section-title { font-size: 14px; font-weight: 500; color: #fff; margin-bottom: 6px; }
        .section-sub { font-size: 12px; color: #6b6b68; margin-bottom: 20px; line-height: 1.5; }
        .form-label { display: block; font-size: 12px; font-weight: 500; color: #9a9a97; margin-bottom: 6px; }
        .form-input { width: 100%; padding: 10px 12px; font-size: 13px; background: #2a2a28; border: 0.5px solid rgba(255,255,255,0.1); border-radius: 6px; color: #e8e8e5; box-sizing: border-box; outline: none; font-family: monospace; }
        .form-input:focus { border-color: #378ADD; }
        .btn-login { width: 100%; padding: 10px; background: #185FA5; color: #E6F1FB; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; margin-top: 8px; }
        .btn-login:hover { background: #0C447C; }
        .error-msg { background: rgba(162,45,45,0.15); border: 0.5px solid rgba(162,45,45,0.4); border-radius: 6px; padding: 10px 12px; font-size: 12px; color: #F09595; margin-bottom: 16px; }
        .info-box { background: rgba(24,95,165,0.12); border: 0.5px solid rgba(24,95,165,0.3); border-radius: 6px; padding: 10px 12px; font-size: 11px; color: #85B7EB; margin-bottom: 20px; line-height: 1.6; }
        .logout-link { text-align: center; margin-top: 20px; }
        .logout-link a { font-size: 11px; color: #4a4a47; text-decoration: none; }
        .logout-link a:hover { color: #9a9a97; }
        .server-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(99,153,34,0.12); border: 0.5px solid rgba(99,153,34,0.3); border-radius: 20px; padding: 3px 10px; font-size: 11px; color: #9cc95a; margin-bottom: 20px; }
        .server-dot { width: 6px; height: 6px; background: #639922; border-radius: 50%; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <div class="logo-mark">⬡ WebXKey</div>
            <div class="logo-sub">Server Manager</div>
        </div>

        {{-- Step indicator --}}
        <div class="step-indicator">
            <div class="step-done">✓</div>
            <span class="step-text" style="color:#3B6D11;font-size:11px;">Web Login</span>
            <div class="step-line"></div>
            <div class="step-active">2</div>
            <span class="step-text">Server Auth</span>
        </div>

        <div class="server-badge">
            <div class="server-dot"></div>
            57.159.27.225 · PHP 8.3 · Ubuntu 24
        </div>

        <div class="section-title">Server Password</div>
        <div class="section-sub">Enter your server's sudo password. It's stored encrypted in your session and used to run deployment commands.</div>

        @if ($errors->any())
            <div class="error-msg">{{ $errors->first() }}</div>
        @endif

        <div class="info-box">
            🔐 This password is never saved to the database. It lives only in your browser session and is cleared when you log out.
        </div>

        <form method="POST" action="{{ route('server-auth.store') }}">
            @csrf
            <div style="margin-bottom:16px;">
                <label class="form-label">Sudo / root password</label>
                <input class="form-input" type="password" name="sudo_password" required autofocus placeholder="Enter server sudo password">
            </div>
            <button class="btn-login" type="submit">Unlock Server →</button>
        </form>

        <div class="logout-link">
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" style="background:none;border:none;cursor:pointer;font-size:11px;color:#4a4a47;">← Sign out of web app</button>
            </form>
        </div>
    </div>
</body>
</html>
