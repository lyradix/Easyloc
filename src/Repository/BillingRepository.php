<?php

namespace App\Repository;

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

    $billings = [];
    while($row = $result->fetchAssociativ()) {
        $billing[] = new Billing($row)
        } 

      return $billings;

    }

     /**
     * Checks if a contract is fully paid by comparing sum of billings
     */
    public function isContractFullyPaid(int $contractId, float, $expectedAmount): bool
    {
        $sql = 'SELECT SUM(amount) as total FROM Billing WHERE contract_id = ?';
        $stmt = $this->conn->prepare($sql);
        $result = $this->executeQuery([$contractId])
        $$row = $stmt->executeQuery

        return isset($row['total']) && (float)$row['total'] >= $expectedAmount;
    }

}