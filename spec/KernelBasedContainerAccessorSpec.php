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

namespace spec\FriendsOfBehat\CrossContainerExtension;

use FriendsOfBehat\CrossContainerExtension\ContainerAccessor;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpKernel\KernelInterface;

final class KernelBasedContainerAccessorSpec extends ObjectBehavior
{
    function let(KernelInterface $kernel, ContainerInterface $container): void
    {
        $this->beConstructedWith($kernel);

        $container->has('test.service_container')->willReturn(false);
    }

    function it_is_a_container_accessor(): void
    {
        $this->shouldImplement(ContainerAccessor::class);
    }

    function it_uses_test_container_if_available(KernelInterface $kernel, ContainerInterface $container, ContainerInterface $testContainer)
    {
        $kernel->getContainer()->willReturn($container);
        $container->has('test.service_container')->shouldBeCalled()->willReturn(true);

        $service = new \stdClass();
        $testContainer->get('acme')->willReturn($service);
        $testContainer->has('acme')->willReturn(true);

        $container->get('test.service_container')->shouldBeCalled()->willReturn($testContainer);

        $this->getService('acme')->shouldReturn($service);
    }

    function it_gets_a_service(KernelInterface $kernel, ContainerInterface $container): void
    {
        $service = new \stdClass();

        $kernel->getContainer()->willReturn($container);
        $container->get('acme')->willReturn($service);
        $container->has('acme')->willReturn(true);

        $this->getService('acme')->shouldReturn($service);
    }

    function it_throws_an_exception_if_could_not_get_service(KernelInterface $kernel, ContainerInterface $container): void
    {
        $kernel->getContainer()->willReturn($container);
        $container->has('acme')->willReturn(false);

        $this->shouldThrow(\DomainException::class)->during('getService', ['acme']);
    }

    function it_gets_parameters_from_frozen_container(KernelInterface $kernel, Container $container): void
    {
        $kernel->getContainer()->willReturn($container);

        $container->isCompiled()->willReturn(true);
        $container->getParameterBag()->willReturn(new ParameterBag(['name' => 'value']));

        $this->getParameters()->shouldReturn(['name' => 'value']);
    }

    function it_gets_parameters_from_not_frozen_container(KernelInterface $kernel, Container $container): void
    {
        $kernel->getContainer()->willReturn($container);

        $container->isCompiled()->willReturn(false);
        $container->getParameterBag()->willReturn(new ParameterBag(['name' => 'value']));

        $this->getParameters()->shouldReturn(['name' => 'value']);
    }

    function it_throws_an_exception_if_could_not_get_parameters(KernelInterface $kernel, ContainerInterface $container): void
    {
        $kernel->getContainer()->willReturn($container);

        $this->shouldThrow(\DomainException::class)->during('getParameters');
    }
}
