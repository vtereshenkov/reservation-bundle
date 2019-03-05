<?php

/*
 * This file is part of the VtereshenkovSonataOperationBundle package.
 *
 * (c) Vitaliy Tereshenkov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vtereshenkov\ReservationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder $builder The tree builder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('vtereshenkov_reservation');
        $rootNode->children()
                ->scalarNode('user_provider')
//                ->isRequired()
                ->defaultValue('\App\Application\Sonata\UserBundle\Entity\User')
                ->info('User Group Entity (for example FOS\UserBundle\Model\User or Sonata\UserBundle\Entity\BaseUser)')
                ->end()                
                ->end();

        return $builder;
    }

}
