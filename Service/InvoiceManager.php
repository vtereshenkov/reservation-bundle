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

use Vtereshenkov\ReservationBundle\Model\AbstractInvoice as Invoice;
use Vtereshenkov\ReservationBundle\Model\AbstractOrder as Order;
use Vtereshenkov\ReservationBundle\Entity\Reservation;
use Vtereshenkov\ReservationBundle\Entity\ReservationItem;
use Vtereshenkov\ReservationBundle\Entity\PaymentStatus;

/**
 * InvoiceManager
 *
 * @author Vitaliy Tereshenkov <vitaliytereshenkov@gmail.com>
 */
class InvoiceManager implements InvoiceManagerInterface
{

    private $em;

    /**
     *  Helper for format data
     *
     * @var \Vtereshenkov\ReservationBundle\Service\Format
     */
    private $fh;

    /**
     *
     * @var \Vtereshenkov\ReservationBundle\Service\ReservationManager 
     */
    private $rm;

    public function __construct(\Doctrine\ORM\EntityManager $em, \Vtereshenkov\ReservationBundle\Service\Format $fh, \Vtereshenkov\ReservationBundle\Service\ReservationManager $rm)
    {
        $this->em = $em;
        $this->fh = $fh;
        $this->rm = $rm;
    }

    public function invoicePayedProcess(Invoice $invoice): void
    {
        $order = $invoice->getOrder();
        
       
        $invoiceSum = [
            'sum' => $invoice->getSum(),
            'total_sum' => $invoice->getTotalSum(),
        ];
        $orderSum = [
            'need_to_pay' => $order->getNeedToPay(),
            'already_payed' => $order->getAlreadyPayed(),
            'need_to_pay_total' => $order->getNeedToPayTotal(),
            'already_payed_total' => $order->getAlreadyPayedTotal()
        ];
        $resultOrderSum = [
            'need-to-pay' => $orderSum['need_to_pay'] - $invoiceSum['sum'],
            'already-payed' => $orderSum['already_payed'] + $invoiceSum['sum'],
            'need-to-pay-total' => $orderSum['need_to_pay_total'] - $invoiceSum['total_sum'],
            'already-payed-total' => $orderSum['already_payed_total'] + $invoiceSum['total_sum']
        ];

        /* Update order sum, status field data */
        $order->setNeedToPay($resultOrderSum['need-to-pay']);
        $order->setAlreadyPayed($resultOrderSum['already-payed']);
        $order->setNeedToPayTotal($resultOrderSum['need-to-pay-total']);
        $order->setAlreadyPayedTotal($resultOrderSum['already-payed-total']);

        if ($resultOrderSum['need-to-pay'] <= 0) {
            $paymentStatus = $this->em->getRepository(PaymentStatus::class)->find(self::PAYMENT_STATUS_PAYED);
            $order->setPaymentStatus($paymentStatus);
            
        } else {
            $paymentStatus = $this->em->getRepository(PaymentStatus::class)->find(self::PAYMENT_STATUS_PARTIALLY_PAYED);
            $order->setPaymentStatus($paymentStatus);
        }

        $this->em->flush();


        /* Update reservation items data */
        $this->updateReservationItemsSumStatusField($order, $invoiceSum);
    }

