<?php

/*
 * This file is part of the CrossContainerExtension package.
 *
 * (c) Kamil Kokot <kamil@kokot.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\FriendsOfBehat\CrossContainerExtension;

use FriendsOfBehat\CrossContainerExtension\ContainerAccessor;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ContainerBasedContainerAccessorSpec extends ObjectBehavior
{
    function let(ContainerInterface $container)
    {
        $this->beConstructedWith($container);
    }

    function it_is_a_container_accessor()
    {
        $this->shouldImplement(ContainerAccessor::class);
    }

    function it_gets_a_service(ContainerInterface $container)
    {
        $service = new \stdClass();

        $container->get('acme')->willReturn($service);

        $this->getService('acme')->shouldReturn($service);
    }

    function it_gets_a_parameter(ContainerInterface $container)
    {
        $container->getParameter('acme')->willReturn('parameter value');

        $this->getParameter('acme')->shouldReturn('parameter value');
    }
}
