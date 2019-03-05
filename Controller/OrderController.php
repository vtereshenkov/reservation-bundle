<?php

/*
 * This file is part of the VtereshenkovReservationBundle package.
 *
 * (c) Vitaliy Tereshenkov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vtereshenkov\ReservationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Vtereshenkov\ReservationBundle\Entity\Client;
use Vtereshenkov\ReservationBundle\Entity\Order;
use Vtereshenkov\ReservationBundle\Entity\PaymentStatus;
use Vtereshenkov\ReservationBundle\Entity\Status;
use Vtereshenkov\ReservationBundle\Entity\ResidentStatus;
use Vtereshenkov\ReservationBundle\Entity\PaymentMethod;
use Vtereshenkov\ReservationBundle\Entity\Location;
use Vtereshenkov\ReservationBundle\Entity\Invoice;

/**
 * OrderController
 *
 * @author Vitaliy Tereshenkov <vitaliytereshenkov@gmail.com>
 */
class OrderController extends Controller
{

    /**
     * Check dates for order
     * 
     * @return string (JSON)
     */
    public function checkdates(Request $request): JsonResponse
    {
        $model = $this->get('vtereshenkov_reservation.reservation.manager');

        $response = [
            'status' => "success",
            'error' => '',
            'dates_free' => true,
            'list_status' => [],
            'redirect_error' => 'error'
        ];
        if (!empty($request->get('reservation'))) {
            $reservationList = json_decode($request->get('reservation'), true);
        }

        if (!empty($reservationList)) {
            foreach ($reservationList as $reservation) {
                try {
                    if (empty($reservation['reservationId'])) {
                        $isFree = $model->checkDateRangeReservation($reservation['location']['id'], $reservation['bed']['id'], $reservation['checkIn'], $reservation['checkOut'], true);
                    } else {
                        /* Check changes  exist reservation */
                        $startInitRange = new \DateTime($reservation['initDateRange']['start']);
                        $endInitRange = new \DateTime($reservation['initDateRange']['end']);
                        $startFormRange = new \DateTime($reservation['checkIn']);
                        $endFormRange = new \DateTime($reservation['checkOut']);

                        $diffStart = $startInitRange->diff($startFormRange);
                        $diffEnd = $endInitRange->diff($endFormRange);
                        $formatDiffStart = (int) $diffStart->format('%R%a');
                        $formatDiffEnd = (int) $diffEnd->format('%R%a');

                        if ($formatDiffStart == 0 && $formatDiffEnd == 0) {
                            /* Range not modif, check if modif order status */
                            $checkOutTemp = $startInitRange->sub(new \DateInterval('P1D'));
                            $checkIn = $reservation['checkIn'];
                            $checkOut = $reservation['checkOut'];
                            $isFree = $model->checkDateRangeReservation($reservation['location']['id'], $reservation['bed']['id'], $checkIn, $checkOut, true, $reservation['reservationId']);
                        } elseif ($formatDiffStart < 0 && $formatDiffEnd <= 0) {
                            /* Check range for prev form start checkIn */
                            $checkOutTemp = $startInitRange->sub(new \DateInterval('P1D'));
                            $checkIn = $reservation['checkIn'];
                            $checkOut = $checkOutTemp->format('Y-m-d');
                            $isFree = $model->checkDateRangeReservation($reservation['location']['id'], $reservation['bed']['id'], $checkIn, $checkOut, true);
                        } elseif ($formatDiffStart >= 0 && $formatDiffEnd > 0) {
                            /* Check range for next form end checkOut */
                            $checkInTemp = $endInitRange->add(new \DateInterval('P1D'));
                            $checkIn = $checkInTemp->format('Y-m-d');
                            $checkOut = $reservation['checkOut'];
                            $isFree = $model->checkDateRangeReservation($reservation['location']['id'], $reservation['bed']['id'], $checkIn, $checkOut, true);
                        } elseif ($formatDiffStart < 0 && $formatDiffEnd > 0) {
                            /* Check range for next form end checkOut and prev form start checkIn */
                            $checkOutTemp = $startInitRange->sub(new \DateInterval('P1D'));
                            $checkIn = $reservation['checkIn'];
                            $checkOut = $checkOutTemp->format('Y-m-d');
                            $isFreePreStart = $model->checkDateRangeReservation($reservation['location']['id'], $reservation['bed']['id'], $checkIn, $checkOut, true);

                            $checkInTemp = $endInitRange->add(new \DateInterval('P1D'));
                            $checkInE = $checkInTemp->format('Y-m-d');
                            $checkOutE = $reservation['checkOut'];
                            $isFreePreEnd = $model->checkDateRangeReservation($reservation['location']['id'], $reservation['bed']['id'], $checkInE, $checkOutE, true);

                            if (true == $isFreePreStart && true == $isFreePreEnd) {
                                $isFree = true;
                            } else {
                                $isFree = false;
                            }
                        } else {
                            /* If new start date < old start date or new end date > old end date */
                            $isFree = true;
                        }
                    }
                } catch (\Exception $ex) {

                    $response['error'] = $ex->getMessage();
                    $response['status'] = 'error';
                    break;
                }
                $response['list_status'][] = [
                    "reservation_number" => $reservation['reservationNumber'],
                    "free" => $isFree
                ];
                if (false == $isFree) {
                    $response['dates_free'] = false;
                }
            }
        }
        return new JsonResponse($response);
    }

