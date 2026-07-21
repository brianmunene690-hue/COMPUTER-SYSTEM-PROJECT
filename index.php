<?php
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Handle CORS
\Kamau\Config\CORS::handle();

// Include routes
require_once __DIR__ . '/api/routes/api.php';

// Dispatch request
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove /api prefix for routing
$path = str_replace('/api', '', $path);

\Kamau\Routes\Router::dispatch($method, $path);