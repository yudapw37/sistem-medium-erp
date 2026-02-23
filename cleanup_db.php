<?php
// cleanup_db.php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("DROP DATABASE IF EXISTS amalsyuh_erp");
    echo "Database dropped successfully.\n";

    $pdo->exec("CREATE DATABASE amalsyuh_erp");
    echo "Database created successfully.\n";

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
