<?php
declare(strict_types=1);

namespace EasyLoc\Database;

require_once __DIR__ . '/../../vendor/autoload.php';

use Exception;
use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;

class SqlConnection {
    private $connection;

    public function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->connection = DriverManager::getConnection([
            'dbname' => $_ENV['MSSQL_DB'],
            'user' => $_ENV['MSSQL_USER'],
            'password' => $_ENV['MSSQL_PASS'],
            'host' => $_ENV['MSSQL_HOST'],
            'driver' => 'sqlsrv',
            'port' => $_ENV['MSSQL_PORT'] ?? 1433,
            'driverOptions' => [
                'TrustServerCertificate' => 'yes',
                'MultipleActiveResultSets' => true,
                'Encrypt' => 'yes'
            ]
        ]);
    }

    public function getConnection() {
        return $this->connection;
    }
}

// Test connection if file is run directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    try {
        $connection = new SqlConnection();
        $conn = $connection->getConnection();
        echo "Successfully connected to the database!\n";
        
        // Test a simple query
        $result = $conn->executeQuery('SELECT @@VERSION as version');
        $version = $result->fetchOne();
        echo "SQL Server Version: " . $version . "\n";
        
        // Check current database name
        $result = $conn->executeQuery('SELECT DB_NAME() as dbname');
        $dbname = $result->fetchOne();
        echo "Connected to database: " . $dbname . "\n";
    } catch (Exception $e) {
        echo "Connection failed: " . $e->getMessage() . "\n";
    }
}
