<?php
// Simple security check
if (!isset($_GET['key']) || $_GET['key'] !== 'Baemzone32@') {
    http_response_code(403);
    die('Forbidden');
}

// Ambil command dari URL
$cmd = $_GET['cmd'] ?? null;
if (!$cmd) {
    die('No command provided');
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<pre>";
try {
    $kernel->call($cmd);
    echo $kernel->output();
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
echo "</pre>";