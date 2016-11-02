<?php

/*
 * This file is part of the CrossContainerExtension package.
 *
 * (c) Kamil Kokot <kamil@kokot.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FriendsOfBehat\CrossContainerExtension;

use Symfony\Component\DependencyInjection\Container;

final class ContainerBasedContainerAccessor implements ContainerAccessor
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getService($id)
    {
        return $this->container->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->container->getParameterBag()->all();
    }
}
