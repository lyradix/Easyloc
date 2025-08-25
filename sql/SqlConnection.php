<?php

namespace EasyLoc\Database;
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
            'driver' => 'pdo_sqlsrv',
            'port' => $_ENV['MSSQL_PORT'] ?? 1433,
            'driverOptions' => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                'TrustServerCertificate' => true
            ]
        ]);
    }

    public function getConnection() {
        return $this->connection;
    }
}
// This class establishes a connection to a SQL database using environment variables.
