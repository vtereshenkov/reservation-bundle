<?php

/*
 * This file is part of the VtereshenkovReservationBundle package.
 *
 * (c) Vitaliy Tereshenkov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vtereshenkov\ReservationBundle\Service;

use Vtereshenkov\ReservationBundle\Entity\Location;
use Vtereshenkov\ReservationBundle\Entity\Client;
use Vtereshenkov\ReservationBundle\Entity\Bed;
use Vtereshenkov\ReservationBundle\Entity\ReservationItem;
use Vtereshenkov\ReservationBundle\Entity\Reservation;
use Vtereshenkov\ReservationBundle\Entity\Order;
use Vtereshenkov\ReservationBundle\Entity\PaymentStatus;
use Vtereshenkov\ReservationBundle\Entity\Status;
use Vtereshenkov\ReservationBundle\Entity\ResidentStatus;
use Vtereshenkov\ReservationBundle\Entity\PaymentMethod;

/**
 * ReservationManager
 *
 * @author Vitaliy Tereshenkov <vitaliytereshenkov@gmail.com>
 */
class ReservationManager
{

    private $em;

    /**
     *  Helper for format data
     *
     * @var \Vtereshenkov\ReservationBundle\Service\Format
     */
    private $fh;

    public function __construct(\Doctrine\ORM\EntityManager $em, \Vtereshenkov\ReservationBundle\Service\Format $fh)
    {
        $this->em = $em;
        $this->fh = $fh;
    }

    /**
     * Get rooms location list
     *
     * @param string $selectLocation - default select location
     * @return array
     * @throws \Exception
     */
    public function getRoomLocation(string $selectLocation = ""): array
    {
        try {
            $locations = $this->em->getRepository(Location::class)->findBy(['status' => 1], ["sortNumber" => "ASC"]);
        } catch (\Exception $ex) {
            throw new \Exception("Get data getRoomLocation. " . $ex->getMessage());
        }

        $locations = $this->fh->formatLocationListData($locations);
        foreach ($locations as $key => $location) {
            if ($selectLocation == "" && $key == 0) {
                $locations[$key]['selected'] = true;
                continue;
            }
            if ($selectLocation != "" && $location['title'] == $selectLocation) {
                $locations[$key]['selected'] = true;
                continue;
            }
            $locations[$key]['selected'] = false;
        }

        return $locations;
    }

    /**
     * Get reservation client
     *
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getClientsList(int $id = null): array
    {
        $clients = [];
        try {
            $clientRepository = $this->em->getRepository(Client::class);
            if (!empty($id)) {
                $clients = $clientRepository->findBy(['id' => (int) $id]);
            } else {
                $clients = $clientRepository->findAll();
            }
        } catch (\Exception $ex) {
            throw new \Exception("Get data clients. " . $ex->getMessage());
        }

        return $clients;
    }

    /**
     * Get room-bed list for calendar
     *
     * @param int $locationId
     */
    public function getRoomListCalendar($locationId = null): array
    {
        $result = [];
        try {
            $bedRepository = $this->em->getRepository(Bed::class);
            if (!empty($locationId)) {
                $beds = $bedRepository->createQueryBuilder('b')
                                ->innerJoin('b.room', 'r')
                                ->innerJoin('r.location', 'l')
                                ->where('l.id = :locations_id')
                                ->setParameter('locations_id', (int) $locationId)
                                ->orderBy('b.id', 'ASC')
                                ->getQuery()->getResult();
            } else {
                $beds = $bedRepository->findBy([], ['id' => 'ASC']);
            }
        } catch (\Exception $ex) {
            throw new \Exception("Get Calendar room list. " . $ex->getMessage());
        }

        /* Create result list */
        foreach ($beds as $bed) {
            if (!empty($bed->getRoom()) && !empty($bed->getType())) {
                $roomForBed = $bed->getRoom();
                if (!empty($roomForBed->getType())) {
                    $roomType = $roomForBed->getType()->getTitle();
                    $result[] = array(
                        'roomName' => $roomType{0} . $roomForBed->getNumber() . '/' . $bed->getNumber(),
                        'roomType' => $roomType{0},
                        'roomData' => [
                            'busy' => [],
                            'reservation' => [],
                            'tenant' => '',
                            'room' => [
                                'id' => $roomForBed->getId(),
                                'room_number' => $roomForBed->getNumber(),
                                'title' => (!empty($roomForBed->getTitle()) ? $roomForBed->getTitle() : '' ),
                                'type' => $roomType{0},
                                'location' => $roomForBed->getLocation()->getTitle(),
                                'location_id' => $roomForBed->getLocation()->getId()
                            ],
                            'bed' => array(
                                'id' => $bed->getId(),
                                'bed_number' => $bed->getNumber(),
                                'price_per_day' => (!empty($bed->getPriceDay()) ? $bed->getPriceDay() . ' USD' : "0 USD" ),
                                'price_per_month' => (!empty($bed->getPriceMonth()) ? $bed->getPriceMonth() . ' USD' : "0 USD" ),
                                'type' => (!empty($bed->getType()) ? $bed->getType()->getTitle() : 'Bunk')
                            )
                        ]
                    );
                }
            }
        }
        return $result;
    }

