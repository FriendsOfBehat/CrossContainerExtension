<?php

/*
 * This file is part of the CrossContainerExtension package.
 *
 * (c) Kamil Kokot <kamil@kokot.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FriendsOfBehat\CrossContainerExtension\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use FriendsOfBehat\CrossContainerExtension\ContainerAccessor;
use FriendsOfBehat\CrossContainerExtension\ContainerBasedContainerAccessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class CrossContainerExtension implements Extension
{
    /**
     * @var ContainerAccessor[]
     */
    private $containers = [];

    /**
     * @param string $containerIdentifier
     * @param ContainerAccessor $containerAccessor
     */
    public function addContainer($containerIdentifier, ContainerAccessor $containerAccessor)
    {
        $this->containers[$containerIdentifier] = $containerAccessor;
    }

    /**
     * @internal
     *
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'fob_cross_container';
    }

    /**
     * @internal
     *
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * @internal
     *
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
    }

    /**
     * @internal
     *
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->containers['behat'] = new ContainerBasedContainerAccessor($container);
    }

    /**
     * @internal
     *
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
