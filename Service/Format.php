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

/**
 * Format data
 *
 * @author Vitaliy Tereshenkov <vitaliytereshenkov@gmail.com>
 */
class Format
{

    /**
     * Format location data 
     * 
     * @param array $locations
     * @return array
     */
    public function formatLocationListData(array $locations): array
    {
        $result = [];
        if (!empty($locations)) {
            foreach ($locations as $_location) {
                $result[] = array(
                    'id' => $_location->getId(),
                    'title' => $_location->getTitle(),
                    'address' => $_location->getAddress(),
                    'name' => $_location->getName(),
                    'city' => $_location->getCity()->getName()
                );
            }
        }
        return $result;
    }

    /**
     * Format reservation items
     * 
     * @param array $items (ReservationItems)
     * @return array
     */
    public function formatReservationItem(array $items): array
    {
        $result = [];
        if (!empty($items)) {
            foreach ($items as $_item) {
                $result[] = array(
                    'id' => $_item->getId(),
                    'reservation' => $_item->getReservation()->getId(),
                    'date' => $_item->getDate(),
                    'bed' => [
                        'id' => $_item->getBed()->getId(),
                        'title' => $_item->getBed()->getNumber()
                    ],
                    'location' => [
                        'id' => $_item->getLocation()->getId(),
                        'title' => $_item->getLocation()->getTitle()
                    ],
                    'sum' => [
                        'value' => $_item->getSum(),
                        'currency' => 'USD'
                    ],
                    'tax' => [
                        'value' => $_item->getTax(),
                        'currency' => 'USD'
                    ],
                    'total_sum' => [
                        'value' => $_item->getTotalSum(),
                        'currency' => 'USD'
                    ],
                    'need_to_pay' => [
                        'value' => $_item->getNeedToPay(),
                        'currency' => 'USD'
                    ],
                    'already_payed' => [
                        'value' => $_item->getAlreadyPayed(),
                        'currency' => 'USD'
                    ],
                    'need_to_pay_total' => [
                        'value' => $_item->getNeedToPayTotal(),
                        'currency' => 'USD'
                    ],
                    'already_payed_total' => [
                        'value' => $_item->getAlreadyPayedTotal(),
                        'currency' => 'USD'
                    ],
                    'payment_status' => [
                        'title' => $_item->getPaymentStatus()->getTitle(),
                        'id' => $_item->getPaymentStatus()->getId()
                    ],
                    'status' => [
                        'title' => $_item->getStatus()->getTitle(),
                        'id' => $_item->getStatus()->getId()
                    ],
                    'resident_status' => [
                        'title' => $_item->getResidentStatus()->getTitle(),
                        'id' => $_item->getResidentStatus()->getId()
                    ]
                );
            }
        }
        return $result;
    }

