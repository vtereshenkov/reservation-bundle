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

/**
 * InvoiceManager
 *
 * @author Vitaliy Tereshenkov <vitaliytereshenkov@gmail.com>
 */
interface InvoiceManagerInterface
{
    const PAYMENT_STATUS_PAYED = 1;
    const PAYMENT_STATUS_NOT_PAYED = 2;
    const PAYMENT_STATUS_PARTIALLY_PAYED = 3;
    
    /**
     * Еhe implementation of the payment order invoice (modification of the order data,
     * reservation items)
     * 
     * @param Invoice $invoice
     * @return void
     */
    public function invoicePayedProcess(Invoice $invoice): void;
    
}
