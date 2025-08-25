<?php
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use Dotenv\Dotenv;

class EasyLocMongoDBCollection {
    private $client;
    private $collection;

    public function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        // Connect to MongoDB
        $this->client = new Client("mongodb://localhost:27017");
        
        // Insert Customer data
        $this->collection = $this->client->EasyLoc->Customer;
        $this->collection->insertMany([
            [
                'uid' => 1,
                'firstName' => "Alice",
                'lastName' => "Dupont",
                'email' => "alice.dupont@gmail.com",
                'phone' => "06 45 78 90 12",
                'address' => [
                    'street' => "14b Av. Fragonard",
                    'city' => "Nice",
                    'postalCode' => "06100",
                    'country' => "France"
                ],
                'permitNumber' => "56GHI1234567"
            ],
            [
                'uid' => 2,
                'firstName' => "Bob",
                'lastName' => "Martin",
                'email' => "bob.martin@yahoo.fr",
                'phone' => "+330612345678",
                'address' => [
                    'street' => "16. Av Joachim",
                    'city' => "Nice",
                    'postalCode' => "06100",
                    'country' => "France"
                ],
                'permitNumber' => "34DEF7891234"
            ],
            [
                'uid' => 3,
                'firstName' => "Charlie",
                'lastName' => "Leroy",
                'email' => "charlie.Leroy@attitude.fr",
                'phone' => "0678507452",
                'address' => [
                    'street' => "7 Chemin des Pins",
                    'city' => "Nice",
                    'postalCode' => "06100",
                    'country' => "France"
                ],
                'permitNumber' => "56GHI1234567"
            ]
        ]);
        echo("MongoDB connection established and Customer data inserted.\n");

        // Insert Vehicle data
        $this->collection = $this->client->EasyLoc->Vehicle;
        $this->collection->insertMany([
            [
                'uid' => 1,
                'licencePlate' => "AB-123-CD",
                'informations' => [
                    'mark' => "Peugeot",
                    'model' => "208",
                    'color' => "Blue",
                    'fuelType' => "Petrol",
                    'transmission' => "Manual",
                    'seats' => 5,
                    'doors' => 5
                ],
                'km' => 11500
            ],
            [
                'uid' => 2,
                'licencePlate' => "EF-456-GH",
                'informations' => [
                    'mark' => "citroen",
                    'model' => "C3",
                    'color' => "Silver",
                    'fuelType' => "Diesel",
                    'transmission' => "Automatic",
                    'seats' => 5,
                    'doors' => 5
                ],
                'km' => 11200
            ],
            [
                'uid' => 3,
                'licencePlate' => "IJ-789-KL",
                'informations' => [
                    'mark' => "Volkswagen",
                    'model' => "Golf",
                    'color' => "Red",
                    'fuelType' => "Petrol",
                    'transmission' => "Manual",
                    'seats' => 5,
                    'doors' => 5
                ],
                'km' => 28700
            ]
        ]);
        echo("MongoDB connection established and Vehicle data inserted.\n");
    }
}