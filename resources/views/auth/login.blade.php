<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Monitoring Izin Keluar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --ink: #20252b;
            --coral: #ef6666;
            --paper: #f5f6f8;
        }

        body {
            min-height: 100vh;
            margin: 0;
            color: var(--ink);
            background: linear-gradient(135deg, #f9fafb 0%, #f2d8d3 100%);
            font-family: 'Segoe UI', sans-serif;
        }

        .login-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .login-panel {
            width: min(100%, 920px);
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 24px 60px rgba(32, 37, 43, .16);
        }

        .login-intro {
            padding: 56px 42px;
            color: #fff;
            background: var(--ink);
        }

        .login-intro i {
            color: var(--coral);
            font-size: 42px;
            margin-bottom: 28px;
        }

        .login-intro h1 {
            font-size: clamp(2rem, 4vw, 3.2rem);
            line-height: 1;
            margin-bottom: 18px;
        }

        .login-form {
            padding: 56px 48px;
            background: var(--paper);
        }

        .login-form h2 {
            font-size: 1.8rem;
            margin-bottom: 8px;
        }

        .form-control {
            min-height: 48px;
            border: 1px solid #d8dce1;
        }

        .form-control:focus {
            border-color: var(--coral);
            box-shadow: 0 0 0 .2rem rgba(239, 102, 102, .18);
        }

        .btn-login {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;

            width: 100%;
            height: 40px;

            background-color: #ef6666 !important;
            color: #fff !important;
            border: none !important;
            border-radius: 8px;

            font-size: 14px;
            font-weight: 600;
            cursor: pointer;

            box-shadow: 0 4px 0 #c94b4b !important;

            transition:
                background-color .2s ease,
                transform .2s ease,
                box-shadow .2s ease;
        }

        .btn-login:hover {
            background-color: #f47f7f !important;
            color: #fff !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #c94b4b !important;
        }

        .btn-login:active {
            background-color: #d95353 !important;
            color: #fff !important;
            transform: translateY(2px);
            box-shadow: 0 2px 0 #c94b4b !important;
        }

        .btn-login:focus {
            outline: none !important;
            color: #fff !important;
            box-shadow:
                0 4px 0 #c94b4b,
                0 0 0 3px rgba(239, 102, 102, .25) !important;
        }

        .login-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 15px;
        }

        .logo-image1 {
            width: 350px;
            height: 350px;
            object-fit: contain;
            display: block;
        }        

        @media (max-width: 700px) {
            .login-panel { grid-template-columns: 1fr; }
            .login-intro, .login-form { padding: 36px 28px; }
            .login-intro h1 { font-size: 2.3rem; }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <section class="login-panel">
            <div class="login-intro">
                <div class="login-logo">        
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-image1">
                </div>
                <p class="mb-0">Masuk untuk mengelola monitoring izin keluar masuk pegawai.</p>
            </div>

            <div class="login-form">
                <h2>Masuk ke akun</h2>
                <p class="text-secondary mb-4">Gunakan username dan password Anda.</p>

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}" autocomplete="off">
                    @csrf

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input id="username" type="text" name="username"
                            class="form-control @error('username') is-invalid @enderror"
                            value="{{ old('username') }}" autocomplete="off" required autofocus>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            autocomplete="new-password" required>
                    </div>

                    <div class="form-check mb-4">
                        <input id="remember" type="checkbox" name="remember" value="1" class="form-check-input">
                        <label for="remember" class="form-check-label">Ingat saya</label>
                    </div>

                    <button type="submit" class="btn btn-login w-100">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Masuk
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>