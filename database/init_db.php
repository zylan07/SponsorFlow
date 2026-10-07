<?php
/**
 * SponsorFlow - Database Migration & Seeding CLI/Web Tool
 * 
 * Runs schema.sql and seed.sql against the configured MySQL database.
 */

require_once __DIR__ . '/../config/database.php';

function runMigration() {
    $isCli = (php_sapi_name() === 'cli');
    $nl = $isCli ? PHP_EOL : "<br>\n";

    echo "============================================{$nl}";
    echo " SponsorFlow Database Setup & Migration {$nl}";
    echo "============================================{$nl}";

    try {
        echo "Connecting to MySQL server (" . DB_HOST . ":" . DB_PORT . ")...{$nl}";
        
        // Ensure database exists
        $serverDsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', DB_HOST, DB_PORT);
        $serverPdo = new PDO($serverDsn, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo " Database '" . DB_NAME . "' verified/created.{$nl}";

        $pdo = getDBConnection();
        echo " Connected to database successfully!{$nl}";

        // 1. Run schema.sql
        $schemaPath = __DIR__ . '/schema.sql';
        if (!file_exists($schemaPath)) {
            throw new Exception("schema.sql not found at: {$schemaPath}");
        }

        echo "Reading schema.sql...{$nl}";
        $schemaSql = file_get_contents($schemaPath);
        
        // Execute schema
        $pdo->exec($schemaSql);
        echo " Schema created successfully (all 9 tables)!{$nl}";

        // 2. Run seed.sql
        $seedPath = __DIR__ . '/seed.sql';
        if (!file_exists($seedPath)) {
            throw new Exception("seed.sql not found at: {$seedPath}");
        }

        echo "Reading seed.sql...{$nl}";
        $seedSql = file_get_contents($seedPath);

        // Execute seed
        $pdo->exec($seedSql);
        echo " Realistic seed data loaded successfully!{$nl}";

        // 3. Verify counts
        echo "--------------------------------------------{$nl}";
        echo "Verification of Data Records:{$nl}";
        $tables = ['users', 'companies', 'contacts', 'outreach', 'followups', 'proposals', 'sponsorships', 'deliverables', 'activities'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM `{$table}`");
            $count = $stmt->fetch()['cnt'];
            echo " - {$table}: {$count} rows{$nl}";
        }
        echo "============================================{$nl}";
        echo " Database setup completed successfully!{$nl}";

    } catch (PDOException $e) {
        echo " [ERROR] Database Error: " . $e->getMessage() . "{$nl}";
        echo "Troubleshooting:{$nl}";
        echo "1. Verify host, port, database, username, and password in config/database.php{$nl}";
        echo "2. If using Aiven Cloud MySQL, ensure your IP is allowed or public access is enabled in Aiven console.{$nl}";
        echo "3. Ensure SSL CA is configured if required by your cloud provider.{$nl}";
        if ($isCli) exit(1);
    } catch (Exception $e) {
        echo " [ERROR] " . $e->getMessage() . "{$nl}";
        if ($isCli) exit(1);
    }
}

if (php_sapi_name() === 'cli' || basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    runMigration();
}
