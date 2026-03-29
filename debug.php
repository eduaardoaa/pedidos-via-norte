<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<pre>";

echo "PHP: " . phpversion() . "\n\n";

echo ".env: ";
var_dump(file_exists(__DIR__ . '/.env'));

echo "vendor/autoload.php: ";
var_dump(file_exists(__DIR__ . '/vendor/autoload.php'));

echo "bootstrap/app.php: ";
var_dump(file_exists(__DIR__ . '/bootstrap/app.php'));

echo "storage/: ";
var_dump(is_dir(__DIR__ . '/storage'));

echo "storage writable: ";
var_dump(is_writable(__DIR__ . '/storage'));

echo "bootstrap/cache: ";
var_dump(is_dir(__DIR__ . '/bootstrap/cache'));

echo "bootstrap/cache writable: ";
var_dump(is_writable(__DIR__ . '/bootstrap/cache'));