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

/**
 *  CityAdmin
 *
 * @author Vitaliy Tereshenkov <vitaliytereshenkov@gmail.com>
 */
class CityAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper->add('name', TextType::class);
        $formMapper->add('shortName', TextType::class);        
    }
    
    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {       
        $datagridMapper->add('name');
        $datagridMapper->add('shortName');      
    }
    
    protected function configureListFields(ListMapper $listMapper) {       
        $listMapper->addIdentifier('name');
        $listMapper->addIdentifier('shortName');
    }

    public function toString($object) {
        return $object instanceof \Vtereshenkov\ReservationBundle\Entity\City ? $object->getName() : 'City';
    }
}