    /**
     * Add new order whith client, reservations, invoices (ajax method)
     */
    public function createAjax(Request $request): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $response = [
            'success' => 1,
            'message' => 'Order success create!'
        ];
        $taxConfig = 0.14;
        $error = "";
        $model = $this->get('vtereshenkov_reservation.reservation.manager');
        $user = $this->getUser();
        $clientId = $request->get('client');

        if (empty($clientId)) {
            $clientName = $request->get('client_name');
            $clientEmail = $request->get('client_email');
            $clientSocialProfile = $request->get('client_social_profile');
            $clientPhone = $request->get('client_phone');
        }
        $orderSumm = $request->get('reservation_total');
        $numberOfPeople = $request->get('order_number_of_people');
        $orderPaymentStatus = $request->get('order_payment_status');
        $orderStatus = $request->get('order_status');
        $orderResidentStatus = $request->get('order_resident_status');
        $orderTax = $request->get('order_tax');
        $orderTotal = $request->get('order_total_sum');
        $invoiceSumm = $request->get('order_invoice_sum');
        $reservationList = $request->get('reservation_list');
        $paymentMethod = $request->get('order_payment_method');
        $orderCreateInvoice = $request->get('order_create_invoice');

        $deposit = $request->get('order_deposit');
        $flagDeposit = $request->get('order_deposit_flag');

