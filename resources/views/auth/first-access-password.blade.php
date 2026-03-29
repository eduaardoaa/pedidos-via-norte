<!doctype html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Primeiro Acesso - Vianorte</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('assets/imgs/LOGO VIA NORTE.png') }}" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">

    <style>
        .password-group {
            position: relative;
        }

        .password-group .form-control {
            padding-right: 48px;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #9ca3af;
            font-size: 1.1rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }

        .toggle-password:hover {
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container-tight">
        <div class="text-center mb-4">
            <a href="#" class="navbar-brand navbar-brand-autodark">
                <img src="{{ asset('assets/imgs/LOGO VIA NORTE.png') }}" alt="Logo Via Norte">
            </a>
        </div>

        <h2 class="login-title"><i class="bi bi-shield-lock"></i> Primeiro acesso</h2>
        <div class="login-subtitle">
            Por segurança, defina uma nova senha para continuar.
        </div>

        <form method="POST" action="{{ route('password.first_access.update') }}">
            @csrf

            <div class="mb-3">
                <div class="password-group">
                    <input
                        type="password"
                        class="form-control"
                        name="password"
                        id="password"
                        placeholder="Nova senha"
                        required
                    >
                    <button type="button" class="toggle-password" data-target="password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-2">
                <div class="password-group">
                    <input
                        type="password"
                        class="form-control"
                        name="password_confirmation"
                        id="password_confirmation"
                        placeholder="Confirmar nova senha"
                        required
                    >
                    <button type="button" class="toggle-password" data-target="password_confirmation">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                @if ($errors->any())
                    <div class="alert-custom alert-danger-custom">
                        {{ $errors->first() }}
                    </div>
                @endif
            </div>

            <div class="form-footer mt-3">
                <button type="submit" class="btn btn-login">
                    Salvar nova senha
                </button>
            </div>
        </form>
    </div>

    @include('partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.querySelectorAll('.toggle-password').forEach(function(button) {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });
    </script>
</body>
</html>