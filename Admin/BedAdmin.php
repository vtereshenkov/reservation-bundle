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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * BedAdmin
 *
 * @author Vitaliy Tereshenkov <vitaliytereshenkov@gmail.com>
 */
class BedAdmin extends AbstractAdmin
{
     protected function configureFormFields(FormMapper $formMapper) {
        $formMapper->add('number', TextType::class);
        $formMapper->add('priceDay', NumberType::class);
        $formMapper->add('priceMonth', NumberType::class);        
        
        $formMapper->add('type', EntityType::class, [ 
            'class' => \Vtereshenkov\ReservationBundle\Entity\BedType::class,
            'choice_label' => 'title',
            'multiple' => false,
            'translation_domain' => false,
            'required' => false,
            'label' => 'Bed Type'
        ]);
        $formMapper->add('room', EntityType::class, [ 
            'class' => \Vtereshenkov\ReservationBundle\Entity\Room::class,
            'choice_label' => 'title',
            'multiple' => false,
            'translation_domain' => false,
            'required' => true,
            'label' => 'Room'
        ]);
    }
    
    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper->add('number');
        $datagridMapper->add('priceDay');
        $datagridMapper->add('room', null, [], EntityType::class, [
            'class' => \Vtereshenkov\ReservationBundle\Entity\Room::class,
            'choice_label' => 'title',
        ]);
        $datagridMapper->add('priceMonth');
    }
    
    protected function configureListFields(ListMapper $listMapper) {
        $listMapper->addIdentifier('number');
        $listMapper->add('priceDay');
        $listMapper->add('priceMonth');
        $listMapper->add('room.title', null, [
            'label' => 'Room'
        ]);
    }

    public function toString($object) {
        return $object instanceof \Vtereshenkov\ReservationBundle\Entity\Bed ? $object->getNumber() : 'Bed';
    }
}
