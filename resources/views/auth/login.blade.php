<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebXKey — Server Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #0d1117;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
            overflow: hidden;
            position: relative;
        }

        /* Grid pattern */
        .bg-grid {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(79,142,247,0.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(79,142,247,0.045) 1px, transparent 1px);
            background-size: 44px 44px;
            pointer-events: none;
            z-index: 0;
        }

        /* Center glow */
        .bg-glow {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 800px; height: 800px;
            background: radial-gradient(circle at center,
                rgba(79,142,247,0.08) 0%,
                rgba(129,140,248,0.04) 35%,
                transparent 65%);
            pointer-events: none;
            z-index: 0;
        }

        /* Corner accent glows */
        .bg-corner-tl {
            position: fixed;
            top: -200px; left: -200px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(79,142,247,0.06), transparent 60%);
            pointer-events: none; z-index: 0;
        }
        .bg-corner-br {
            position: fixed;
            bottom: -200px; right: -200px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(129,140,248,0.06), transparent 60%);
            pointer-events: none; z-index: 0;
        }

        .login-wrap {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 400px;
            padding: 0 20px;
        }

        /* Online badge */
        .server-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #161b27;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 30px;
            padding: 6px 16px;
            margin-bottom: 36px;
            font-size: 11.5px;
            color: #525a72;
        }

        .pulse {
            width: 7px; height: 7px;
            background: #4ade80;
            border-radius: 50%;
            box-shadow: 0 0 7px #4ade80;
            animation: pulse 2.2s ease-in-out infinite;
            flex-shrink: 0;
        }

        @keyframes pulse {
            0%,100% { opacity:1; box-shadow:0 0 7px #4ade80; }
            50%      { opacity:.4; box-shadow:0 0 2px #4ade80; }
        }

        /* Logo block */
        .logo-block {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-icon {
            width: 60px; height: 60px;
            background: linear-gradient(140deg, #4f8ef7 0%, #818cf8 100%);
            border-radius: 18px;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            box-shadow: 0 0 40px rgba(79,142,247,0.4), 0 8px 24px rgba(0,0,0,0.5);
        }

        .logo-title {
            font-size: 24px;
            font-weight: 700;
            color: #f0f2f7;
            letter-spacing: -0.6px;
        }

        .logo-sub {
            font-size: 13px;
            color: #525a72;
            margin-top: 5px;
            letter-spacing: 0.2px;
        }

        /* Card */
        .card {
            width: 100%;
            background: #161b27;
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 18px;
            padding: 36px;
            box-shadow:
                0 0 0 1px rgba(79,142,247,0.06),
                0 24px 64px rgba(0,0,0,0.6),
                0 8px 24px rgba(0,0,0,0.4);
        }

        .card-title {
            font-size: 15px;
            font-weight: 600;
            color: #f0f2f7;
            margin-bottom: 5px;
            letter-spacing: -0.2px;
        }

        .card-sub {
            font-size: 12.5px;
            color: #525a72;
            margin-bottom: 28px;
            line-height: 1.6;
        }

        /* Error */
        .error {
            background: rgba(248,113,113,0.08);
            border: 1px solid rgba(248,113,113,0.2);
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 12.5px;
            color: #f87171;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Form */
        label {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            color: #8e96b0;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        input[type="password"] {
            width: 100%;
            padding: 13px 16px;
            font-size: 16px;
            background: #0d1117;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            color: #f0f2f7;
            outline: none;
            font-family: monospace;
            letter-spacing: 5px;
            transition: all 0.15s;
            margin-bottom: 20px;
        }

        input[type="password"]:focus {
            border-color: #4f8ef7;
            box-shadow: 0 0 0 3px rgba(79,142,247,0.15);
        }

        input[type="password"]::placeholder {
            color: #252c3d;
            letter-spacing: 4px;
        }

        button[type="submit"] {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4f8ef7 0%, #6366f1 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            letter-spacing: -0.1px;
            transition: all 0.15s;
            box-shadow: 0 4px 20px rgba(79,142,247,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: inherit;
        }

        button[type="submit"]:hover {
            box-shadow: 0 6px 28px rgba(79,142,247,0.55);
            transform: translateY(-1px);
            filter: brightness(1.06);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        /* Bottom tags */
        .tags {
            display: flex;
            gap: 6px;
            justify-content: center;
            margin-top: 22px;
            flex-wrap: wrap;
        }

        .tag {
            background: #161b27;
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 10.5px;
            color: #525a72;
            font-family: monospace;
            letter-spacing: 0.3px;
        }
    </style>
</head>
<body>
    <div class="bg-grid"></div>
    <div class="bg-glow"></div>
    <div class="bg-corner-tl"></div>
    <div class="bg-corner-br"></div>

    <div class="login-wrap">

        <div class="server-pill">
            <div class="pulse"></div>
            57.159.27.225 &nbsp;·&nbsp; online
        </div>

        <div class="logo-block">
            <div class="logo-icon">⬡</div>
            <div class="logo-title">WebXKey</div>
            <div class="logo-sub">Server Manager</div>
        </div>

        <div class="card">
            <div class="card-title">Authenticate</div>
            <div class="card-sub">Enter your sudo password to unlock the dashboard.</div>

            @if($errors->any())
                <div class="error">
                    <span>⚠</span>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('server-auth.store') }}">
                @csrf
                <label>Sudo Password</label>
                <input type="password" name="sudo_password"
                       required autofocus placeholder="••••••••"
                       autocomplete="current-password">
                <button type="submit">
                    <span>Unlock Dashboard</span>
                    <span>→</span>
                </button>
            </form>
        </div>

        <div class="tags">
            <span class="tag">PHP 8.3</span>
            <span class="tag">Laravel 11</span>
            <span class="tag">Nginx</span>
            <span class="tag">Ubuntu 24</span>
            <span class="tag">57.159.27.225</span>
        </div>

    </div>
</body>
</html>
