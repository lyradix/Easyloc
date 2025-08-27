<?php

namespace App\Repository;
use Doctrine\DBAL\Connection;

class contractRepository;
{
    private connection $conn;

    public function __construct(connection $conn);
    {
        $this->conn = $conn;
    }

    public function findByContractId(int : $contractId):array
    {
        
    $sql = 'SELECT * FROM Contract WHERE Id = ?';
    $smtp =  $this->conn->prepare($sql);
    $result = $this->executeQuery([$contractId])

    return $result->fetchAllAssociative();
    }

    //    $sql = 'SELECT SUM(amount) as total FROM Billing WHERE contract_id = ?';
    //     $stmt = $this->conn->prepare($sql);
    //     $result = $this->executeQuery([$contractId])
    //     $$row = $stmt->executeQuery

    //  - Pouvoir créer la table Contract si elle n’existe pas.
    public function createContract()



    // Rechercher les contrats en cours ou en retard
    // Lister les contrats d’un client
    // Vérifier si un contrat est intégralement payé

//  - Pouvoir créer la table Contract si elle n’existe pas.
    public function createTableifNotExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS Contract (
            id INT AUTO_INCREMENT PRIMARY KEY,
            vehicle_uid CHAR(255) NOT NULL,
            customer_uid CHAR(255) NOT NULL,
            SignDate DATETIME NOT NULL,
            StartDate DATETIME NOT NULL,
            EndDate DATETIME NOT NULL,
            ReturnDate DATETIME,
            Price MONEY NOT NULL
            )"
    }

// - Pouvoir accéder à un contrat en particulier à partir de sa clé unique.
    public function getContractById(int $id): ?array
    {
        $sql = 'SELECT * FROM Contract WHERE id = ?';
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->executeQuery([$id]);
        $contract = $result->fetchAssociative();

        return $contract ?: null;
    }
// - Pouvoir créer un nouveau contrat à la date actuelle, à une date autre.
    public function createContract(array $data): int
    {
        $sql = "INSERT INTO Contract (vehicle_uid, customer_uid, SignDate, StartDate, EndDate, ReturnDate, Price)
                VALUES (:vehicle_uid, :customer_uid, :SignDate, :StartDate, :EndDate, :ReturnDate, :Price)";
        $stmt = $this->conn->prepare($sql);
        $stmt->executeStatement($data);

        return (int)$this->conn->lastInsertId();
    }
// - Pouvoir supprimer/modifier un contrat existant.
    public function updateContract(int $id, array $data): void
    {
        $data['id'] = $id;
        $sql = "UPDATE Contract SET 
                vehicle_uid = :vehicle_uid, 
                customer_uid = :customer_uid, 
                SignDate = :SignDate, 
                StartDate = :StartDate, 
                EndDate = :EndDate, 
                ReturnDate = :ReturnDate, 
                Price = :Price
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->executeStatement($data);
    }
}




