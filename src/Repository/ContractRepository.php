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
    $sql = 
    $smtp = 
    $result = 


    // Rechercher les contrats en cours ou en retard
    // Lister les contrats d’un client
    // Vérifier si un contrat est intégralement payé

    public function getLateContracts(): array
    public function getContractsByCustomer($customerId): array
    public function isContractFullyPaid($contractId): bool
}




