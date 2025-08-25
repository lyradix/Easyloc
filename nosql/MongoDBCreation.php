<?php
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use Dotenv\Dotenv;

// Load environment variables if available
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
try {
    $dotenv->load();
} catch (Exception $e) {
    // Continue even if .env file not found
}

$client = new Client("mongodb://localhost:27017");
$collection = $client->EasyLoc->Test;
$result = $collection->insertMany([['test' => 'ok']]);
echo "Nombre de documents dans la collection 'Test': " . $collection->countDocuments();