<?php

namespace App\Entity;

class contract
{
//     - id (INT - clé unique du contrat)
// - vehicle_uid (CHAR(255) - uid du Vehicle associé au contrat)
// - customer_uid (CHAR(255) - uid du Customer associé au contrat)
// - sign_datetime (DATETIME - Date + heure de signature du contrat)
// - loc_begin_datetime (DATETIME - Date + heure de début de la location)
// - loc_end_ datetime (DATETIME - Date + heure de fin de la location)
// - returning_datetime (DATETIME - Date + heure de rendu du véhicule)
// - price (MONEY - Prix facturé pour le contrat)

    private int $id;
    private string $vehicleUid;
    private string $customerUid;
    private datetime $signDatetime;
    private datetime $locBeginDatetime;
    private datetime $locEndDatetime;
    private datetime $returningDatetime;
    private float $price;

    //getters
    public function getId():int
    {
        return $this->id;
    }

    public function getVehicleUid(): string 
    {
        return $this->vehicleUid;
    }

    public function getCustomerUid(): int
    {
        return $this->customerUid;
    }

    public function getLocBeginDatetime():datetime
    {
        return $this->locBeginDatetime;
    }

      public function getLocEndDatetime():datetime
    {
        return $this->locEndDatetime;
    }

      public function geRerreturningDatetime():datetime
    {
        return $this->returningDatetime;
    }

       public function getPrice():float
    {
        return $this->price;
    }

    //setters
    public function set
}