<?php
require_once __DIR__ . '/vendor/autoload.php';
use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;

echo "Database Standardization Script (Revised Version)\n";
echo "===========================================\n\n";

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
    
    // Start a transaction
    $connection->beginTransaction();
    
    echo "Beginning transaction...\n\n";
    
    // Step 1: Identify and drop foreign key constraints
    echo "Step 1: Dropping foreign key constraints...\n";
    $fkSql = "
        SELECT 
            f.name AS ForeignKeyName,
            OBJECT_NAME(f.parent_object_id) AS TableName
        FROM 
            sys.foreign_keys AS f
        WHERE 
            OBJECT_NAME(f.parent_object_id) IN ('billings', 'Billing')
    ";
    
    $foreignKeys = $connection->executeQuery($fkSql)->fetchAllAssociative();
    
    foreach ($foreignKeys as $fk) {
        $dropFkSql = "ALTER TABLE [dbo].[{$fk['TableName']}] DROP CONSTRAINT [{$fk['ForeignKeyName']}]";
        $connection->executeStatement($dropFkSql);
        echo "- Dropped foreign key {$fk['ForeignKeyName']} from table {$fk['TableName']}\n";
    }
    
    // Step 2: Fix the Billing table structure
    echo "\nStep 2: Updating Billing table structure...\n";
    
    // First check if we need to drop and recreate the Billing table
    $dropAndRecreate = false;
    
    try {
        // Try to update an existing column to see if it allows NULL
        $connection->executeStatement("ALTER TABLE [dbo].[Billing] ALTER COLUMN [CustomerId] [int] NULL");
        echo "- Modified CustomerId to allow NULL values\n";
        
        // Check columns in Billing
        $columnsSql = "
            SELECT 
                COLUMN_NAME
            FROM 
                INFORMATION_SCHEMA.COLUMNS
            WHERE 
                TABLE_NAME = 'Billing'
        ";
        
        $columns = $connection->executeQuery($columnsSql)->fetchAllAssociative();
        $columnNames = array_column($columns, 'COLUMN_NAME');
        
        // Check if ContractId column exists
        if (!in_array('ContractId', $columnNames)) {
            $connection->executeStatement("ALTER TABLE [dbo].[Billing] ADD [ContractId] [int] NULL");
            echo "- Added ContractId column to Billing table\n";
        }
        
        // Check if PaymentMethod column exists
        if (!in_array('PaymentMethod', $columnNames)) {
            $connection->executeStatement("ALTER TABLE [dbo].[Billing] ADD [PaymentMethod] [nvarchar](100) NULL");
            echo "- Added PaymentMethod column to Billing table\n";
        }
        
        // Check if InvoiceNumber column exists
        if (!in_array('InvoiceNumber', $columnNames)) {
            $connection->executeStatement("ALTER TABLE [dbo].[Billing] ADD [InvoiceNumber] [nvarchar](200) NULL");
            echo "- Added InvoiceNumber column to Billing table\n";
        }
    } catch (Exception $e) {
        echo "- Cannot modify Billing table structure, will drop and recreate\n";
        $dropAndRecreate = true;
    }
    
    // If we need to drop and recreate, do that now
    if ($dropAndRecreate) {
        // Save existing data
        $billingData = [];
        try {
            $selectSql = "SELECT * FROM [dbo].[Billing]";
            $billingData = $connection->executeQuery($selectSql)->fetchAllAssociative();
            echo "- Saved " . count($billingData) . " records from Billing table\n";
        } catch (Exception $e) {
            echo "- No data to save from Billing table\n";
        }
        
        // Drop the table
        try {
            $connection->executeStatement("DROP TABLE [dbo].[Billing]");
            echo "- Dropped Billing table\n";
        } catch (Exception $e) {
            echo "- Could not drop Billing table: " . $e->getMessage() . "\n";
        }
        
        // Create new table
        $createBillingSql = "
            CREATE TABLE [dbo].[Billing] (
                [Id] [int] PRIMARY KEY,
                [ContractId] [int] NULL,
                [CustomerId] [int] NULL,
                [Amount] [decimal](10, 2) NOT NULL,
                [BillingDate] [date] NOT NULL,
                [PaymentMethod] [nvarchar](100) NULL,
                [InvoiceNumber] [nvarchar](200) NULL
            )
        ";
        
        $connection->executeStatement($createBillingSql);
        echo "- Created new Billing table with proper structure\n";
        
        // Restore data if any
        if (count($billingData) > 0) {
            foreach ($billingData as $record) {
                $insertSql = "
                    INSERT INTO [dbo].[Billing] (
                        [Id], [CustomerId], [Amount], [BillingDate]
                    ) VALUES (
                        {$record['Id']}, {$record['CustomerId']}, {$record['Amount']}, '{$record['BillingDate']}'
                    )
                ";
                
                $connection->executeStatement($insertSql);
            }
            echo "- Restored " . count($billingData) . " records to Billing table\n";
        }
    }
    
    // Step 3: Create Contract table if it doesn't exist
    echo "\nStep 3: Creating Contract table if needed...\n";
    
    $tableExistsSql = "
        SELECT 
            COUNT(*) as count 
        FROM 
            INFORMATION_SCHEMA.TABLES 
        WHERE 
            TABLE_NAME = 'Contract'
    ";
    
    $contractTableExists = $connection->executeQuery($tableExistsSql)->fetchAssociative()['count'] > 0;
    
    if (!$contractTableExists) {
        $createContractSql = "
            CREATE TABLE [dbo].[Contract] (
                [Id] [int] PRIMARY KEY,
                [VehicleId] [nvarchar](510) NOT NULL,
                [CustomerId] [nvarchar](510) NOT NULL,
                [SignDate] [datetime] NOT NULL,
                [StartDate] [datetime] NOT NULL,
                [EndDate] [datetime] NOT NULL,
                [ReturnDate] [datetime] NULL,
                [Price] [money] NOT NULL
            )
        ";
        
        $connection->executeStatement($createContractSql);
        echo "- Created Contract table\n";
    } else {
        echo "- Contract table already exists\n";
    }
    
    // Step 4: Migrate data from contracts to Contract
    echo "\nStep 4: Migrating data from contracts to Contract...\n";
    
    // Check if contracts table exists
    $contractsExistsSql = "
        SELECT 
            COUNT(*) as count 
        FROM 
            INFORMATION_SCHEMA.TABLES 
        WHERE 
            TABLE_NAME = 'contracts'
    ";
    
    $contractsExists = $connection->executeQuery($contractsExistsSql)->fetchAssociative()['count'] > 0;
    
    if ($contractsExists) {
        // Count records in contracts
        $countSql = "SELECT COUNT(*) as count FROM [dbo].[contracts]";
        $count = $connection->executeQuery($countSql)->fetchAssociative()['count'];
        
        if ($count > 0) {
            // Clear any existing data in Contract table
            $connection->executeStatement("DELETE FROM [dbo].[Contract]");
            
            // Copy data from contracts to Contract
            $insertSql = "
                INSERT INTO [dbo].[Contract] (
                    [Id], [VehicleId], [CustomerId], [SignDate], 
                    [StartDate], [EndDate], [ReturnDate], [Price]
                )
                SELECT 
                    [id], [vehicle_uid], [customer_uid], [sign_datetime],
                    [loc_begin_datetime], [loc_end_datetime], [returning_datetime], [price]
                FROM [dbo].[contracts]
            ";
            
            $connection->executeStatement($insertSql);
            echo "- Copied $count records from contracts to Contract\n";
            
            // Drop the old contracts table
            $connection->executeStatement("DROP TABLE [dbo].[contracts]");
            echo "- Dropped old contracts table\n";
        } else {
            echo "- No data in contracts table to migrate\n";
            
            // Still drop the empty table
            $connection->executeStatement("DROP TABLE [dbo].[contracts]");
            echo "- Dropped empty contracts table\n";
        }
    } else {
        echo "- contracts table doesn't exist, skipping migration\n";
    }
    
    // Step 5: Migrate data from billings to Billing
    echo "\nStep 5: Migrating data from billings to Billing...\n";
    
    // Check if billings table exists
    $billingsExistsSql = "
        SELECT 
            COUNT(*) as count 
        FROM 
            INFORMATION_SCHEMA.TABLES 
        WHERE 
            TABLE_NAME = 'billings'
    ";
    
    $billingsExists = $connection->executeQuery($billingsExistsSql)->fetchAssociative()['count'] > 0;
    
    if ($billingsExists) {
        // Count records in billings
        $countSql = "SELECT COUNT(*) as count FROM [dbo].[billings]";
        $count = $connection->executeQuery($countSql)->fetchAssociative()['count'];
        
        if ($count > 0) {
            // Copy data from billings to Billing
            $insertSql = "
                UPDATE [dbo].[Billing]
                SET 
                    [ContractId] = b.[contract_id],
                    [PaymentMethod] = b.[payment_method],
                    [InvoiceNumber] = b.[invoice_number]
                FROM 
                    [dbo].[Billing]
                INNER JOIN 
                    [dbo].[billings] b ON [dbo].[Billing].[Id] = b.[id]
            ";
            
            // Check if there are any matching IDs between Billing and billings
            $matchCountSql = "
                SELECT COUNT(*) as count 
                FROM [dbo].[Billing] b1
                INNER JOIN [dbo].[billings] b2 ON b1.[Id] = b2.[id]
            ";
            
            $matchCount = $connection->executeQuery($matchCountSql)->fetchAssociative()['count'];
            
            if ($matchCount > 0) {
                $connection->executeStatement($insertSql);
                echo "- Updated $matchCount records in Billing from billings\n";
            } else {
                // If no matches, insert all records
                $insertAllSql = "
                    INSERT INTO [dbo].[Billing] (
                        [Id], [ContractId], [Amount], [BillingDate], 
                        [PaymentMethod], [InvoiceNumber]
                    )
                    SELECT 
                        [id], [contract_id], [amount], [payment_date],
                        [payment_method], [invoice_number]
                    FROM [dbo].[billings]
                ";
                
                $connection->executeStatement($insertAllSql);
                echo "- Inserted $count records from billings to Billing\n";
            }
            
            // Drop the old billings table
            $connection->executeStatement("DROP TABLE [dbo].[billings]");
            echo "- Dropped old billings table\n";
        } else {
            echo "- No data in billings table to migrate\n";
            
            // Still drop the empty table
            $connection->executeStatement("DROP TABLE [dbo].[billings]");
            echo "- Dropped empty billings table\n";
        }
    } else {
        echo "- billings table doesn't exist, skipping migration\n";
    }
    
    // Step 6: Create foreign key between Billing and Contract
    echo "\nStep 6: Creating foreign key relationship...\n";
    
    $createFkSql = "
        IF NOT EXISTS (
            SELECT * FROM sys.foreign_keys 
            WHERE name = 'FK_Billing_Contract' AND 
                  parent_object_id = OBJECT_ID('dbo.Billing')
        )
        BEGIN
            ALTER TABLE [dbo].[Billing] 
            ADD CONSTRAINT [FK_Billing_Contract] 
            FOREIGN KEY ([ContractId]) REFERENCES [dbo].[Contract] ([Id])
        END
    ";
    
    $connection->executeStatement($createFkSql);
    echo "- Created foreign key relationship between Billing and Contract\n";
    
    // Step 7: Add sample data if tables are empty
    echo "\nStep 7: Checking if sample data needed...\n";
    
    // Check if Contract has data
    $countSql = "SELECT COUNT(*) as count FROM [dbo].[Contract]";
    $contractCount = $connection->executeQuery($countSql)->fetchAssociative()['count'];
    
    if ($contractCount == 0) {
        echo "- Adding sample data to Contract table\n";
        
        $sampleContractSql = "
            INSERT INTO [dbo].[Contract] (
                [Id], [VehicleId], [CustomerId], [SignDate], 
                [StartDate], [EndDate], [ReturnDate], [Price]
            ) VALUES 
                (1, 'VEH001', 'CUST001', '2025-07-01', '2025-07-01', '2025-12-31', NULL, 500.00),
                (2, 'VEH002', 'CUST002', '2025-08-01', '2025-08-01', '2025-11-30', NULL, 450.00),
                (3, 'VEH003', 'CUST003', '2025-09-01', '2025-09-01', '2025-10-31', NULL, 600.00)
        ";
        
        $connection->executeStatement($sampleContractSql);
        $contractCount = 3;
    }
    
    // Check if Billing has data
    $countSql = "SELECT COUNT(*) as count FROM [dbo].[Billing]";
    $billingCount = $connection->executeQuery($countSql)->fetchAssociative()['count'];
    
    if ($billingCount == 0 && $contractCount > 0) {
        echo "- Adding sample data to Billing table\n";
        
        $sampleBillingSql = "
            INSERT INTO [dbo].[Billing] (
                [Id], [ContractId], [CustomerId], [Amount], [BillingDate], 
                [PaymentMethod], [InvoiceNumber]
            ) VALUES 
                (1, 1, 1, 50.00, '2025-08-01', 'Credit Card', 'INV-2025-001'),
                (2, 2, 2, 75.00, '2025-08-15', 'Bank Transfer', 'INV-2025-002'),
                (3, 3, 3, 100.00, '2025-08-30', 'Cash', 'INV-2025-003')
        ";
        
        $connection->executeStatement($sampleBillingSql);
    }
    
    // Commit transaction
    $connection->commit();
    echo "\nTransaction committed successfully!\n\n";
    
    // Verify final state
    echo "Final Database State:\n";
    echo "-------------------\n";
    
    $tablesSql = "
        SELECT name FROM sys.tables 
        WHERE type = 'U' 
        ORDER BY name
    ";
    
    $tables = $connection->executeQuery($tablesSql)->fetchAllAssociative();
    
    echo "Tables in database:\n";
    foreach ($tables as $table) {
        echo "- " . $table['name'] . "\n";
    }
    
    // Count records
    $countContractSql = "SELECT COUNT(*) as count FROM [dbo].[Contract]";
    $countBillingSql = "SELECT COUNT(*) as count FROM [dbo].[Billing]";
    
    $contractCount = $connection->executeQuery($countContractSql)->fetchAssociative()['count'];
    $billingCount = $connection->executeQuery($countBillingSql)->fetchAssociative()['count'];
    
    echo "\nRecord counts:\n";
    echo "- Contract records: $contractCount\n";
    echo "- Billing records: $billingCount\n";
    
    echo "\nStandardization completed successfully!\n";
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($connection) && $connection->isTransactionActive()) {
        $connection->rollBack();
        echo "\nTransaction rolled back due to error!\n";
    }
    
    echo "ERROR: " . $e->getMessage() . "\n";
    if ($e->getPrevious()) {
        echo "Caused by: " . $e->getPrevious()->getMessage() . "\n";
    }
}
