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
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpKernel\KernelInterface;

final class KernelBasedContainerAccessorSpec extends ObjectBehavior
{
    function let(KernelInterface $kernel)
    {
        $this->beConstructedWith($kernel);
    }

    function it_is_a_container_accessor()
    {
        $this->shouldImplement(ContainerAccessor::class);
    }

    function it_gets_a_service(KernelInterface $kernel, Container $container)
    {
        $service = new \stdClass();

        $kernel->getContainer()->willReturn($container);
        $container->get('acme')->willReturn($service);

        $this->getService('acme')->shouldReturn($service);
    }

    function it_throws_an_exception_if_could_not_get_service(KernelInterface $kernel, ContainerInterface $container)
    {
        $kernel->getContainer()->willReturn($container);

        $this->shouldThrow(\DomainException::class)->during('getService', ['acme']);
    }

    function it_gets_parameters(KernelInterface $kernel, Container $container)
    {
        $kernel->getContainer()->willReturn($container);
        $container->getParameterBag()->willReturn(new ParameterBag(['name' => 'value']));

        $this->getParameters()->shouldReturn(['name' => 'value']);
    }

    function it_throws_an_exception_if_could_not_get_parameters(KernelInterface $kernel, ContainerInterface $container)
    {
        $kernel->getContainer()->willReturn($container);

        $this->shouldThrow(\DomainException::class)->during('getParameters');
    }
}
