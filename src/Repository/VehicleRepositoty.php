<?php

use App\Entity\Vehicle;
namespace App\Repository;
use Doctrine\DBAL\Connection;

class VehicleRepository
{
    private Connection $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

   
}