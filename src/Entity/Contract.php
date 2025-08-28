<?php

namespace EasyLoc\Entity;

class Contract
{
    private int $id;
    private string $vehicleUid;
    private string $customerUid;
    private \DateTime $signDatetime;
    private \DateTime $locBeginDatetime;
    private \DateTime $locEndDatetime;
    private \DateTime $returningDatetime;
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

    public function getCustomerUid(): string
    {
        return $this->customerUid;
    }

    public function getLocBeginDatetime(): \DateTime
    {
        return $this->locBeginDatetime;
    }

    public function getLocEndDatetime(): \DateTime
    {
        return $this->locEndDatetime;
    }

    public function getReturningDatetime(): \DateTime
    {
        return $this->returningDatetime;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    //setters
    public function setId(int $id)
    {
        $this->id = $id;
    }
    public function setVehicleUid(string $vehicleUid)
    {
        $this->vehicleUid = $vehicleUid;
    }
    public function setCustomerUid(string $customerUid)
    {
        $this->customerUid = $customerUid;
    }
    public function setSignDatetime(\DateTime $signDatetime)
    {
        $this->signDatetime = $signDatetime;
    }
    public function setLocBeginDatetime(\DateTime $locBeginDatetime)
    {
        $this->locBeginDatetime = $locBeginDatetime;
    }
    public function setLocEndDatetime(\DateTime $locEndDatetime)
    {
        $this->locEndDatetime = $locEndDatetime;
    }
    public function setReturningDatetime(\DateTime $returningDatetime)
    {
        $this->returningDatetime = $returningDatetime;
    }
    public function setPrice(float $price)
    {
        $this->price = $price;
    }
}