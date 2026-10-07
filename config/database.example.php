<?php
/**
 * SponsorFlow - Database Configuration Template
 * 
 * Instructions:
 * 1. Copy this file to 'config/database.php'
 * 2. Configure your local MySQL credentials (default port 3306, user 'root')
 * 3. Never commit 'config/database.php' with production passwords to source control.
 */

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'sponsorflow');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

/**
 * Returns a configured PDO database connection instance.
 *
 * @return PDO
 * @throws PDOException
 */
function getDBConnection(): PDO {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        DB_PORT,
        DB_NAME
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database connection error: ' . $e->getMessage());
        throw new PDOException(
            'Database connection failed: ' . $e->getMessage()
        );
    }
}
