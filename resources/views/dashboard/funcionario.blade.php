<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header('Location: /login');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Página do Funcionário</title>
</head>
<body>
    <h1>Página do Funcionário</h1>

    

    <div class="sidebar-bottom">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-logout">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="sidebar-link-text">Sair do sistema</span>
                        </button>
                    </form>
                </div>
</body>
</html>