    /**
     * 
     * @param array $data
     * @return array
     */
    public function numberIndexArrayConvert(array $data): array
    {
        $key = 0;
        $result = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                $result[$key] = $value;
                $key++;
            }
        }
        return $result;
    }

    /**
     * Format data reservation list
     * 
     * @param  array $reservations (Reservations)
     * @return array
     */
    public function formatReservationListData(array $reservations): array
    {
        $result = [];
        if (!empty($reservations)) {
            foreach ($reservations as $_reservation) {
                $result[] = [
                    'id' => $_reservation->getId(),
                    'check_in' => $_reservation->getCheckIn(),
                    'check_out' => $_reservation->getCheckOut(),
                    'bed' => [
                        'title' => $_reservation->getBed()->getNumber(),
                        'id' => $_reservation->getBed()->getId()
                    ],
                    'order' => [
                        'id' => $_reservation->getOrder()->getId()
                    ]
                ];
            }
        }
        return $result;
    }

    /**
     * Format order data for table
     *
     * @param  array $items(Order)
     * @return array
     */
    public function formatOrderDataForTable(array $items): array
    {
        $result = [];

        if (!empty($items)) {
            $i = 0;
            foreach ($items as $_item) {
                $result[$i] = [
                    'id' => $_item->getId(),
                    'number' => $_item->getNumber(),
                    'date' => $_item->getDate(),
                    'number_of_people' => $_item->getNumberOfPeople(),
                    'sum' => [
                        'value' => $_item->getSum(),
                        'currency' => 'USD'
                    ],
                    'tax' => [
                        'value' => $_item->getTax(),
                        'currency' => 'USD'
                    ],
                    'total_sum' => [
                        'value' => $_item->getTotalSum(),
                        'currency' => 'USD'
                    ],
                    'need_to_pay' => [
                        'value' => $_item->getNeedToPay(),
                        'currency' => 'USD'
                    ],
                    'already_payed' => [
                        'value' => $_item->getAlreadyPayed(),
                        'currency' => 'USD'
                    ],
                    'need_to_pay_total' => [
                        'value' => $_item->getNeedToPayTotal(),
                        'currency' => 'USD'
                    ],
                    'already_payed_total' => [
                        'value' => $_item->getAlreadyPayedTotal(),
                        'currency' => 'USD'
                    ],
                    'client' => [
                        'title' => $_item->getClient()->getName(),
                        'id' => $_item->getClient()->getId()
                    ],
                    'payment_method' => [
                        'title' => $_item->getPaymentMethod()->getTitle(),
                        'id' => $_item->getPaymentMethod()->getId()
                    ],
                    'payment_status' => [
                        'text' => $_item->getPaymentStatus()->getTitle(),
                        'id' => $_item->getPaymentStatus()->getId()
                    ],
                    'status' => [
                        'text' => $_item->getStatus()->getTitle(),
                        'id' => $_item->getStatus()->getId()
                    ],
                    'resident_status' => [
                        'text' => $_item->getResidentStatus()->getTitle(),
                        'id' => $_item->getResidentStatus()->getId()
                    ],
                    'deposit' => (!empty($_item->getDeposit()) ? $_item->getDeposit() : 0.00),
                    'deposit_flag' => (!empty($_item->getDepositFlag()) ? 'Yes' : 'No')
                ];
                //Status color
                if (!empty($result[$i]['payment_status'])) {
                    switch ($result[$i]['payment_status']['text']) {
                        case 'Not Payed':
                            $result[$i]['payment_status']['color'] = "";
                            break;
                        case 'Payed':
                            $result[$i]['payment_status']['color'] = "success";
                            break;
                        case 'Partially payed':
                            $result[$i]['payment_status']['color'] = "warning";
                            break;
                    }
                } else {
                    $result[$i]['payment_status']['color'] = "";
                }
                $i++;
            }
        }

        return $result;
    }

    /**
     * Select default value for status list (Order, ReservationItem)
     * @param array $items
     * @param string $itemValue
     * @return array
     */
    public function setSelectValue(array $items, string $itemValue): array
    {
        if (!empty($items)) {
            foreach ($items as $key => $value) {
                if ($value->getTitle() == $itemValue) {
                    $items[$key]->selected = true;
                    continue;
                }
                $items[$key]->selected = false;
            }
        }
        return $items;
    }

    /**
     * Create date range
     * 
     * @param DateTime $start
     * @param DateTime $end
     * @return array
     */
    public function createRangeDates(\DateTime $start, \DateTime $end): array
    {
        $result = [];
        $startU = $start->format("U");
        $endU = $end->format("U");

        if ($start >= $end) {
            $result[0] = $start->format("Y-m-d");
        } else {
            $interval = new \DateInterval('P1D');
            $period = new \DatePeriod($start, $interval, $end);
            foreach ($period as $date) {
                $result[] = $date->format('Y-m-d');
            }
            //last day We do not consider
            //$result[] = $end->format('Y-m-d');
        }

        return $result;
    }

    /**
     * Format client data
     * 
     * @param array $clients
     * @return array
     */
    public function formatClientList(array $clients): array
    {
        $result = [];
        if (!empty($clients)) {
            foreach ($clients as $_client) {
                $result[] = [
                    'id' => $_client->getId(),
                    'title' => $_client->getName(),
                    'email' => $_client->getEmail(),
                    'phone' => $_client->getPhone(),
                    'social_link' => $_client->getProfile()
                ];
            }
        }
        return $result;
    }

}
