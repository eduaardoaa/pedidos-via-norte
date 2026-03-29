<!doctype html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Faça seu Login - Vianorte</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('assets/imgs/LOGO VIA NORTE.png') }}" type="image/x-icon">

    {{-- Otimização de conexão com CDN --}}
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">

    {{-- Preload dos estilos --}}
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" as="style">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" as="style">
    <link rel="preload" href="{{ asset('css/login.css') }}" as="style">

    {{-- CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>
    <div class="container-tight">
        <div class="text-center mb-4">
            <a href="{{ url('/login') }}" class="navbar-brand navbar-brand-autodark">
                <img
                    src="{{ asset('assets/imgs/LOGO VIA NORTE.png') }}"
                    alt="Logo Via Norte"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                >
            </a>
        </div>

        <h2 class="login-title">
            <i class="bi bi-box-arrow-in-right"></i> Fazer Login
        </h2>

        <div class="login-subtitle">
            Acesse o sistema interno da Vianorte
        </div>

        <form method="POST" action="{{ route('login.attempt') }}" autocomplete="on">
            @csrf

            <div class="mb-3">
                <input
                    type="text"
                    class="form-control"
                    name="cpf"
                    id="cpf"
                    placeholder="CPF"
                    value="{{ old('cpf') }}"
                    maxlength="14"
                    inputmode="numeric"
                    autocomplete="username"
                    required
                    autofocus
                >
            </div>

            <div class="mb-2">
                <div class="input-group input-group-flat">
                    <input
                        type="password"
                        class="form-control"
                        name="password"
                        id="password"
                        placeholder="Senha"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        type="button"
                        class="input-group-text"
                        onclick="togglePassword()"
                        aria-label="Mostrar ou ocultar senha"
                    >
                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>

                @if ($errors->any())
                    <div class="alert-custom alert-danger-custom">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert-custom alert-success-custom">
                        {{ session('success') }}
                    </div>
                @endif
            </div>

            <div class="form-footer mt-3">
                <button type="submit" class="btn btn-login">
                    Entrar
                </button>
            </div>
        </form>
    </div>

    {{-- Se esse footer tiver scripts ou conteúdo pesado, teste deixar comentado --}}
    @include('partials.footer')
    {{-- @include('partials.footer') --}}

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePasswordIcon');

            const isPassword = passwordField.type === 'password';
            passwordField.type = isPassword ? 'text' : 'password';

            toggleIcon.classList.toggle('bi-eye', !isPassword);
            toggleIcon.classList.toggle('bi-eye-slash', isPassword);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const cpfInput = document.getElementById('cpf');

            if (cpfInput) {
                cpfInput.addEventListener('input', function (e) {
                    let value = e.target.value.replace(/\D/g, '').slice(0, 11);

                    if (value.length > 3) value = value.replace(/^(\d{3})(\d)/, '$1.$2');
                    if (value.length > 7) value = value.replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
                    if (value.length > 11) value = value.replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');
                    else value = value.replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d{1,2})$/, '$1.$2.$3-$4');

                    e.target.value = value;
                });
            }
        });
    </script>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        defer
    ></script>
</body>
</html>