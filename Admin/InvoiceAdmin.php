<?php

/*
 * This file is part of the VtereshenkovReservationBundle package.
 *
 * (c) Vitaliy Tereshenkov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vtereshenkov\ReservationBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Vtereshenkov\ReservationBundle\Entity\PaymentMethod;
use Vtereshenkov\ReservationBundle\Entity\PaymentStatus;
use Sonata\AdminBundle\Form\Type\ModelHiddenType;
use Vtereshenkov\ReservationBundle\Service\InvoiceManagerInterface;
/**
 * InvoiceAdmin
 *
 * @author Vitaliy Tereshenkov <vitaliytereshenkov@gmail.com>
 */
class InvoiceAdmin extends AbstractAdmin
{

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['list', 'edit']);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->add('number', TextType::class);
        $formMapper->add('sum', NumberType::class);
        $formMapper->add('tax', NumberType::class, [
        ]);
        $formMapper->add('totalSum', NumberType::class, [
            'label' => 'Total Sum'
        ]);
        $formMapper->add('paymentMethod', EntityType::class, [
            'class' => PaymentMethod::class,
            'choice_label' => 'title',
            'multiple' => false,
            'translation_domain' => false,
            'required' => false,
            'label' => 'Payment Method'
        ]);
        $formMapper->add('paymentStatus', EntityType::class, [
            'class' => PaymentStatus::class,
            'choice_label' => 'title',
            'multiple' => false,
            'translation_domain' => false,
            'required' => false,
            'label' => 'Payment Status'
        ]);
        $formMapper->add('order', ModelHiddenType::class);
        $formMapper->add('client', ModelHiddenType::class);
        
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('number');
        $datagridMapper->add('paymentMethod', null, [], EntityType::class, [
            'class' => PaymentMethod::class,
            'choice_label' => 'title',
        ]);
        $datagridMapper->add('paymentStatus', null, [], EntityType::class, [
            'class' => PaymentStatus::class,
            'choice_label' => 'title',
        ]);
        $datagridMapper->add('order', null, [], EntityType::class, [
            'class' => \Vtereshenkov\ReservationBundle\Entity\Order::class,
            'choice_label' => 'number',
        ]);
        $datagridMapper->add('client', null, [], EntityType::class, [
            'class' => \Vtereshenkov\ReservationBundle\Entity\Client::class,
            'choice_label' => 'name',
        ]);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('number');
        $listMapper->add('sum');
        $listMapper->add('tax');
        $listMapper->add('totalSum');
        $listMapper->add('order.number', null, [
            'label' => 'Order'
        ]);
        $listMapper->add('client.name', null, [
            'label' => 'Client'
        ]);
        $listMapper->add('paymentMethod.title', null, [
            'label' => 'Payment Method'
        ]);
        $listMapper->add('paymentStatus.title', null, [
            'label' => 'Payment Status'
        ]);
    }

    public function toString($object)
    {
        return $object instanceof \Vtereshenkov\ReservationBundle\Entity\Invoice ? $object->getNumber() : 'Invoice';
    }
    
    public function postUpdate($object)
    {
        $paymentStatusId = $object->getPaymentStatus()->getId();
         if ($paymentStatusId == InvoiceManagerInterface::PAYMENT_STATUS_PAYED){
             $manager = $this->getConfigurationPool()->getContainer()->get('vtereshenkov_reservation.invoice.manager');
             $manager->invoicePayedProcess($object);
         }      
                 
       
    }

}
