<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<pre>";

try {
    require __DIR__ . '/vendor/autoload.php';
    echo "1. Autoload OK\n";

    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "2. Bootstrap OK\n";

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "3. Kernel OK\n";

    $request = Illuminate\Http\Request::capture();
    echo "4. Request OK\n";

    $response = $kernel->handle($request);
    echo "5. Response gerada OK\n\n";

    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Conteúdo:\n";
    echo $response->getContent();

    $kernel->terminate($request, $response);
} catch (Throwable $e) {
    echo "\n===== ERRO CAPTURADO =====\n";
    echo "Mensagem: " . $e->getMessage() . "\n\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n\n";
    echo "Trace:\n" . $e->getTraceAsString();
}