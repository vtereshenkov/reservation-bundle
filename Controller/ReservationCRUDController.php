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

use Sonata\AdminBundle\Controller\CRUDController;
use Vtereshenkov\ReservationBundle\Model\ReservationManager;

class ReservationCRUDController extends CRUDController
{

    public function listAction()
    {
        $model = $this->get('vtereshenkov_reservation.reservation.manager');
        $taxKoef = 0.14;
        $locations = $model->getRoomLocation("LA-1");
        $clientList = $model->getClientsList();
        $orderStatusList = $model->getOrderStatus(true);
        $orderPaymentMethod = $model->getPaymentMethod();
        $clientList = array_merge([["id" => "", "name" => "New client", "email" => "", "profile" => "", "phone" => ""]], $clientList);
        $locations = array_merge([['id' => 'all', 'title' => 'All', 'selected' => false]], $locations);

        return $this->renderWithExtraParams('@VtereshenkovReservation/admin/reservation.html.twig', [
                    'locations' => $locations,
                    'clients' => $clientList,
                    'orderStatusList' => $orderStatusList,
                    'orderPaymentMethodList' => $orderPaymentMethod,
                    'tax' => $taxKoef
        ]);
    }

}
