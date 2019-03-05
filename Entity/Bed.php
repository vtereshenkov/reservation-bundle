<?php

namespace Vtereshenkov\ReservationBundle\Entity;

class Bed
{
    private $number;

    private $priceDay;

    private $priceMonth;

    private $id;

    private $type;

    private $room;

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getPriceDay(): ?float
    {
        return $this->priceDay;
    }

    public function setPriceDay(float $priceDay): self
    {
        $this->priceDay = $priceDay;

        return $this;
    }

    public function getPriceMonth(): ?float
    {
        return $this->priceMonth;
    }

    public function setPriceMonth(float $priceMonth): self
    {
        $this->priceMonth = $priceMonth;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?BedType
    {
        return $this->type;
    }

    public function setType(?BedType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): self
    {
        $this->room = $room;

        return $this;
    }
}
