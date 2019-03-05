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

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * ReservationController
 *
 * @author Vitaliy Tereshenkov <vitaliytereshenkov@gmail.com>
 */
class ReservationController extends Controller
{

    /**
     * Get calendar data for range room calendar
     * 
     * @return JsonResponse
     */
    public function getrangedata(Request $request): JsonResponse
    {
        
        $model = $this->get('vtereshenkov_reservation.reservation.manager');
       
        $rangeStart = $request->get('rangeStart');
        $rangeEnd = $request->get('rangeEnd');
        $location = $request->get('location');
        $responce = [
            'status' => 'success',
            'error' => '',
            'redirect_error' => 'error',
            'all_location' => false
        ];
        $allReservation = [];

        try {
            if ($location != 'all') {
                $rooms = $model->getRoomListCalendar($location);                  
                /*Get room reservation for date range*/
                $roomsReservation = $model->getRoomReservation($rooms, $rangeStart, $rangeEnd);     
                
            } else {
                $locations = $model->getRoomLocation();
                foreach ($locations as $_location) {
                    $locationId = (int) $_location['id'];
                    $rooms = $model->getRoomListCalendar($locationId);
                    //Get room reservation date range
                    $roomsReservation = $model->getRoomReservation($rooms, $rangeStart, $rangeEnd);
                    $allReservation[] = [
                        'location' => $_location['title'],
                        'rooms_reservation' => $roomsReservation
                    ];
                }
            }
        } catch (\Exception $ex) {            
            $responce = [
                'status' => 'error',
                'error' => $ex->getMessage(),
                'redirect_error' => 'error'
            ];
        }
        if ($responce['status'] != 'error') {
            if (empty($allReservation)) {
                $responce['all_location'] = false;
                $responce['rooms_reservation'] = $roomsReservation;
            } else {
                $responce['all_location'] = true;
                $responce['rooms_reservation'] = $allReservation;
            }
        }
               
        
        return new JsonResponse($responce); 
    }

    /**
     * Get data for calendar location filter
     * 
     * @return string JSON
     */
    public function getlocation(): JsonResponse
    {
        $model = $this->get('vtereshenkov_reservation.reservation.manager');       
        try {
            $location = $model->getRoomLocation();
        } catch (\Exception $ex) { 
            $this->redirect('error');
        }
        
        return new JsonResponse($responce); 
    }

}
