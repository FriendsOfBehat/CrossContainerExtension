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
        return $this->kernel->getContainer()->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($id)
    {
        return $this->kernel->getContainer()->getParameter($id);
    }
}
