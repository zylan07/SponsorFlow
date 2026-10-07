<?php
/**
 * SponsorFlow - Database Configuration Template
 * 
 * Instructions:
 * 1. Copy this file to 'config/database.php'
 * 2. Fill in your Aiven Cloud MySQL credentials (or local MySQL credentials)
 * 3. Never commit 'config/database.php' to source control.
 */

define('DB_HOST', 'your-aiven-host.aivencloud.com');
define('DB_PORT', '12345'); // Default Aiven port is usually 5-digit e.g. 17892; standard MySQL is 3306
define('DB_NAME', 'defaultdb');
define('DB_USER', 'avnadmin');
define('DB_PASSWORD', 'your_secure_password_here');

// SSL Configuration for Aiven Cloud MySQL
// Path to CA certificate (e.g., __DIR__ . '/ca.pem'). 
// If null, PHP will attempt system CA or SSL connection with default verification.
define('DB_SSL_CA', null); 
define('DB_SSL_VERIFY', false); // Set to true when providing a verified CA certificate file

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

    // Configure SSL for Aiven Cloud MySQL
    if (defined('PDO::MYSQL_ATTR_SSL_CA') && DB_SSL_CA && file_exists(DB_SSL_CA)) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = DB_SSL_CA;
    }

    if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = (bool) DB_SSL_VERIFY;
    }

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database connection error: ' . $e->getMessage());
        throw new PDOException('Database connection failed. Please verify credentials in config/database.php.');
    }
}
