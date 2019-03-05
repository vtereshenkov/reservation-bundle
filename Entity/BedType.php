<?php

namespace Vtereshenkov\ReservationBundle\Entity;

class BedType
{
    private $title;

    private $id;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