    /**
     * Payed reservation items from invoice sum
     *
     * @param  Order $order
     * @param  array $invoiceSum
     * @return void
     */
    public function updateReservationItemsSumStatusField(Order $order, array $invoiceSum): void
    {
        /* Get order reservations */
        $reservations = $this->em->getRepository(Reservation::class)
                ->findBy(['order' => $order->getId()]);              

        $reservations = $this->fh->formatReservationListData($reservations);
        /* Get reservation items */
        if (!empty($reservations)) {
            $reservationIds = [];
            $reindexReservation = [];
            foreach ($reservations as $reservation) {
                $reservationIds[] = (int) $reservation['id'];
                $reindexReservation[$reservation['id']] = $reservation;
            }
            $reservationsItems = $this->rm->getReservationItems(null, null, [], $reservationIds);

            /* Add reservation items to reservation (ONLY not payed or part payed) */
            foreach ($reservationsItems as $value) {
                $reservationPaymentStatus = $value['payment_status']['id'];
                $reservationId = $value['reservation'];
                if ($reservationPaymentStatus == self::PAYMENT_STATUS_NOT_PAYED || $reservationPaymentStatus == self::PAYMENT_STATUS_PARTIALLY_PAYED) {
                    if (!empty($reindexReservation[$reservationId])) {
                        $reindexReservation[$reservationId]['reservation_item'][] = $value;
                    }
                }
            }
            $reservations = array_values($reindexReservation);
            /* Sorting reservation items in reservation */
            foreach ($reservations as $key => $reservation) {
                if (!empty($reservations[$key]['reservation_item'])) {
                    usort($reservations[$key]['reservation_item'], function($a, $b) {
                        $temp1 = $a['date']->getTimestamp();
                        $temp2 = $b['date']->getTimestamp();
                        return ($temp1 - $temp2);
                    });
                }
            }
            /* Payed reservation items */
            $indItem = 0;
            $allPayed = false;
            $maxReservationItems = 0;
            foreach ($reservations as $reservation) {
                $countReservationItems = (!empty($reservation['reservation_item']) ? count($reservation['reservation_item']) : 0);
                if ($countReservationItems > $maxReservationItems) {
                    $maxReservationItems = $countReservationItems;
                }
            }
            if ($maxReservationItems == 0) {
                $allPayed = true;
            }
            
            $paymentStatusPayed = $this->em->getRepository(PaymentStatus::class)->find(self::PAYMENT_STATUS_PAYED);
            $paymentStatusPartPayed = $this->em->getRepository(PaymentStatus::class)->find(self::PAYMENT_STATUS_PARTIALLY_PAYED);
            
            while ($invoiceSum['sum'] > 0 && $allPayed == false) {
                foreach ($reservations as $reservation) {
                    if ($invoiceSum['sum'] > 0) {
                        if (!empty($reservation['reservation_item'][$indItem])) {
                            $itemReservationForUpdate = $reservation['reservation_item'][$indItem];
                            /* Check sum for item and update data */
                            $itemSum = [
                                'need_to_pay' => (float) $itemReservationForUpdate['need_to_pay']['value'],
                                'already_payed' => (float) $itemReservationForUpdate['already_payed']['value'],
                                'need_to_pay_total' => (float) $itemReservationForUpdate['need_to_pay_total']['value'],
                                'already_payed_total' => (float) $itemReservationForUpdate['already_payed_total']['value'],
                            ];
                            if ($invoiceSum['sum'] >= $itemSum['need_to_pay']) {
                                $itemSumResult = [
                                    'need-to-pay' => [
                                        'value' => 0.0,
                                        'currency' => 'USD'
                                    ],
                                    'already-payed' => [
                                        'value' => $itemSum['need_to_pay'],
                                        'currency' => 'USD'
                                    ],
                                    'need-to-pay-total' => [
                                        'value' => 0.0,
                                        'currency' => 'USD'
                                    ],
                                    'already-payed-total' => [
                                        'value' => $itemSum['need_to_pay_total'],
                                        'currency' => 'USD'
                                    ],
                                    'payment-status' => $paymentStatusPayed
                                ];
                                $invoiceSum['sum'] -= $itemSum['need_to_pay'];
                                $invoiceSum['total_sum'] -= $itemSum['need_to_pay_total'];
                            } else if ($invoiceSum['sum'] > 0) {
                                $itemSumResult = [
                                    'need-to-pay' => [
                                        'value' => $itemSum['need_to_pay'] - $invoiceSum['sum'],
                                        'currency' => 'USD'
                                    ],
                                    'already-payed' => [
                                        'value' => $itemSum['already_payed'] + $invoiceSum['sum'],
                                        'currency' => 'USD'
                                    ],
                                    'need-to-pay-total' => [
                                        'value' => $itemSum['need_to_pay_total'] - $invoiceSum['total_sum'],
                                        'currency' => 'USD'
                                    ],
                                    'already-payed-total' => [
                                        'value' => $itemSum['already_payed_total'] + $invoiceSum['total_sum'],
                                        'currency' => 'USD'
                                    ],
                                    'payment-status' => $paymentStatusPartPayed
                                ];
                                $invoiceSum['sum'] = 0;
                                $invoiceSum['total_sum'] = 0;
                            }
                            /* Update reservation item sum fields */
                            if (!empty($itemSumResult)) {
                                $reservationItem = $this->em->find(ReservationItem::class, $itemReservationForUpdate['id']);
                                $reservationItem->setNeedToPay($itemSumResult['need-to-pay']['value']);
                                $reservationItem->setAlreadyPayed($itemSumResult['already-payed']['value']);
                                $reservationItem->setNeedToPayTotal($itemSumResult['need-to-pay-total']['value']);
                                $reservationItem->setAlreadyPayedTotal($itemSumResult['already-payed-total']['value']);
                                $reservationItem->setPaymentStatus($itemSumResult['payment-status']);

                                $this->em->flush();
                            }
                        }
                    } else {
                        break;
                    }
                }
                $indItem++;
                if ($indItem >= $maxReservationItems) {
                    $allPayed = true;
                }
            }
        }
    }

}
