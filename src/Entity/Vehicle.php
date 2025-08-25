<?php

// - uid (UUID - Identifiant unique du document)
// - licence_plate (CHAR(255) - Immatriculation du véhicule)
// - informations (TEXT - Notes sur le véhicule, par exemple dégradations)
// - km (INT - Kilométrage du véhicule)

namespace App\Entity;

class Vehicle
{
    private string $uid;
    private string $licencePlate;
    private string $informations;
    private int $km;

    //getters
    public function getUid(): string
    {
        return $this->uid;
    }

    public function getLicencePlate(): string
    {
        return $this->licencePlate;
    }

    public function getInformations(): string
    {
        return $this->informations;
    }

    public function getKm(): int
    {
        return $this->km;
    }

    //setters
    public function setUid(string $uid)
    {
        $this->uid = $uid;
    }

    public function setLicencePlate(string $licencePlate)
    {
        $this->licencePlate = $licencePlate;
    }

    public function setInformations(string $informations)
    {
        $this->informations = $informations;
    }

    public function setKm(int $km)
    {
        $this->km = $km;
    }
}