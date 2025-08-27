<?php
require_once __DIR__ . '/vendor/autoload.php';
use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;

echo "SQL Server Connection and Schema Test\n";
echo "===================================\n\n";

try {
    // Load environment variables
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    echo "✓ Environment variables loaded\n";

    // Display connection information (masked password)
    echo "\nConnection Settings:\n";
    echo "- Server: " . $_ENV['MSSQL_HOST'] . ":" . ($_ENV['MSSQL_PORT'] ?? '1433') . "\n";
    echo "- Database: " . $_ENV['MSSQL_DB'] . "\n";
    echo "- User: " . $_ENV['MSSQL_USER'] . "\n\n";

    // Create connection
    $connection = DriverManager::getConnection([
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

    // Test connection
    $version = $connection->executeQuery('SELECT @@version as version')->fetchAssociative();
    echo "✓ Connected successfully\n";
    echo "✓ SQL Server Version: " . substr($version['version'], 0, strpos($version['version'], "\n")) . "\n\n";

    // Check Contract table
    echo "Checking Contract table...\n";
    $contractColumns = $connection->executeQuery("
        SELECT 
            COLUMN_NAME,
            DATA_TYPE,
            CHARACTER_MAXIMUM_LENGTH,
            IS_NULLABLE
        FROM 
            INFORMATION_SCHEMA.COLUMNS 
        WHERE 
            TABLE_NAME = 'Contract'
        ORDER BY 
            ORDINAL_POSITION
    ")->fetchAllAssociative();

    if (empty($contractColumns)) {
        echo "✗ Contract table not found!\n";
    } else {
        echo "✓ Contract table exists with following columns:\n";
        foreach ($contractColumns as $column) {
            $type = $column['DATA_TYPE'];
            if ($column['CHARACTER_MAXIMUM_LENGTH'] !== null) {
                $type .= "({$column['CHARACTER_MAXIMUM_LENGTH']})";
            }
            echo "  - {$column['COLUMN_NAME']}: {$type} " . 
                 ($column['IS_NULLABLE'] === 'YES' ? '(nullable)' : '(not null)') . "\n";
        }

        // Check for sample data
        $contractCount = $connection->executeQuery("SELECT COUNT(*) as count FROM Contract")->fetchOne();
        echo "✓ Contract table has {$contractCount} records\n";
    }

    echo "\nChecking Billing table...\n";
    $billingColumns = $connection->executeQuery("
        SELECT 
            COLUMN_NAME,
            DATA_TYPE,
            CHARACTER_MAXIMUM_LENGTH,
            IS_NULLABLE
        FROM 
            INFORMATION_SCHEMA.COLUMNS 
        WHERE 
            TABLE_NAME = 'Billing'
        ORDER BY 
            ORDINAL_POSITION
    ")->fetchAllAssociative();

    if (empty($billingColumns)) {
        echo "✗ Billing table not found!\n";
    } else {
        echo "✓ Billing table exists with following columns:\n";
        foreach ($billingColumns as $column) {
            $type = $column['DATA_TYPE'];
            if ($column['CHARACTER_MAXIMUM_LENGTH'] !== null) {
                $type .= "({$column['CHARACTER_MAXIMUM_LENGTH']})";
            }
            echo "  - {$column['COLUMN_NAME']}: {$type} " . 
                 ($column['IS_NULLABLE'] === 'YES' ? '(nullable)' : '(not null)') . "\n";
        }

        // Check for sample data
        $billingCount = $connection->executeQuery("SELECT COUNT(*) as count FROM Billing")->fetchOne();
        echo "✓ Billing table has {$billingCount} records\n";
    }

    // Check foreign key relationship
    echo "\nChecking relationships...\n";
    $foreignKeys = $connection->executeQuery("
        SELECT 
            fk.name AS FK_NAME,
            OBJECT_NAME(fk.parent_object_id) AS TABLE_NAME,
            COL_NAME(fkc.parent_object_id, fkc.parent_column_id) AS COLUMN_NAME,
            OBJECT_NAME(fk.referenced_object_id) AS REFERENCED_TABLE_NAME,
            COL_NAME(fkc.referenced_object_id, fkc.referenced_column_id) AS REFERENCED_COLUMN_NAME
        FROM 
            sys.foreign_keys AS fk
        INNER JOIN 
            sys.foreign_key_columns AS fkc ON fk.object_id = fkc.constraint_object_id
        WHERE 
            OBJECT_NAME(fk.parent_object_id) IN ('Billing', 'Contract')
    ")->fetchAllAssociative();

    if (empty($foreignKeys)) {
        echo "✗ No foreign key relationships found!\n";
    } else {
        echo "✓ Found following relationships:\n";
        foreach ($foreignKeys as $fk) {
            echo "  - {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} -> " .
                 "{$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}" .
                 " (FK: {$fk['FK_NAME']})\n";
        }
    }

    // Test a JOIN query
    echo "\nTesting JOIN query...\n";
    $joinQuery = $connection->executeQuery("
        SELECT TOP 1
            b.Id as BillingId,
            b.Amount as BillingAmount,
            c.Id as ContractId,
            c.StartDate as ContractStart
        FROM 
            Billing b
        LEFT JOIN 
            Contract c ON b.ContractId = c.Id
    ")->fetchAssociative();

    if ($joinQuery) {
        echo "✓ JOIN query successful\n";
        echo "Sample joined record:\n";
        foreach ($joinQuery as $key => $value) {
            echo "  - {$key}: {$value}\n";
        }
    } else {
        echo "✗ No results from JOIN query\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    if ($e->getPrevious()) {
        echo "Caused by: " . $e->getPrevious()->getMessage() . "\n";
    }
}

echo "\nTest completed.\n";
