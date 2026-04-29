<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebXKey Server Manager — Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #0f0f0e; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .login-card { background: #1c1c1a; border: 0.5px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 40px; width: 360px; }
        .logo { text-align: center; margin-bottom: 32px; }
        .logo-mark { font-size: 18px; font-weight: 600; color: #fff; letter-spacing: -0.4px; }
        .logo-sub { font-size: 12px; color: #6b6b68; margin-top: 4px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 12px; font-weight: 500; color: #9a9a97; margin-bottom: 6px; }
        .form-input { width: 100%; padding: 10px 12px; font-size: 13px; background: #2a2a28; border: 0.5px solid rgba(255,255,255,0.1); border-radius: 6px; color: #e8e8e5; box-sizing: border-box; outline: none; }
        .form-input:focus { border-color: #378ADD; }
        .btn-login { width: 100%; padding: 10px; background: #185FA5; color: #E6F1FB; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; margin-top: 8px; }
        .btn-login:hover { background: #0C447C; }
        .error-msg { background: rgba(162,45,45,0.15); border: 0.5px solid rgba(162,45,45,0.4); border-radius: 6px; padding: 10px 12px; font-size: 12px; color: #F09595; margin-bottom: 16px; }
        .server-info { text-align: center; margin-top: 24px; font-size: 11px; color: #4a4a47; }
        .remember-row { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #9a9a97; margin-bottom: 16px; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <div class="logo-mark">⬡ WebXKey</div>
            <div class="logo-sub">Server Manager</div>
        </div>

        @if ($errors->any())
            <div class="error-msg">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Email address</label>
                <input class="form-input" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@webxkey.com">
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input class="form-input" type="password" name="password" required placeholder="••••••••">
            </div>
            <label class="remember-row">
                <input type="checkbox" name="remember"> Remember me
            </label>
            <button class="btn-login" type="submit">Sign in →</button>
        </form>

        <div class="server-info">57.159.27.225 · PHP 8.3 · Ubuntu 24</div>
    </div>
</body>
</html>