    /**
     * Get room reservation info
     *
     * @param array $rooms prepared room list in method getRoomListCalendar
     * @param string $rangeStart - range start date
     * @param string $rangeEnd - range end date
     * @param int $active
     * @return array
     * @throws \Exception
     */
    public function getRoomReservation(array $rooms, string $rangeStart, string $rangeEnd, int $active = 1): array
    {
        $result = $rooms;
        $dateRange = ['start' => $rangeStart, 'end' => $rangeEnd];
        if (!empty($rooms)) {
            foreach ($rooms as $key => $room) {
                /* Get reservation item for room */
                try {
                    $reservationItemsDateRange = $this->getReservationItems($room['roomData']['room']['location_id'], $room['roomData']['bed']['id'], $dateRange, array(), $active);
                    /* Get reservation status for item */
                    $rangeForRoom = $this->getRangeForRoom($reservationItemsDateRange);                   
                } catch (\Exception $ex) {
                    throw new \Exception($ex->getMessage());
                }
                $result[$key]['roomData']['busy']['range'] = $rangeForRoom['busy'];
                $result[$key]['roomData']['reservation']['range'] = $rangeForRoom['reservation'];
                $result[$key]['roomData']['part_payed']['range'] = $rangeForRoom['part_payed'];
                $result[$key]['roomData']['order'] = $rangeForRoom['order'];
            }

            /* Group bed for rooms */
            $formatResult = [];
            foreach ($result as $value) {
                $roomId = $value['roomData']['room']['id'];
                $roomTitle = $value['roomData']['room']['title'];
                if (empty($formatResult[$roomId])) {
                    $formatResult[$roomId]['group_name'] = $roomTitle;
                    $formatResult[$roomId]['group_data'][] = $value;
                } else {
                    $formatResult[$roomId]['group_data'][] = $value;
                }
            }
            $result = $this->fh->numberIndexArrayConvert($formatResult);
        }

        return $result;
    }

    /**
     * Search reservation items
     *
     * @param  int $locationId
     * @param  int $bedId
     * @param  array $dateRange (format Y-m-d)
     * @param  array $reservationIds
     * @param  int $reservationItemStatus
     * @throws \Exception
     * @return array
     */
    public function getReservationItems(int $locationId = null, int $bedId = null, array $dateRange = [], array $reservationIds = [], int $reservationItemStatus = null): array
    {
        $result = [];
        if (!empty($reservationIds) || (!empty($locationId) && !empty($bedId) && !empty($dateRange))) {
            $filters = [];
            try {
                $reservationItemsRepository = $this->em->getRepository(ReservationItem::class);
                if (!empty($reservationIds)) {
                    $filters['reservation'] = $reservationIds;
                    if (!empty($reservationItemStatus)) {
                        $filters['status'] = (int) $reservationItemStatus;
                    }
                    $items = $reservationItemsRepository->findBy($filters);
                } else {
                    $query = $reservationItemsRepository->createQueryBuilder('ri')
                            ->where('ri.location = :locations_id')
                            ->andWhere('ri.bed = :bed_id')
                            ->andWhere('ri.date BETWEEN :start AND :end')
                            ->setParameter('locations_id', (int) $locationId)
                            ->setParameter('bed_id', (int) $bedId)
                            ->setParameter('start', new \DateTime($dateRange['start']))
                            ->setParameter('end', new \DateTime($dateRange['end']))
                            ->orderBy('ri.id', 'ASC');
                    if (!empty($reservationItemStatus)) {
                        $query->andWhere('ri.status = :status')->setParameter('status', (int) $reservationItemStatus);
                    }
                    $items = $query->getQuery()->getResult();
                }
            } catch (\Exception $ex) {
                throw new \Exception("Get ReservationItems. " . $ex->getMessage());
            }
            $result = $this->fh->formatReservationItem($items);
        }
        return $result;
    }

