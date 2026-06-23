<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mouhamat_db');

$pdo = null;

try {
    // Attempt connecting to the specific database
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // If database does not exist (Error code 1049), attempt to create it using schema.sql
    if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
        try {
            // Connect to MySQL server without dbname
            $tempDsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
            $tempPdo = new PDO($tempDsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Read the schema.sql file
            $schemaPath = dirname(__DIR__) . '/schema.sql';
            if (file_exists($schemaPath)) {
                $sql = file_get_contents($schemaPath);
                
                // Execute schema.sql (contains CREATE DATABASE, tables, and seeding)
                $tempPdo->exec($sql);
                
                // Reconnect to the newly created database
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } else {
                die("Database connection failed, and schema.sql was not found to auto-initialize: " . $e->getMessage());
            }
        } catch (PDOException $ex) {
            die("Database auto-initialization failed: " . $ex->getMessage());
        }
    } else {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Helper functions for easy query execution
function dbQuery($query, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt;
}

function dbFetch($query, $params = []) {
    return dbQuery($query, $params)->fetch();
}

function dbFetchAll($query, $params = []) {
    return dbQuery($query, $params)->fetchAll();
}

function dbInsertId() {
    global $pdo;
    return $pdo->lastInsertId();
}