        /* Create client(optionality), order and reservation */
        if (!empty(($clientId || $clientName)) && !empty($numberOfPeople) && !empty($orderPaymentStatus) && !empty($reservationList)) {
            $reservationList = json_decode($reservationList, true);

            if (empty($clientId)) {
                //create people
                try {
                    $clientC = new Client();
                    $clientC->setName($clientName);
                    $clientC->setEmail($clientEmail);
                    $clientC->setProfile($clientSocialProfile);
                    $clientC->setPhone($clientPhone);

                    $em->persist($clientC);
                    $em->flush();

                    $clientId = $clientC->getId();
                } catch (\Exception $ex) {
                    $error = "Could not create client.";
                }
            } else {
                $clientC = $em->getRepository(Client::class)->find($clientId);
            }

            /* Create order */
            $locationsForOrder = $this->getOrderLocatons($reservationList);
            if ($error == "") {
                $paymentStatusF = $em->getRepository(PaymentStatus::class)->find($orderPaymentStatus);
                $statusF = $em->getRepository(Status::class)->find($orderStatus);
                $statusResidentF = $em->getRepository(ResidentStatus::class)->find($orderResidentStatus);
                $locationsF = $em->getRepository(Location::class)->findBy(['id' => $locationsForOrder]);
                $paymentMethodF = $em->getRepository(PaymentMethod::class)->find($paymentMethod);

                try {
                    $orderC = new Order();
                    $orderC->setDate(new \DateTime('now'));
                    $orderC->setSum($orderSumm);
                    $orderC->setPaymentStatus($paymentStatusF);
                    $orderC->setStatus($statusF);
                    $orderC->setResidentStatus($statusResidentF);
                    $orderC->setNumberOfPeople($numberOfPeople);
                    foreach ($locationsF as $_location) {
                        $orderC->addLocation($_location);
                    }
                    $orderC->setClient($clientC);
                    $orderC->setCreatedManager($user);
                    $orderC->setTax(!empty($orderTax) ? (float) $orderTax : 0.00);
                    $orderC->setTotalSum($orderTotal);
                    $orderC->setPaymentMethod($paymentMethodF);
                    $orderC->setDeposit(!empty($deposit) ? (float) $deposit : 0.00);
                    $orderC->setDepositFlag(!empty($flagDeposit) ? 1 : 0);
                    $orderC->setNeedToPay($orderSumm);
                    $orderC->setNeedToPayTotal($orderTotal);                    

                    $em->persist($orderC);
                    $em->flush();

                    $orderId = $orderC->getId();
                    /* Update order number */
                    $orderC->setNumber("n" . $orderId);
                    $em->persist($orderC);
                    $em->flush();
                } catch (\Exception $ex) {
                    $error = "Could not create order. " . $ex->getMessage();
                }
            }
            /* Сreate reservation */
            if ($error == "") {
                if (!empty($reservationList)) {
                    $orderTax = (!empty($orderTax) ? (float) $orderTax : 0.00);
                    $reservationItemSum = $this->getReservationItemSumFromOrderSum($reservationList, (float) $orderSumm);
                    $reservationItemTax = (!empty($orderTax) ? $reservationItemSum * $taxConfig : 0.00);
                    $reservationItemTotal = $reservationItemSum + $reservationItemTax;
                    foreach ($reservationList as $reservation) {
                        try {
                            $model->createRoomReservation([
                                'check-in' => $reservation['checkIn'],
                                'check-out' => $reservation['checkOut'],
                                'bed' => $reservation['bed']['id'],
                                'order' => $orderC,
                                'location' => $reservation['location']['id'],
                                'status' => $statusF,
                                'status_payment' => $paymentStatusF,
                                'status_resident' => $statusResidentF,
                                'sum' => $reservationItemSum,
                                'tax' => $reservationItemTax,
                                'total_sum' => $reservationItemTotal,
                                'need_to_pay' => $reservationItemSum,
                                'need_to_pay_total' => $reservationItemTotal
                            ]);
                        } catch (\Exception $ex) {
                            $error = "Could not create reservation.";
                        }
                    }
                }
            }
            /* Create invoice */
            $dataInvoice = [];
            switch ($orderCreateInvoice) {
                case 'whole_sum':
                    $dataInvoice = [
                        'sum' => (float) $orderSumm,
                        'tax' => (!empty($orderTax) ? (float) $orderTax : 0.00),
                        'total_sum' => (float) $orderTotal
                    ];
                    break;
                case '10_prepayment':
                    $dataInvoice = [
                        'sum' => round(((float) $orderSumm) * 0.1, 2),
                        'tax' => (!empty($orderTax) ? round(((float) $orderTax) * 0.1, 2) : 0.00),
                        'total_sum' => round(((float) $orderTotal) * 0.1, 2)
                    ];
                    break;
                case 'any_sum':
                    $sum = round(((float) $invoiceSumm), 2);
                    $tax = $sum * $taxConfig;
                    $total = $sum + $tax;
                    $dataInvoice = [
                        'sum' => $sum,
                        'tax' => $tax,
                        'total_sum' => $total
                    ];
                    break;
            }
            if (!empty($dataInvoice)) {
                if ($error == "") {
                    try {
                        $invoiceC = new Invoice();
                        $invoiceC->setDate(new \DateTime('now'));
                        $invoiceC->setClient($clientC);
                        $invoiceC->setOrder($orderC);
                        $invoiceC->setPaymentMethod($paymentMethodF);
                        $invoiceC->setPaymentStatus($paymentStatusF);
                        $invoiceC->setSum($dataInvoice['sum']);
                        $invoiceC->setTax($dataInvoice['tax']);
                        $invoiceC->setTotalSum($dataInvoice['total_sum']);

                        $em->persist($invoiceC);
                        $em->flush();

                        /* Update invoice number */
                        $invoiceC->setNumber('n' . $invoiceC->getId());
                        $em->persist($invoiceC);
                        $em->flush();
                    } catch (\Exception $ex) {
                        $error = "Could not create invoice.";
                    }
                }
            }
            /* Create deposit invoice (Deposit) */
            if (!empty($deposit) && $error == "") {               
                try {
                    if(!empty($flagDeposit)){
                        $depositPaymentStatus = $em->getRepository(PaymentStatus::class)->find(1);
                    }else{
                        $depositPaymentStatus = $em->getRepository(PaymentStatus::class)->find(2);
                    }
                    
                    $depositInvoice = new Invoice();
                    $depositInvoice->setDate(new \DateTime('now'));
                    $depositInvoice->setClient($clientC);
                    $depositInvoice->setOrder($orderC);
                    $depositInvoice->setPaymentMethod($paymentMethodF);
                    $depositInvoice->setPaymentStatus($depositPaymentStatus);
                    $depositInvoice->setSum(round((float) $deposit, 2));
                    $depositInvoice->setTax(0.00);
                    $depositInvoice->setTotalSum(round((float) $deposit, 2));

                    $em->persist($depositInvoice);
                    $em->flush();
                    
                    $depositInvoice->setNumber('n' . $depositInvoice->getId());
                    $em->persist($depositInvoice);
                    $em->flush();
                } catch (\Exception $ex) {                   
                    $error = "Could not create deposit invoice.";
                }
            }
            if ($error != "") {
                $response = [
                    'success' => 0,
                    'message' => $error
                ];
            }
        } else {
            $response = [
                'success' => 0,
                'message' => "Are not filled requirement field!"
            ];
        }

        return new JsonResponse($response);
    }

    /**
     * Get location id from reservation list
     * 
     * @param array $reservationList
     * @return array
     */
    private function getOrderLocatons($reservationList): array
    {
        $result = [];
        if (!empty($reservationList)) {
            foreach ($reservationList as $reservation) {
                $result[] = (int) $reservation['location']['id'];
            }
        }

        return $result;
    }

    /**
     * Get sum for reservation item from order sum
     * 
     * @param  array $reservations
     * @param  float $orderSum
     * @return int
     */
    private function getReservationItemSumFromOrderSum(array $reservations, float $orderSum): int
    {
        $result = 0.0;
        $countReservaionItems = 0;
        if (!empty($reservations)) {
            foreach ($reservations as $value) {
                $reservationStart = new \DateTime($value['checkIn']);
                $reservationEnd = new \DateTime($value['checkOut']);
                $diffDays = $reservationStart->diff($reservationEnd);
                $formatDiff = (int) $diffDays->format('%R%a');
                if ($formatDiff > 0) {
                    $countReservaionItems += $formatDiff;
                } else {
                    $countReservaionItems++;
                }
            }
            if ($countReservaionItems > 0) {
                $result = $orderSum / $countReservaionItems;
            }
        }

        return $result;
    }

}
