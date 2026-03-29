<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<pre>";

try {
    $env = parse_ini_file(__DIR__ . '/.env');

    $host = $env['DB_HOST'] ?? '127.0.0.1';
    $port = $env['DB_PORT'] ?? '3306';
    $db   = $env['DB_DATABASE'] ?? '';
    $user = $env['DB_USERNAME'] ?? '';
    $pass = $env['DB_PASSWORD'] ?? '';

    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass);
    echo "Conexão com banco OK";
} catch (Throwable $e) {
    echo "Erro no banco:\n";
    echo $e->getMessage();
}