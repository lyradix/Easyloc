<?php
require_once __DIR__ . '/vendor/autoload.php';
use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // Create database connection
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
    
    echo "Connected to database: " . $_ENV['MSSQL_DB'] . " at " . $_ENV['MSSQL_HOST'] . "\n\n";
    
    // List all tables
    echo "Tables in database:\n";
    echo "-------------------\n";
    
    $tablesSql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
    $tables = $connection->executeQuery($tablesSql)->fetchAllAssociative();
    
    if (empty($tables)) {
        echo "No tables found in database.\n";
    } else {
        foreach ($tables as $table) {
            echo "* " . $table['TABLE_NAME'] . "\n";
            
            // Show column details for each table
            $columnsSql = "
                SELECT 
                    COLUMN_NAME, 
                    DATA_TYPE, 
                    CHARACTER_MAXIMUM_LENGTH, 
                    IS_NULLABLE, 
                    COLUMN_DEFAULT
                FROM 
                    INFORMATION_SCHEMA.COLUMNS 
                WHERE 
                    TABLE_NAME = '{$table['TABLE_NAME']}'
                ORDER BY 
                    ORDINAL_POSITION
            ";
            
            $columns = $connection->executeQuery($columnsSql)->fetchAllAssociative();
            
            echo "  Columns:\n";
            foreach ($columns as $column) {
                $type = $column['DATA_TYPE'];
                if (!is_null($column['CHARACTER_MAXIMUM_LENGTH'])) {
                    $type .= "({$column['CHARACTER_MAXIMUM_LENGTH']})";
                }
                
                $nullable = $column['IS_NULLABLE'] === 'YES' ? 'NULL' : 'NOT NULL';
                $default = !is_null($column['COLUMN_DEFAULT']) ? "DEFAULT {$column['COLUMN_DEFAULT']}" : '';
                
                echo "  - {$column['COLUMN_NAME']} {$type} {$nullable} {$default}\n";
            }
            
            // Show primary keys
            $pkSql = "
                SELECT 
                    COLUMN_NAME
                FROM 
                    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE 
                    TABLE_NAME = '{$table['TABLE_NAME']}' AND
                    CONSTRAINT_NAME LIKE 'PK%'
            ";
            
            $primaryKeys = $connection->executeQuery($pkSql)->fetchAllAssociative();
            
            if (!empty($primaryKeys)) {
                echo "  Primary Key: " . implode(", ", array_column($primaryKeys, 'COLUMN_NAME')) . "\n";
            }
            
            // Show foreign keys
            $fkSql = "
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
                    OBJECT_NAME(fk.parent_object_id) = '{$table['TABLE_NAME']}'
            ";
            
            $foreignKeys = $connection->executeQuery($fkSql)->fetchAllAssociative();
            
            if (!empty($foreignKeys)) {
                echo "  Foreign Keys:\n";
                foreach ($foreignKeys as $fk) {
                    echo "  - {$fk['FK_NAME']}: {$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
                }
            }
            
            echo "\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    if ($e->getPrevious()) {
        echo "Caused by: " . $e->getPrevious()->getMessage() . "\n";
    }
}
