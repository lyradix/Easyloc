<?php

namespace App\Entity;

class Billing
{
    private int $id;
    private int $contractId;
    private float $amount;

    // Constructor to initialize properties
    public function __construct($values = array())
    {
       if(!empty($values)) {
         $this->hydrate($values);
       }   
    }

    public function hydrate(array $datas): void
    {
        foreach ($datas as $key => $value) {
            $method = 'set'.ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }   
        }
    } 

    //getters
    public function getId():int
    {
        return $this->id;
    }

    public function getContractId():int
    {
        return $this->contractId;
    }

    public function getAmount():float
    {
        return $this->amount;
    }

    //setters
    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function setContractId(int $contractId)
    {
        $this->contractId = $contractId;
    }

    public function setAmount(float $amount)
    {
        $this->amount = $amount;
    }

}
