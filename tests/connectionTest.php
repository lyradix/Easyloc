<?php
// Turn on all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables directly
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
try {
    $dotenv->load();
    echo "Environment variables loaded successfully\n\n";
} catch (Exception $e) {
    echo "Warning: Could not load .env file: " . $e->getMessage() . "\n\n";
}

echo "======= DATABASE CONNECTION TESTS =======\n\n";

// Test SQL Connection
echo "1. SQL SERVER CONNECTION TEST\n";
echo "---------------------------\n";

try {
    // Show SQL Server configuration
    echo "Configuration:\n";
    echo "- Database: " . ($_ENV['MSSQL_DB'] ?? 'Not set') . "\n";
    echo "- Host: " . ($_ENV['MSSQL_HOST'] ?? 'Not set') . "\n";
    echo "- Port: " . ($_ENV['MSSQL_PORT'] ?? '1433') . "\n";
    echo "- User: " . ($_ENV['MSSQL_USER'] ?? 'Not set') . "\n\n";
    
    // Connect using Doctrine DBAL
    echo "Connecting to SQL Server...\n";
    
    $connection = \Doctrine\DBAL\DriverManager::getConnection([
        'dbname' => $_ENV['MSSQL_DB'] ?? '',
        'user' => $_ENV['MSSQL_USER'] ?? '',
        'password' => $_ENV['MSSQL_PASS'] ?? '',
        'host' => $_ENV['MSSQL_HOST'] ?? '',
        'driver' => 'pdo_sqlsrv',
        'port' => $_ENV['MSSQL_PORT'] ?? 1433,
        'driverOptions' => [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            'TrustServerCertificate' => true
        ]
    ]);
    
    // Test connection with a simple query
    $result = $connection->executeQuery('SELECT @@version as version')->fetchAssociative();
    echo "SUCCESS: Connected to SQL Server!\n";
    echo "SQL Server Version: " . $result['version'] . "\n\n";
    
    // Try to get table list
    try {
        $tables = $connection->executeQuery("SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE'")->fetchAllAssociative();
        echo "Available tables: ";
        if (count($tables) > 0) {
            $tableNames = array_column($tables, 'table_name');
            echo implode(', ', $tableNames) . "\n\n";
        } else {
            echo "No tables found.\n\n";
        }
    } catch (Exception $e) {
        echo "Could not retrieve tables: " . $e->getMessage() . "\n\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: SQL connection failed: " . $e->getMessage() . "\n\n";
}

// Test MongoDB Connection
echo "2. MONGODB CONNECTION TEST\n";
echo "--------------------------\n";

try {
    // Show MongoDB configuration
    echo "Configuration:\n";
    $mongoUri = $_ENV['MONGODB_URI'] ?? 'mongodb://localhost:27017';
    $safeMongoUri = preg_replace('/mongodb\+srv:\/\/([^:]+):([^@]+)@/', 'mongodb+srv://******:******@', $mongoUri);
    echo "- Connection URI: " . $safeMongoUri . "\n";
    echo "- Database: " . ($_ENV['MONGO_DB'] ?? 'Not set') . "\n\n";
    
    // Connect to MongoDB
    echo "Connecting to MongoDB...\n";
    $mongoClient = new \MongoDB\Client($mongoUri);
    
    // Test connection with a ping command
    $adminDB = $mongoClient->selectDatabase('admin');
    $result = $adminDB->command(['ping' => 1]);
    
    // Convert command result to array to check its status
    $resultArray = current($result->toArray());
    if (isset($resultArray->ok) && $resultArray->ok == 1) {
        echo "SUCCESS: Connected to MongoDB!\n";
        
        // List available databases
        $dbs = [];
        foreach ($mongoClient->listDatabases() as $dbInfo) {
            $dbs[] = $dbInfo->getName();
        }
        echo "Available databases: " . implode(', ', $dbs) . "\n\n";
        
        // Check if EasylocDB exists and show collections
        if (in_array('EasylocDB', $dbs)) {
            $db = $mongoClient->selectDatabase('EasylocDB');
            $collections = [];
            foreach ($db->listCollections() as $collectionInfo) {
                $collections[] = $collectionInfo->getName();
            }
            echo "Collections in EasylocDB: " . implode(', ', $collections) . "\n\n";
            
            // Get document counts for key collections
            if (in_array('Customer', $collections)) {
                $count = $db->Customer->countDocuments();
                echo "Customer documents: $count\n";
            }
            
            if (in_array('Vehicle', $collections)) {
                $count = $db->Vehicle->countDocuments();
                echo "Vehicle documents: $count\n";
            }
        }
    } else {
        echo "ERROR: MongoDB ping command failed\n";
    }
} catch (Exception $e) {
    echo "ERROR: MongoDB connection failed: " . $e->getMessage() . "\n";
}

echo "\n======= TEST COMPLETED =======\n";