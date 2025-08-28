<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/SqlConnection.php';
require_once __DIR__ . '/../../src/Entity/User.php';
require_once __DIR__ . '/../../src/Entity/Contract.php';
require_once __DIR__ . '/../../src/Entity/Billing.php';
require_once __DIR__ . '/../../src/Entity/Customer.php';
require_once __DIR__ . '/../../src/Entity/Vehicle.php';

use EasyLoc\Database\SqlConnection;
use EasyLoc\Entity\User;
use EasyLoc\Entity\Contract;
use EasyLoc\Entity\Billing;
// use EasyLoc\Entity\Customer;
// use EasyLoc\Entity\Vehicle;

class DatabaseSchemaCreator {
    private $connection;
    public $entities = [
        'User' => __DIR__ . '/../../src/Entity/User.php',
        'Contract' => __DIR__ . '/../../src/Entity/Contract.php',
        'Billing' => __DIR__ . '/../../src/Entity/Billing.php',
        // 'Customer' => __DIR__ . '/../../src/Entity/Customer.php',
        // 'Vehicle' => __DIR__ . '/../../src/Entity/Vehicle.php'
    ];

    public function __construct() {
        $sqlConnection = new SqlConnection();
        $this->connection = $sqlConnection->getConnection();
    }

    public function createTables() {
        foreach ($this->entities as $entityName => $entityFile) {
            echo "\nProcessing $entityName...\n";
            if (!file_exists($entityFile)) {
                echo "ERROR: File does not exist: $entityFile\n";
                continue;
            }
            
            try {
                $className = "EasyLoc\\Entity\\$entityName";
                echo "Looking for class $className...\n";
                
                // Load the file contents to check for syntax errors
                $contents = file_get_contents($entityFile);
                if ($contents === false) {
                    echo "ERROR: Could not read file: $entityFile\n";
                    continue;
                }
                
                require_once $entityFile;
                
                if (!class_exists($className)) {
                    echo "ERROR: Class $className not found after loading $entityFile\n";
                    continue;
                }
                
                echo "Class found, creating reflection...\n";
                $reflection = new ReflectionClass($className);
                    $properties = $reflection->getProperties();
                
                $columns = [];
                foreach ($properties as $property) {
                    $type = $this->getPropertyType($property);
                    $columns[] = $this->getColumnDefinition($property->getName(), $type);
                }

                $sql = "IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[$entityName]') AND type in (N'U'))
                BEGIN
                    CREATE TABLE [dbo].[$entityName] (
                        " . implode(",\n                        ", $columns) . "
                    )
                    PRINT '$entityName table created successfully.'
                END";

                try {
                    $this->connection->executeStatement($sql);
                    echo "$entityName table created or already exists.\n";
                } catch (Exception $e) {
                    echo "Error creating $entityName table: " . $e->getMessage() . "\n";
                }
            } catch (Exception $e) {
                echo "Error processing $entityName: " . $e->getMessage() . "\n";
            }
        }
    }

    private function getPropertyType(ReflectionProperty $property): string {
        $type = $property->getType();
        if ($type) {
            return $type->getName();
        }
        return 'string'; // default type
    }

    private function getColumnDefinition(string $propertyName, string $type): string {
        // Convert camelCase to snake_case
        $columnName = strtoupper(preg_replace('/(?<!^)[A-Z]/', '_$0', $propertyName));
        
        // Map PHP types to SQL Server types
        $sqlType = match($type) {
            'int' => 'INT',
            'float' => 'DECIMAL(10,2)',
            'string' => 'NVARCHAR(255)',
            'datetime' => 'DATETIME',
            'bool' => 'BIT',
            default => 'NVARCHAR(255)'
        };

        // Add special handling for ID columns
        if (strtolower($propertyName) === 'id') {
            return "[$columnName] INT IDENTITY(1,1) PRIMARY KEY";
        }

        return "[$columnName] $sqlType NOT NULL";
    }
}

// Create tables when script is run directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    try {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        echo "Starting table creation...\n";
        $creator = new DatabaseSchemaCreator();
        echo "Checking entity files...\n";
        foreach ($creator->entities as $entityName => $entityFile) {
            if (file_exists($entityFile)) {
                echo "$entityName entity file found at $entityFile\n";
            } else {
                echo "Warning: $entityName entity file not found at $entityFile\n";
            }
        }
        echo "Creating tables...\n";
        $creator->createTables();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
}