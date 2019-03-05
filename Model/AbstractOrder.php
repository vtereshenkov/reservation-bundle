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

use Symfony\Component\Security\Core\User\UserInterface;
use Vtereshenkov\ReservationBundle\Entity\Status;
use Vtereshenkov\ReservationBundle\Entity\PaymentStatus;
use Vtereshenkov\ReservationBundle\Entity\ResidentStatus;
use Vtereshenkov\ReservationBundle\Entity\PaymentMethod;
use Vtereshenkov\ReservationBundle\Entity\Client;
use Vtereshenkov\ReservationBundle\Entity\Location;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * AbstractOrder
 *
 * @author Vitaliy Tereshenkov <vitaliytereshenkov@gmail.com>
 */
class AbstractOrder
{

    protected $number;
    protected $date;
    protected $sum;
    protected $deposit;
    protected $depositFlag;
    protected $tax;
    protected $totalSum;
    protected $needToPay;
    protected $alreadyPayed = 0.0;
    protected $needToPayTotal;
    protected $alreadyPayedTotal = 0.0;
    protected $numberOfPeople;
    protected $id;
    protected $status;
    protected $paymentStatus;
    protected $residentStatus;
    protected $paymentMethod;
    protected $client;
    protected $location;
    protected $createdManager;
    protected $editedManager;

    public function __construct()
    {
        $this->location = new ArrayCollection();
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;

        return $this;
    }

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

    public function getDeposit(): ?float
    {
        return $this->deposit;
    }

    public function setDeposit(?float $deposit): self
    {
        $this->deposit = $deposit;

        return $this;
    }

    public function getDepositFlag(): ?int
    {
        return $this->depositFlag;
    }

    public function setDepositFlag(?int $depositFlag): self
    {
        $this->depositFlag = $depositFlag;

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

    public function getNumberOfPeople(): ?int
    {
        return $this->numberOfPeople;
    }

    public function setNumberOfPeople(int $numberOfPeople): self
    {
        $this->numberOfPeople = $numberOfPeople;

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

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?PaymentMethod $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getLocation(): Collection
    {
        return $this->location;
    }

    public function addLocation(Location $location): self
    {
        if (!$this->location->contains($location)) {
            $this->location[] = $location;
        }

        return $this;
    }

    public function removeBed(Location $location): self
    {
        if ($this->location->contains($location)) {
            $this->location->removeElement($location);
        }

        return $this;
    }

    public function getCreatedManager(): ?UserInterface
    {
        return $this->createdManager;
    }

    public function setCreatedManager(?UserInterface $createdManager): self
    {
        $this->createdManager = $createdManager;

        return $this;
    }

    public function getEditedManager(): ?UserInterface
    {
        return $this->editedManager;
    }

    public function setEditedManager(?UserInterface $editedManager): self
    {
        $this->editedManager = $editedManager;

        return $this;
    }

}
