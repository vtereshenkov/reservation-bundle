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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Vtereshenkov\ReservationBundle\Entity\City;

/**
 * LocationAdmin
 *
 * @author Vitaliy Tereshenkov <vitaliytereshenkov@gmail.com>
 */
class LocationAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper->add('name', TextType::class);
        $formMapper->add('title', TextType::class);
        $formMapper->add('address', TextType::class);
        $formMapper->add('sortNumber', NumberType::class);
        $formMapper->add('description', TextareaType::class, ['required' => false]);
        $formMapper->add('slug', TextType::class, ['required' => false]);   
        $formMapper->add('city', EntityType::class, [ 
            'class' => City::class,
            'choice_label' => 'name',
            'multiple' => false,
            'translation_domain' => false,
            'required' => false,
            'label' => 'City'
        ]);
    }
    
    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper->add('title');
        $datagridMapper->add('name');
        $datagridMapper->add('slug');
        $datagridMapper->add('status');
    }
    
    protected function configureListFields(ListMapper $listMapper) {
        $listMapper->addIdentifier('title');
        $listMapper->add('name');
        $listMapper->add('status', null, ['editable' => true]);
    }

    public function toString($object) {
        return $object instanceof \Vtereshenkov\ReservationBundle\Entity\Location ? $object->getTitle() : 'Location';
    }
}
