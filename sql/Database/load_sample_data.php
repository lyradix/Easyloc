<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/SqlConnection.php';

use EasyLoc\Database\SqlConnection;

class DataLoader {
    private $connection;

    public function __construct() {
        $sqlConnection = new SqlConnection();
        $this->connection = $sqlConnection->getConnection();
    }

    public function loadSampleData() {
        try {
            // Read and execute the SQL file
            $sqlFile = file_get_contents(__DIR__ . '/sample_data_sqlsrv.sql');
            $statements = explode('GO', $sqlFile);

            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    echo "Executing SQL statement...\n";
                    $this->connection->executeStatement($statement);
                    echo "Statement executed successfully.\n";
                }
            }

        } catch (Exception $e) {
            echo "Error loading sample data: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}

// Load data when script is run directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    try {
        $loader = new DataLoader();
        $loader->loadSampleData();
        echo "All sample data loaded successfully!\n";
    } catch (Exception $e) {
        echo "Failed to load sample data: " . $e->getMessage() . "\n";
        exit(1);
    }
}
