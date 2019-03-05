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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;

/**
 * ClientAdmin
 *
 * @author Vitaliy Tereshenkov <vitaliytereshenkov@gmail.com>
 */
class ClientAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->add('name', TextType::class);
        $formMapper->add('email', EmailType::class);
        $formMapper->add('phone', TelType::class, [
            'required' => false
        ]);
        $formMapper->add('profile', TextType::class, [
            'label' => 'Social profile',
            'required' => false
        ]);
        
        
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('name');
        $datagridMapper->add('email');
        $datagridMapper->add('phone');
        $datagridMapper->add('profile');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('name');
        $listMapper->add('email');   
        $listMapper->add('profile');   
        
    }

    public function toString($object)
    {
        return $object instanceof \Vtereshenkov\ReservationBundle\Entity\Client ? $object->getName() : 'Client';
    }

}
