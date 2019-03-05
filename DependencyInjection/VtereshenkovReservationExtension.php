<?php

/*
 * This file is part of the VtereshenkovReservationBundle package.
 *
 * (c) Vitaliy Tereshenkov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vtereshenkov\ReservationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;


class VtereshenkovReservationExtension extends Extension implements PrependExtensionInterface
{
    
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('sonata_admin.xml');
    }
    
    public function getAlias()
    {
        return 'vtereshenkov_reservation';
    }
    
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);
        
        
        $doctrineConfig = [];
        $doctrineConfig['orm']['resolve_target_entities']['Vtereshenkov\ReservationBundle\Entity\UserInterface'] = $config['user_provider'];
        
        $doctrineConfig['orm']['mappings'][] = array(
            'name' => 'VtereshenkovReservationBundle',
            'is_bundle' => true,
            'type' => 'xml',
            'prefix' => 'Vtereshenkov\ReservationBundle\Entity'
        );
        $container->prependExtensionConfig('doctrine', $doctrineConfig);
                
    }

}
