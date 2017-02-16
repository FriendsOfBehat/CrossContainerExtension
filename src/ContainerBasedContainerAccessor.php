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
        $parameterBag = $this->container->getParameterBag();
        if (!$this->container->isFrozen()) {
            $parameterBag = clone $parameterBag;
            $parameterBag->resolve();

            // Values are automatically unescaped during resolving, need to revert it
            $parameterBag->add(array_map(function ($unescapedValue) use ($parameterBag) {
                return $parameterBag->escapeValue($unescapedValue);
            }, $parameterBag->all()));
        }

        return $parameterBag->all();
    }
}
