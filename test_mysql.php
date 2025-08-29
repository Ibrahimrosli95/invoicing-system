<?php
try {
    // First, try to connect to MySQL without specifying a database
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "Connected to MySQL successfully!\n";
    
    // Create the database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS sales_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database 'sales_system' created or already exists.\n";
    
    // Now test connecting to the specific database
    $pdo2 = new PDO('mysql:host=127.0.0.1;dbname=sales_system', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "Connected to sales_system database successfully!\n";
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    
    // Check if it's an auth issue and suggest solution
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "\nThis is likely because MySQL root user is using auth_socket plugin.\n";
        echo "You need to run this command as system administrator:\n";
        echo "sudo mysql -e \"ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';FLUSH PRIVILEGES;\"\n";
    }
}
?>