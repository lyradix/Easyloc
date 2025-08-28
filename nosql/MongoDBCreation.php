<?php

require_once __DIR__ . '/../vendor/autoload.php';

// MongoDB Atlas connection string
$connectionString = "mongodb+srv://username:password@cluster.mongodb.net/EasylocDB?retryWrites=true&w=majority";

try {
    echo "Attempting to connect to MongoDB Atlas cluster...\n";
    $client = new MongoDB\Client($connectionString);

    // Create database
    $database = $client->EasylocDB;
    echo "Database 'EasylocDB' selected.\n";

    // Test connection
    $command = new MongoDB\Driver\Command(['ping' => 1]);
    $client->getManager()->executeCommand('admin', $command);
    echo "Successfully connected to MongoDB Atlas!\n";

    // Create a test collection and insert a document
    $collection = $database->Test;
    $result = $collection->insertOne([
        'test' => true,
        'timestamp' => new MongoDB\BSON\UTCDateTime(),
        'message' => 'MongoDB connection test successful'
    ]);

    echo "Inserted test document with ID: " . $result->getInsertedId() . "\n";
    echo "Number of documents in 'Test' collection: " . $collection->countDocuments() . "\n";

    // Create indexes
    echo "\nCreating indexes...\n";
    
    // Contracts collection
    $database->Contract->createIndex(['VEHICLE_UID' => 1]);
    $database->Contract->createIndex(['CUSTOMER_UID' => 1]);
    echo "Contract indexes created.\n";

    // Customers collection
    $database->Customer->createIndex(['EMAIL' => 1], ['unique' => true]);
    echo "Customer indexes created.\n";

    // Vehicles collection
    $database->Vehicle->createIndex(['LICENSE_PLATE' => 1], ['unique' => true]);
    echo "Vehicle indexes created.\n";

    // Users collection
    $database->User->createIndex(['EMAIL' => 1], ['unique' => true]);
    echo "User indexes created.\n";

    // Billing collection
    $database->Billing->createIndex(['CONTRACT_ID' => 1]);
    $database->Billing->createIndex(['CUSTOMER_UID' => 1]);
    echo "Billing indexes created.\n";

    echo "\nMongoDB setup completed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}