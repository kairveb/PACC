<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=coor', 'root', '');
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Database: coor\n";
    echo "Tables: " . count($tables) . "\n\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
