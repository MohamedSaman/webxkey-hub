<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebXKey Server Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #0f0f0e; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .card { background: #1c1c1a; border: 0.5px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 40px; width: 360px; }
        .logo { text-align: center; margin-bottom: 32px; }
        .logo-mark { font-size: 20px; font-weight: 600; color: #fff; letter-spacing: -0.4px; }
        .logo-sub { font-size: 12px; color: #6b6b68; margin-top: 4px; }
        .server-badge { display: flex; align-items: center; justify-content: center; gap: 6px; margin-bottom: 24px; }
        .server-dot { width: 7px; height: 7px; background: #639922; border-radius: 50%; animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:0.5; } }
        .server-text { font-size: 11px; color: #6b6b68; }
        .form-label { display: block; font-size: 12px; font-weight: 500; color: #9a9a97; margin-bottom: 8px; }
        .form-input { width: 100%; padding: 11px 14px; font-size: 14px; background: #2a2a28; border: 0.5px solid rgba(255,255,255,0.12); border-radius: 7px; color: #e8e8e5; box-sizing: border-box; outline: none; font-family: monospace; letter-spacing: 2px; }
        .form-input:focus { border-color: #378ADD; letter-spacing: normal; }
        .btn-submit { width: 100%; padding: 11px; background: #185FA5; color: #E6F1FB; border: none; border-radius: 7px; font-size: 13px; font-weight: 500; cursor: pointer; margin-top: 12px; }
        .btn-submit:hover { background: #0C447C; }
        .error-msg { background: rgba(162,45,45,0.15); border: 0.5px solid rgba(162,45,45,0.4); border-radius: 7px; padding: 10px 13px; font-size: 12px; color: #F09595; margin-bottom: 16px; }
        .hint { font-size: 11px; color: #4a4a47; text-align: center; margin-top: 20px; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <div class="logo-mark">⬡ WebXKey</div>
            <div class="logo-sub">Server Manager</div>
        </div>

        <div class="server-badge">
            <div class="server-dot"></div>
            <span class="server-text">57.159.27.225 · PHP 8.3 · Ubuntu 24</span>
        </div>

        @if ($errors->any())
            <div class="error-msg">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('server-auth.store') }}">
            @csrf
            <label class="form-label">Server sudo password</label>
            <input class="form-input" type="password" name="sudo_password" required autofocus
                   placeholder="••••••••" autocomplete="current-password">
            <button class="btn-submit" type="submit">Unlock Server →</button>
        </form>

        <div class="hint">
            Enter your server's sudo password to access<br>
            the management dashboard.
        </div>
    </div>
</body>
</html>
