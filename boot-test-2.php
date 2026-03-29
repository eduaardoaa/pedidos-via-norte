<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

ob_start();

register_shutdown_function(function () {
    $error = error_get_last();

    echo "<pre>";

    if ($error) {
        echo "\n===== FATAL / SHUTDOWN ERROR =====\n";
        print_r($error);
    } else {
        echo "\n===== SHUTDOWN SEM ERROR_GET_LAST =====\n";
    }

    $buffer = ob_get_contents();
    if ($buffer) {
        echo "\n===== BUFFER FINAL =====\n";
        echo $buffer;
    }

    ob_end_flush();
});

echo "Iniciando...\n";

try {
    require __DIR__ . '/vendor/autoload.php';
    echo "1. Autoload OK\n";

    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "2. Bootstrap OK\n";

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "3. Kernel OK\n";

    $request = Illuminate\Http\Request::capture();
    echo "4. Request OK\n";
    echo "URI: " . $request->getRequestUri() . "\n";
    echo "Path: " . $request->path() . "\n";

    $response = $kernel->handle($request);
    echo "5. Handle OK\n";

    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Conteúdo:\n";
    echo $response->getContent();

    $kernel->terminate($request, $response);
    echo "\n6. Terminate OK\n";

} catch (Throwable $e) {
    echo "\n===== EXCEPTION =====\n";
    echo "Mensagem: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . ':' . $e->getLine() . "\n\n";
    echo $e->getTraceAsString();
}