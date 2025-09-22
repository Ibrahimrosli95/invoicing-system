<?php
/**
 * Laravel Artisan Web Runner for Shared Hosting
 * 🚨 SECURITY WARNING: Delete this file after deployment setup!
 *
 * Usage: https://yourdomain.com/artisan-runner.php?key=Baemzone32@&cmd=config:cache
 */

// Security Configuration
$SECURE_KEY = 'Baemzone32@';
$ALLOWED_COMMANDS = [
    'config:cache', 'config:clear',
    'route:cache', 'route:clear',
    'view:cache', 'view:clear',
    'cache:clear',
    'migrate', 'migrate:status',
    'key:generate',
    'storage:link',
    'about', 'list',
    'queue:work', 'queue:restart',
    'optimize', 'optimize:clear'
];

// Security check
if (!isset($_GET['key']) || $_GET['key'] !== $SECURE_KEY) {
    http_response_code(403);
    die('❌ Access Denied');
}

// Get command from URL
$cmd = $_GET['cmd'] ?? null;
$args = $_GET['args'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Artisan Runner</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background: #f8fafc;
            color: #374151;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .warning {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            text-align: center;
        }
        .form-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .quick-commands {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin: 20px 0;
        }
        .quick-cmd {
            padding: 10px 15px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
        }
        .quick-cmd:hover {
            background: #2563eb;
        }
        .output {
            background: #1f2937;
            color: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            max-height: 500px;
            overflow-y: auto;
            margin: 20px 0;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            margin: 5px 0;
        }
        button {
            background: #059669;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #047857;
        }
        .error {
            color: #dc2626;
            background: #fef2f2;
            padding: 10px;
            border-radius: 6px;
            margin: 10px 0;
        }
        .success {
            color: #059669;
            background: #f0fdf4;
            padding: 10px;
            border-radius: 6px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚀 Laravel Artisan Runner</h1>
        <p>Enhanced Invoice & Quotation Builder System</p>
    </div>

    <div class="warning">
        <strong>🚨 Security Warning:</strong> Delete this file after completing deployment setup!
    </div>

    <div class="form-section">
        <h3>Quick Commands</h3>
        <div class="quick-commands">
            <a href="?key=<?= $SECURE_KEY ?>&cmd=config:cache" class="quick-cmd">Cache Config</a>
            <a href="?key=<?= $SECURE_KEY ?>&cmd=route:cache" class="quick-cmd">Cache Routes</a>
            <a href="?key=<?= $SECURE_KEY ?>&cmd=view:cache" class="quick-cmd">Cache Views</a>
            <a href="?key=<?= $SECURE_KEY ?>&cmd=migrate" class="quick-cmd">Run Migrations</a>
            <a href="?key=<?= $SECURE_KEY ?>&cmd=migrate:status" class="quick-cmd">Migration Status</a>
            <a href="?key=<?= $SECURE_KEY ?>&cmd=storage:link" class="quick-cmd">Storage Link</a>
            <a href="?key=<?= $SECURE_KEY ?>&cmd=about" class="quick-cmd">Laravel Info</a>
            <a href="?key=<?= $SECURE_KEY ?>&cmd=optimize" class="quick-cmd">Optimize App</a>
        </div>
    </div>

    <div class="form-section">
        <h3>Custom Command</h3>
        <form method="GET">
            <input type="hidden" name="key" value="<?= $SECURE_KEY ?>">
            <select name="cmd">
                <option value="">Select a command...</option>
                <?php foreach ($ALLOWED_COMMANDS as $allowedCmd): ?>
                    <option value="<?= $allowedCmd ?>" <?= $cmd === $allowedCmd ? 'selected' : '' ?>>
                        <?= $allowedCmd ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="args" placeholder="Additional arguments (optional)" value="<?= htmlspecialchars($args) ?>">
            <button type="submit">Execute Command</button>
        </form>
    </div>

    <?php if ($cmd): ?>
        <div class="form-section">
            <h3>Command Output</h3>
            <div class="output">
<?php
// Validate command
if (!in_array($cmd, $ALLOWED_COMMANDS)) {
    echo "❌ Error: Command '{$cmd}' is not allowed for security reasons.";
} else {
    try {
        // Load Laravel
        require __DIR__ . '/../vendor/autoload.php';
        $app = require_once __DIR__ . '/../bootstrap/app.php';

        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

        // Prepare full command
        $fullCommand = $cmd;
        if ($args) {
            $fullCommand .= ' ' . $args;
        }

        echo "🚀 Executing: php artisan {$fullCommand}\n";
        echo "⏰ Started at: " . date('Y-m-d H:i:s') . "\n";
        echo str_repeat('-', 60) . "\n";

        // Execute command
        $exitCode = $kernel->call($cmd, $args ? explode(' ', $args) : []);
        echo $kernel->output();

        echo "\n" . str_repeat('-', 60);
        echo "\n✅ Command completed with exit code: {$exitCode}";
        echo "\n⏰ Finished at: " . date('Y-m-d H:i:s');

    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage();
        echo "\n📍 File: " . $e->getFile();
        echo "\n🔢 Line: " . $e->getLine();
    }
}
?>
            </div>
        </div>
    <?php endif; ?>

    <div class="form-section">
        <h3>Common Deployment Commands</h3>
        <p><strong>After uploading files:</strong></p>
        <ol>
            <li>Run <code>config:cache</code> to cache configuration</li>
            <li>Run <code>route:cache</code> to cache routes</li>
            <li>Run <code>view:cache</code> to cache views</li>
            <li>Run <code>migrate</code> to set up database</li>
            <li>Run <code>storage:link</code> to link storage</li>
            <li>Check <code>about</code> to verify Laravel installation</li>
        </ol>

        <p><strong>Remember:</strong> Delete this file after completing setup!</p>
    </div>
</body>
</html>