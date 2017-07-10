<?php

declare(strict_types=1);

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
use FriendsOfBehat\CrossContainerExtension\CrossContainerProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @api
 */
final class CrossContainerExtension implements Extension
{
    /**
     * @var CrossContainerProcessor
     */
    private $crossContainerProcessor;

    public function __construct()
    {
        $this->crossContainerProcessor = new CrossContainerProcessor();
    }

    /**
     * @api
     *
     * @return CrossContainerProcessor
     */
    public function getCrossContainerProcessor(): CrossContainerProcessor
    {
        return $this->crossContainerProcessor;
    }

    /**
     * @internal
     *
     * {@inheritdoc}
     */
    public function getConfigKey(): string
    {
        return 'fob_cross_container';
    }

    /**
     * @internal
     *
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    /**
     * @internal
     *
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    /**
     * @internal
     *
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config): void
    {
        $this->crossContainerProcessor->addContainerAccessor('behat', new ContainerBasedContainerAccessor($container));
    }

    /**
     * @internal
     *
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
    }
}
