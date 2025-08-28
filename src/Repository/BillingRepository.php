<?php

namespace EasyLoc\Repository;

use App\Entiry\Billing;
use Doctrine\DBAL\Connection;

class BillingRepository
{
    private Connection $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }
    /**
    *Return an array of Billing entities for a given conttract
    */
    public function findByContractId(int : $contractId): array
    {
    $sql = 'SELECT * FROM Billing WHERE contract_id = ?';
    $stmt = $this->conn->prepare($sql);
    $result = $stmt->executeQuery([$contract])

    $billing = [];
    while($row = $result->fetchAssociativ()) {
        $billing[] = new Billing($row)
        } 

      return $billing;

    }

     /**
     * Checks if a contract is fully paid by comparing sum of billing
     */
    public function isContractFullyPaid(int $contractId, float, $expectedAmount): bool
    {
        $sql = 'SELECT SUM(amount) as total FROM Billing WHERE contract_id = ?';
        $stmt = $this->conn->prepare($sql);
        $result = $this->executeQuery([$contractId])
        $$row = $stmt->executeQuery

        return isset($row['total']) && (float)$row['total'] >= $expectedAmount;
    }


//     Calculer les montants dus
    public function calculateTotalBilled(int $contractId): float
    {
        $sql = 'SELECT SUM(amount) as total FROM Billing WHERE contract_id = ?';
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->executeQuery([$contractId]);
        $row = $result->fetchAssociative();

        return isset($row['total']) ? (float)$row['total'] : 0.0;
    }

// Regrouper les paiements par client ou par période
}