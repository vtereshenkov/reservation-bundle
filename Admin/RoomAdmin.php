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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * RoomAdmin
 *
 * @author Vitaliy Tereshenkov <vitaliytereshenkov@gmail.com>
 */
class RoomAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->add('number', TextType::class);
        $formMapper->add('title', TextType::class);
        
        $formMapper->add('type', EntityType::class, [
            'class' => \Vtereshenkov\ReservationBundle\Entity\RoomType::class,
            'choice_label' => 'title',
            'multiple' => false,
            'translation_domain' => false,
            'required' => true,
            'label' => 'Room Type'
        ]);
        $formMapper->add('location', EntityType::class, [
            'class' => \Vtereshenkov\ReservationBundle\Entity\Location::class,
            'choice_label' => 'title',
            'multiple' => false,
            'translation_domain' => false,
            'required' => true,
            'label' => 'Location'
        ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('number');
        $datagridMapper->add('title');
        $datagridMapper->add('location', null, [], EntityType::class, [
            'class' => \Vtereshenkov\ReservationBundle\Entity\Location::class,
            'choice_label' => 'title',
        ]);
        $datagridMapper->add('type', null, [], EntityType::class, [
            'class' => \Vtereshenkov\ReservationBundle\Entity\RoomType::class,
            'choice_label' => 'title',
        ]);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('number');
        $listMapper->add('title');        
        $listMapper->add('location.title', null, [
            'label' => 'Location'
        ]);
        $listMapper->add('type.title', null, [
            'label' => 'Room type'
        ]);
    }

    public function toString($object)
    {
        return $object instanceof \Vtereshenkov\ReservationBundle\Entity\Room ? $object->getTitle() : 'Room';
    }

}