    /**
     * Get range busy, reservation and part payed for room
     *
     * @param  array $items (ReservationItem)
     * @return array
     */
    public function getRangeForRoom(array $items): array
    {

        $result = [
            'busy' => [],
            'reservation' => [],
            'part_payed' => [],
            'order' => []
        ];

        /* Get reservation ids */
        $reservationIds = [];
        foreach ($items as $value) {
            if (!empty($value['reservation'])) {
                $reservationIds[] = (int) $value['reservation'];
            }
        }
         
        if (!empty($reservationIds)) {
            $reservationIds = array_values(array_unique($reservationIds));

            /* Get reservation data */
            try {
                $reservations = $this->em->getRepository(Reservation::class)->findBy(['id' => $reservationIds]);
            } catch (\Exception $ex) {
                throw new \Exception("getRangeForRoom APP_RESERVATIONS_ID. " . $ex->getMessage());
            }
            $reservations = $this->fh->formatReservationListData($reservations);
           
            /* Get order client for reservation */
            if (!empty($reservations)) {
                $reservations = $this->getClientDataForReservation($reservations);
            }
            /* Reindex reservation */
            $reindexReservation = [];
            foreach ($reservations as $reservation) {
                $reindexReservation[$reservation['id']] = $reservation;
            }
            
            /* Create date range for room */
            foreach ($items as $value) {
                if (!empty($value['reservation'])) {
                    $statusReservation = $reindexReservation[$value['reservation']]['order_payment_status']['id'];
                    if ($statusReservation == 1) {
                        /* Order payed */
                        $result['busy'][] = array(
                            'day' => $value['date']->format('Y-m-d'),
                            'client' => $reindexReservation[$value['reservation']]['client'],
                            'sum' => round($value['sum']['value'], 2),
                            'status' => $value['status'],
                            'payment_status' => $value['payment_status'],
                            'resident_status' => $value['resident_status'],
                            'check_in' => $reindexReservation[$value['reservation']]['check_in']->format('Y-m-d'),
                            'check_out' => $reindexReservation[$value['reservation']]['check_out']->format('Y-m-d'),
                            'order' => $reindexReservation[$value['reservation']]['order']
                        );
                    } elseif ($statusReservation == 3) {
                        /* Partially_payed */
                        $result['part_payed'][] = array(
                            'day' => $value['date']->format('Y-m-d'),
                            'client' => $reindexReservation[$value['reservation']]['client'],
                            'sum' => round($value['sum']['value'], 2),
                            'status' => $value['status'],
                            'payment_status' => $value['payment_status'],
                            'resident_status' => $value['resident_status'],
                            'check_in' => $reindexReservation[$value['reservation']]['check_in']->format('Y-m-d'),
                            'check_out' => $reindexReservation[$value['reservation']]['check_out']->format('Y-m-d'),
                            'order' => $reindexReservation[$value['reservation']]['order']
                        );
                    } else {
                        /* Order not payed */
                        $result['reservation'][] = array(
                            'day' => $value['date']->format('Y-m-d'),
                            'client' => $reindexReservation[$value['reservation']]['client'],
                            'sum' => round($value['sum']['value'], 2),
                            'status' => $value['status'],
                            'payment_status' => $value['payment_status'],
                            'resident_status' => $value['resident_status'],
                            'check_in' => $reindexReservation[$value['reservation']]['check_in']->format('Y-m-d'),
                            'check_out' => $reindexReservation[$value['reservation']]['check_out']->format('Y-m-d'),
                            'order' => $reindexReservation[$value['reservation']]['order']
                        );
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get client data for reservations from order
     *
     * @param  array $reservations
     * @return array
     */
    public function getClientDataForReservation(array $reservations): array
    {
        $orderIds = [];
        foreach ($reservations as $reservation) {
            if (!empty($reservation['order']['id'])) {
                $orderIds[] = (int) $reservation['order']['id'];
            }
        }
        $orderIds = array_values(array_unique($orderIds));
        try {
            $items = $this->em->getRepository(Order::class)->findBy(['id' => $orderIds]);
        } catch (\Exception $ex) {
            throw new \Exception("Get client data from order for reservation. " . $ex->getMessage());
        }
        $orders = $this->fh->formatOrderDataForTable($items);
      
        $reindexOrders = [];
        foreach ($orders as $value) {
            $reindexOrders[$value['id']] = $value;
            /* Get full client info */
            try {
                $reindexOrders[$value['id']]['client'] = $this->fh->formatClientList($this->getClientsList((int) $value['client']['id']))[0];
            } catch (\Exception $ex) {
                throw new \Exception("Get client data from order for reservation. " . $ex->getMessage());
            }
        }
         
        /* Add client data to reservation */
        foreach ($reservations as $key => $reservation) {
            if (!empty($reservation['order']['id'])) {
                $reservations[$key]['client'] = $reindexOrders[$reservation['order']['id']]['client'];
                $reservations[$key]['order_payment_status'] = $reindexOrders[$reservation['order']['id']]['payment_status'];
                $reservations[$key]['order'] = [
                    'id' => $reservation['order']['id'],
                    'number' => $reindexOrders[$reservation['order']['id']]['number'],
                    'date' => $reindexOrders[$reservation['order']['id']]['date']->format('Y-m-d H:i:s'),
                    'summ' => $reindexOrders[$reservation['order']['id']]['sum'],
                    'tax' => $reindexOrders[$reservation['order']['id']]['tax'],
                    'total_sum' => $reindexOrders[$reservation['order']['id']]['total_sum'],
                    'need_to_pay' => $reindexOrders[$reservation['order']['id']]['need_to_pay'],
                    'alredy_payed' => $reindexOrders[$reservation['order']['id']]['already_payed'],
                    'need_to_pay_total' => $reindexOrders[$reservation['order']['id']]['need_to_pay_total'],
                    'alredy_payed_total' => $reindexOrders[$reservation['order']['id']]['already_payed_total'],
                    'status' => $reindexOrders[$reservation['order']['id']]['status'],
                    'resident_status' => $reindexOrders[$reservation['order']['id']]['resident_status'],
                    'payment_status' => $reindexOrders[$reservation['order']['id']]['payment_status'],
                    'number_of_people' => $reindexOrders[$reservation['order']['id']]['number_of_people'],
                    'payment_method' => $reindexOrders[$reservation['order']['id']]['payment_method'],
                    'deposit' => $reindexOrders[$reservation['order']['id']]['deposit'],
                    'deposit_flag' => $reindexOrders[$reservation['order']['id']]['deposit_flag']
                ];
            } else {
                $reservations[$key]['client'] = [];
                $reservations[$key]['order_payment_status'] = [];
                $reservation[$key]['order'] = [];
            }
        }

        return $reservations;
    }

    /**
     * Get order status lists
     *
     * @param boolean $all - get all list status
     * @return boolean
     * @throws \Exception
     */
    public function getOrderStatus(bool $all = false): array
    {
        try {
            $statusPayment = $this->em->getRepository(PaymentStatus::class)->findAll();
            if (false != $all) {
                $status = $this->em->getRepository(Status::class)->findAll();
                $statusResident = $this->em->getRepository(ResidentStatus::class)->findAll();
            }
        } catch (\Exception $ex) {
            throw new \Exception("Get data select item for order. " . $ex->getMessage());
        }
        $statusPayment = $this->fh->setSelectValue($statusPayment, 'Not payed');

        if (false == $all) {
            return $statusPayment;
        } else {
            $status = $this->fh->setSelectValue($status, 'Active');
            $statusResident = $this->fh->setSelectValue($statusResident, 'Not settled');
            return [
                'status' => $status,
                'payment_status' => $statusPayment,
                'resident_status' => $statusResident
            ];
        }
    }

    /**
     * Get payment methods
     *
     * @return array
     */
    public function getPaymentMethod(): array
    {
        $result = [];
        try {
            $result = $this->em->getRepository(PaymentMethod::class)
                    ->findAll();
        } catch (\Exception $ex) {
            throw new \Exception("Find items getPaymentMethod. " . $ex->getMessage());
        }

        return $result;
    }

    /**
     * Check reservation date range
     *
     * @param int $locationId
     * @param int $bedId
     * @param string $startDate (format Y-m-d)
     * @param string $endDate (format Y-m-d)
     * @param boolean $onlyActive
     * @param int $reservationId
     * @return boolean
     */
    public function checkDateRangeReservation(int $locationId, int $bedId, string $startDate, string $endDate, bool $onlyActive = false, int $reservationId = null): bool
    {
        $rangeDates = $this->fh->createRangeDates(new \DateTime($startDate), new \DateTime($endDate));

        $rangeFree = true;
        foreach ($rangeDates as $value) {
            try {
                $reserItemsRepository = $this->em->getRepository(ReservationItem::class);
                $query = $reserItemsRepository->createQueryBuilder('ri')
                        ->where('ri.location = :locations_id')
                        ->andWhere('ri.bed = :bed_id')
                        ->andWhere('ri.date = :date')
                        ->setParameter('locations_id', (int) $locationId)
                        ->setParameter('bed_id', (int) $bedId)
                        ->setParameter('date', new \DateTime($value))
                        ->orderBy('ri.id', 'ASC');
                if (true == $onlyActive) {
                    $query->andWhere('ri.status = :status')->setParameter('status', 1);
                }
                $items = $query->getQuery()->getResult();
            } catch (\Exception $ex) {
                throw new \Exception("checkDateRangeReservation. " . $ex->getMessage());
            }
            if (empty($reservationId)) {
                if (!empty($items)) {
                    $rangeFree = false;
                    break;
                }
            } else {
                /* If find reservation item have one reservation range free = true (range not modif), else return false */
                $isCurrentReservation = true;
                $formatItems = $this->fh->formatReservationItem($items);
                foreach ($formatItems as $_item) {
                    if ($reservationId != $_item['reservation']) {
                        $isCurrentReservation = false;
                        break;
                    }
                }
                if (false == $isCurrentReservation) {
                    $rangeFree = false;
                    break;
                }
            }
        }

        return $rangeFree;
    }

    /**
     * Create room reservation
     *
     * @param array $dataInsert
     * @return Reservation
     */
    public function createRoomReservation($dataInsert): Reservation
    {
        try {
            $bed = $this->em->find(Bed::class, (int) $dataInsert['bed']);

            $reservation = new Reservation();
            $reservation->setCheckIn(new \DateTime($dataInsert['check-in']));
            $reservation->setCheckOut(new \DateTime($dataInsert['check-out']));
            $reservation->setBed($bed);
            $reservation->setOrder($dataInsert['order']);

            $this->em->persist($reservation);
            $this->em->flush();
        } catch (\Exception $ex) {
            throw new \Exception("Create room reservation. " . $ex->getMessage());
        }

        //Create reservation item
        $rangeDates = $this->fh->createRangeDates(new \DateTime($dataInsert['check-in']), new \DateTime($dataInsert['check-out']));
        foreach ($rangeDates as $_date) {
            $insertData = [
                'date' => new \DateTime($_date),
                'reservation' => $reservation,
                'bed' => $bed,
                'location' => $dataInsert['location'],
                'sum' => (float) $dataInsert['sum'],
                'tax' => (float) $dataInsert['tax'],
                'total_sum' => (float) $dataInsert['total_sum'],
                'need_to_pay' => (float) $dataInsert['need_to_pay'],
                'need_to_pay_total' => (float) $dataInsert['need_to_pay_total'],
                'status' => $dataInsert['status'],
                'payment_status' => $dataInsert['status_payment'],
                'resident_status' => $dataInsert['status_resident']
            ];
            try {
                $this->createReservationItem($insertData);
            } catch (\Exception $ex) {
                throw new \Exception("Create items reservation. " . $ex->getMessage());
            }
        }

        return $reservation;
    }

    /**
     * Create new reservation item
     *
     * @param array $dataInsert
     * @throws \Exception
     */
    public function createReservationItem($dataInsert): void
    {
        try {
            $location = $this->em->find(Location::class, (int) $dataInsert['location']);

            $reservationItem = new ReservationItem();
            $reservationItem->setDate($dataInsert['date']);
            $reservationItem->setReservation($dataInsert['reservation']);
            $reservationItem->setBed($dataInsert['bed']);
            $reservationItem->setLocation($location);
            $reservationItem->setSum($dataInsert['sum']);
            $reservationItem->setTax($dataInsert['tax']);
            $reservationItem->setTotalSum($dataInsert['total_sum']);
            $reservationItem->setNeedToPay($dataInsert['need_to_pay']);
            $reservationItem->setNeedToPayTotal($dataInsert['need_to_pay_total']);
            $reservationItem->setAlreadyPayed(0.0);
            $reservationItem->setAlreadyPayedTotal(0.0);
            $reservationItem->setStatus($dataInsert['status']);
            $reservationItem->setPaymentStatus($dataInsert['payment_status']);
            $reservationItem->setResidentStatus($dataInsert['resident_status']);
            
            $this->em->persist($reservationItem);
            $this->em->flush();
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

}
