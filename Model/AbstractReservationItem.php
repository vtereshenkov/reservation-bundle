<?php

/*
 * This file is part of the VtereshenkovReservationBundle package.
 *
 * (c) Vitaliy Tereshenkov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vtereshenkov\ReservationBundle\Model;

use Vtereshenkov\ReservationBundle\Entity\Status;
use Vtereshenkov\ReservationBundle\Entity\PaymentStatus;
use Vtereshenkov\ReservationBundle\Entity\ResidentStatus;
use Vtereshenkov\ReservationBundle\Entity\Location;
use Vtereshenkov\ReservationBundle\Entity\Bed;
use Vtereshenkov\ReservationBundle\Entity\Reservation;

/**
 * AbstractReservationItem
 *
 * @author Vitaliy Tereshenkov <vitaliytereshenkov@gmail.com>
 */
abstract class AbstractReservationItem
{
    protected $date;

    protected $sum;

    protected $tax;

    protected $totalSum;

    protected $needToPay;

    protected $alreadyPayed;

    protected $needToPayTotal;

    protected $alreadyPayedTotal;

    protected $id;

    protected $status;

    protected $paymentStatus;

    protected $residentStatus;

    protected $location;

    protected $bed;

    protected $reservation;

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getSum(): ?float
    {
        return $this->sum;
    }

    public function setSum(float $sum): self
    {
        $this->sum = $sum;

        return $this;
    }

    public function getTax(): ?float
    {
        return $this->tax;
    }

    public function setTax(float $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function getTotalSum(): ?float
    {
        return $this->totalSum;
    }

    public function setTotalSum(float $totalSum): self
    {
        $this->totalSum = $totalSum;

        return $this;
    }

    public function getNeedToPay(): ?float
    {
        return $this->needToPay;
    }

    public function setNeedToPay(float $needToPay): self
    {
        $this->needToPay = $needToPay;

        return $this;
    }

    public function getAlreadyPayed(): ?float
    {
        return $this->alreadyPayed;
    }

    public function setAlreadyPayed(float $alreadyPayed): self
    {
        $this->alreadyPayed = $alreadyPayed;

        return $this;
    }

    public function getNeedToPayTotal(): ?float
    {
        return $this->needToPayTotal;
    }

    public function setNeedToPayTotal(float $needToPayTotal): self
    {
        $this->needToPayTotal = $needToPayTotal;

        return $this;
    }

    public function getAlreadyPayedTotal(): ?float
    {
        return $this->alreadyPayedTotal;
    }

    public function setAlreadyPayedTotal(float $alreadyPayedTotal): self
    {
        $this->alreadyPayedTotal = $alreadyPayedTotal;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPaymentStatus(): ?PaymentStatus
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(?PaymentStatus $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    public function getResidentStatus(): ?ResidentStatus
    {
        return $this->residentStatus;
    }

    public function setResidentStatus(?ResidentStatus $residentStatus): self
    {
        $this->residentStatus = $residentStatus;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getBed(): ?Bed
    {
        return $this->bed;
    }

    public function setBed(?Bed $bed): self
    {
        $this->bed = $bed;

        return $this;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): self
    {
        $this->reservation = $reservation;

        return $this;
    }
}
