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
use Symfony\Component\HttpKernel\KernelInterface;

final class KernelBasedContainerAccessor implements ContainerAccessor
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function getService($id)
    {
        $container = $this->kernel->getContainer();

        if (!$container instanceof Container) {
            throw new \DomainException('Could not get the parameters of kernel\'s container.');
        }

        return (new ContainerBasedContainerAccessor($container))->getService($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        $container = $this->kernel->getContainer();

        if (!$container instanceof Container) {
            throw new \DomainException('Could not get the parameters of kernel\'s container.');
        }

        return (new ContainerBasedContainerAccessor($container))->getParameters();
    }
}
