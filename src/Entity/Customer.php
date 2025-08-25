<?php

// - uid (UUID - Identifiant unique du document)
// - first_name (CHAR(255) - Nom)
// - second_name (CHAR(255) - Prénom)
// - address (CHAR(255) - Adresse complète)
// - permit_number (CHAR(255) -numéro de permis)

namespace App\Entity;
class Customer
{
    private string $uid;
    private string $firstName;
    private string $secondName;
    private string $address;
    private string $permitNumber;

    //getters
    public function getUid(): string
    {
        return $this->uid;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getSecondName(): string
    {
        return $this->secondName;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getPermitNumber(): string
    {
        return $this->permitNumber;
    }

    //setters
    public function setUid(string $uid)
    {
        $this->uid = $uid;
    }

    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;
    }

    public function setSecondName(string $secondName)
    {
        $this->secondName = $secondName;
    }

    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    public function setPermitNumber(string $permitNumber)
    {
        $this->permitNumber = $permitNumber;
    }
}