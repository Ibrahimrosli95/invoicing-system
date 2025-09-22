<?php
/**
 * Laravel Sales System - Deployment Verification Script
 * Place this file in your public_html/sales-system/ directory after deployment
 * Access via: https://yourdomain.com/sales-system/deployment-check.php
 * Delete this file after successful verification
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$checks = [];
$errors = [];

// Check 1: PHP Version
$phpVersion = phpversion();
$checks['PHP Version'] = $phpVersion >= '8.1' ? "✅ PHP $phpVersion" : "❌ PHP $phpVersion (requires 8.1+)";
if ($phpVersion < '8.1') $errors[] = "PHP version too old";

// Check 2: Required PHP Extensions
$requiredExtensions = ['mbstring', 'openssl', 'pdo', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath'];
foreach ($requiredExtensions as $ext) {
    $checks["Extension: $ext"] = extension_loaded($ext) ? "✅ Available" : "❌ Missing";
    if (!extension_loaded($ext)) $errors[] = "Missing extension: $ext";
}

// Check 3: Laravel Application Path
$appPath = '/home/binapain/sales-system';
$checks['Laravel App Path'] = is_dir($appPath) ? "✅ Found at $appPath" : "❌ Not found at $appPath";
if (!is_dir($appPath)) $errors[] = "Laravel application directory not found";

// Check 4: Environment File
$envPath = $appPath . '/.env';
$checks['Environment File'] = file_exists($envPath) ? "✅ Found" : "❌ Missing .env file";
if (!file_exists($envPath)) $errors[] = ".env file missing";

// Check 5: Vendor Directory
$vendorPath = $appPath . '/vendor';
$checks['Vendor Dependencies'] = is_dir($vendorPath) ? "✅ Found" : "❌ Missing vendor directory";
if (!is_dir($vendorPath)) $errors[] = "Vendor dependencies not installed";

// Check 6: Storage Permissions
$storagePath = $appPath . '/storage';
$checks['Storage Directory'] = is_writable($storagePath) ? "✅ Writable" : "❌ Not writable";
if (!is_writable($storagePath)) $errors[] = "Storage directory not writable";

// Check 7: Bootstrap Cache Permissions
$bootstrapCachePath = $appPath . '/bootstrap/cache';
$checks['Bootstrap Cache'] = is_writable($bootstrapCachePath) ? "✅ Writable" : "❌ Not writable";
if (!is_writable($bootstrapCachePath)) $errors[] = "Bootstrap cache not writable";

// Check 8: Database Connection (if .env exists)
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    preg_match('/DB_HOST=(.*)/', $envContent, $hostMatch);
    preg_match('/DB_DATABASE=(.*)/', $envContent, $dbMatch);
    preg_match('/DB_USERNAME=(.*)/', $envContent, $userMatch);
    preg_match('/DB_PASSWORD=(.*)/', $envContent, $passMatch);

    if (isset($hostMatch[1], $dbMatch[1], $userMatch[1])) {
        $host = trim($hostMatch[1]);
        $database = trim($dbMatch[1]);
        $username = trim($userMatch[1]);
        $password = isset($passMatch[1]) ? trim($passMatch[1]) : '';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
            $checks['Database Connection'] = "✅ Connected to $database";
        } catch (PDOException $e) {
            $checks['Database Connection'] = "❌ Connection failed: " . $e->getMessage();
            $errors[] = "Database connection failed";
        }
    } else {
        $checks['Database Connection'] = "❌ Database configuration incomplete";
        $errors[] = "Database configuration incomplete";
    }
}

// Check 9: Laravel Artisan
$artisanPath = $appPath . '/artisan';
$checks['Artisan File'] = file_exists($artisanPath) ? "✅ Found" : "❌ Missing artisan file";
if (!file_exists($artisanPath)) $errors[] = "Artisan file missing";

// Check 10: Public Assets
$assetsPath = __DIR__ . '/build';
$checks['Compiled Assets'] = is_dir($assetsPath) ? "✅ Found" : "❌ Assets not compiled";

// Check 11: Enhanced Builder Feature Flags
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    $featureFlags = [
        'FEATURE_INVOICE_BUILDER_V2',
        'FEATURE_QUOTATION_BUILDER_V2',
        'FEATURE_PRODUCT_SEARCH_MODAL'
    ];

    $flagsEnabled = 0;
    foreach ($featureFlags as $flag) {
        if (strpos($envContent, "$flag=true") !== false) {
            $flagsEnabled++;
        }
    }

    $checks['Enhanced Builder Features'] = $flagsEnabled >= 2 ? "✅ $flagsEnabled/3 enabled" : "⚠️ Only $flagsEnabled/3 enabled";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Sales System - Deployment Check</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #f8fafc;
            color: #374151;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #1f2937;
            margin: 0 0 10px 0;
        }
        .status {
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .status.success {
            border-left: 4px solid #10b981;
        }
        .status.error {
            border-left: 4px solid #ef4444;
        }
        .status.warning {
            border-left: 4px solid #f59e0b;
        }
        .check-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .check-item:last-child {
            border-bottom: none;
        }
        .check-name {
            font-weight: 500;
        }
        .check-result {
            font-family: monospace;
        }
        .errors {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .success-msg {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            font-size: 14px;
            color: #6b7280;
        }
        .next-steps {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚀 Laravel Sales System</h1>
        <p>Deployment Verification Report</p>
        <small>Generated on <?= date('Y-m-d H:i:s') ?></small>
    </div>

    <?php if (empty($errors)): ?>
        <div class="success-msg">
            <strong>✅ Deployment Successful!</strong><br>
            All critical checks passed. Your Laravel Sales System is ready for use.
        </div>
    <?php else: ?>
        <div class="errors">
            <strong>❌ Deployment Issues Found:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="status <?= empty($errors) ? 'success' : 'error' ?>">
        <h3>Deployment Status</h3>
        <?php foreach ($checks as $name => $result): ?>
            <div class="check-item">
                <span class="check-name"><?= htmlspecialchars($name) ?></span>
                <span class="check-result"><?= $result ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($errors)): ?>
        <div class="next-steps">
            <strong>🎯 Next Steps:</strong><br>
            1. Delete this verification file: <code>deployment-check.php</code><br>
            2. Access your application: <a href="index.php">Laravel Sales System</a><br>
            3. Test the Enhanced Invoice & Quotation Builders<br>
            4. Configure email settings and test notifications
        </div>
    <?php endif; ?>

    <div class="footer">
        <p><strong>Laravel Sales System</strong> - Enhanced Invoice & Quotation Builder</p>
        <p>Milestone 19: Enhanced Builder System ✅ Complete</p>
        <p><em>Remember to delete this file after verification!</em></p>
    </div>
</body>
</html>