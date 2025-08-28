<?php

// faire des requetes nosql sur mongoDB pour recuperer les customers

namespace EasyLoc\Repository;
use Doctrine\DBAL\Connection;
class CustomerRepository
{
    private Connection $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

//     - Pouvoir créer/modifier/supprimer un document Customer et Vehicle.
    public function createCustomer(array $customerData): void
    {
        $sql = "INSERT INTO Customer (uid, firstName, lastName, email, phone, address, permitNumber) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->executeStatement([
            $customerData['uid'],
            $customerData['firstName'],
            $customerData['lastName'],
            $customerData['email'],
            $customerData['phone'],
            json_encode($customerData['address']),
            $customerData['permitNumber']
        ]);
    }
// - Pouvoir rechercher un Customer à partir de son nom+prénom.
    public function findByName(string $firstName, string $lastName): ?array
    {
        $sql = 'SELECT * FROM Customer WHERE firstName = ? AND lastName = ?';
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->executeQuery([$firstName, $lastName]);
        $customer = $result->fetchAssociative();

        return $customer ?: null;
    }
// - Pouvoir rechercher un véhicule à partir de son numéro d’immatriculation.
    public function findByLicensePlate(string $licensePlate): ?array
    {
        $sql = 'SELECT * FROM Vehicle WHERE licensePlate = ?';
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->executeQuery([$licensePlate]);
        $vehicle = $result->fetchAssociative();

        return $vehicle ?: null;
    }
// - Pouvoir compter les véhicules ayant plus (respectivement moins) d’un certain kilométrage.
    public function countVehiclesByMileage(int $mileage, bool $greaterThan = true): int
    {
        $operator = $greaterThan ? '>' : '<';
        $sql = "SELECT COUNT(*) as count FROM Vehicle WHERE mileage $operator ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->executeQuery([$mileage]);
        $row = $result->fetchAssociative();

        return (int)($row['count'] ?? 0);
    }
}

