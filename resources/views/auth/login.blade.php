<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ config('adminlte.title', config('app.name')) }}</title>
    @php
        $logoPath = \App\Models\Setting::get('logo_path');
        $faviconPath = \App\Models\Setting::get('favicon_path');
        $logoSrc = $logoPath ? asset('storage/' . $logoPath) : asset('img/default-logo.svg');
        $faviconSrc = $faviconPath ? asset('storage/' . $faviconPath) : asset('img/default-favicon.svg');
        $corPrimaria = \App\Models\Setting::get('cor_primaria', '#1e3a5f');
    @endphp
    <link rel="icon" href="{{ $faviconSrc }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, {{ $corPrimaria }} 0%, #0f1f33 100%);
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-logo img {
            max-height: 64px;
        }

        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .login-card-header {
            padding: 32px 32px 0;
            text-align: center;
        }

        .login-card-header h1 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 6px;
        }

        .login-card-header p {
            font-size: 0.88rem;
            color: #6b7280;
        }

        .login-card-body {
            padding: 28px 32px 32px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .input-wrap input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: inherit;
            color: #1f2937;
            background: #f9fafb;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .input-wrap input:focus {
            border-color: {{ $corPrimaria }};
            background: #fff;
            box-shadow: 0 0 0 3px {{ $corPrimaria }}22;
        }

        .input-wrap input:focus + i,
        .input-wrap input:focus ~ i { color: {{ $corPrimaria }}; }

        /* Fix: icon is before input in DOM, use sibling not + */
        .input-wrap:focus-within i { color: {{ $corPrimaria }}; }

        .input-wrap input.is-invalid {
            border-color: #ef4444;
        }

        .invalid-feedback {
            display: block;
            font-size: 0.78rem;
            color: #ef4444;
            margin-top: 4px;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            font-size: 0.85rem;
        }

        .remember-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .remember-wrap input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: {{ $corPrimaria }};
            cursor: pointer;
        }

        .remember-wrap label {
            color: #4b5563;
            cursor: pointer;
            user-select: none;
            margin: 0;
        }

        .forgot-link {
            color: {{ $corPrimaria }};
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.2s;
        }

        .forgot-link:hover { opacity: 0.75; }

        .btn-login {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 10px;
            background: {{ $corPrimaria }};
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            filter: brightness(1.15);
            box-shadow: 0 4px 16px {{ $corPrimaria }}55;
        }

        .btn-login:active {
            transform: scale(0.98);
        }

        .login-footer {
            text-align: center;
            margin-top: 24px;
        }

        .login-footer a {
            color: rgba(255,255,255,0.7);
            font-size: 0.82rem;
            text-decoration: none;
            transition: color 0.2s;
        }

        .login-footer a:hover { color: #fff; }

        /* Alert de erro */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        @media (max-width: 480px) {
            .login-card-body { padding: 24px 20px 28px; }
            .login-card-header { padding: 28px 20px 0; }
        }
    </style>
</head>
<body>
    <div class="login-container">

        <div class="login-logo">
            <img src="{{ $logoSrc }}" alt="Logo"
                 onerror="this.style.display='none'">
        </div>

        <div class="login-card">
            <div class="login-card-header">
                <h1>Bem-vindo de volta</h1>
                <p>Entre com suas credenciais para acessar o painel</p>
            </div>

            <div class="login-card-body">

                @if(session('status'))
                    <div class="alert-error" style="background:#f0fdf4;border-color:#bbf7d0;color:#166534">
                        <i class="fas fa-check-circle"></i> {{ session('status') }}
                    </div>
                @endif

                <form action="{{ url('login') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <div class="input-wrap">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email"
                                   class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                                   value="{{ old('email') }}"
                                   placeholder="seu@email.com"
                                   autofocus required>
                        </div>
                        @error('email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Senha</label>
                        <div class="input-wrap">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password"
                                   class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                                   placeholder="••••••••"
                                   required>
                        </div>
                        @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-options">
                        <div class="remember-wrap">
                            <input type="checkbox" id="remember" name="remember" checked>
                            <label for="remember">Lembrar-me</label>
                        </div>
                        <a href="{{ url('forgot-password') }}" class="forgot-link">Esqueci a senha</a>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        Entrar
                    </button>
                </form>
            </div>
        </div>

        <div class="login-footer">
            <a href="{{ url('/') }}"><i class="fas fa-arrow-left" style="margin-right:4px"></i> Voltar ao site</a>
        </div>
    </div>
</body>
</html>
