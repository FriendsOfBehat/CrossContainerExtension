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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class ContainerBasedContainerAccessorSpec extends ObjectBehavior
{
    function let(Container $container): void
    {
        $this->beConstructedWith($container);
    }

    function it_is_a_container_accessor(): void
    {
        $this->shouldImplement(ContainerAccessor::class);
    }

    function it_gets_a_service(Container $container): void
    {
        $service = new \stdClass();

        $container->get('acme')->willReturn($service);

        $this->getService('acme')->shouldReturn($service);
    }

    function it_gets_parameters_from_frozen_container(Container $container): void
    {
        $container->isCompiled()->willReturn(true);
        $container->getParameterBag()->willReturn(new ParameterBag(['name' => 'value']));

        $this->getParameters()->shouldReturn(['name' => 'value']);
    }

    function it_gets_parameters_from_not_frozen_container(Container $container): void
    {
        $container->isCompiled()->willReturn(false);
        $container->getParameterBag()->willReturn(new ParameterBag(['name' => 'value']));

        $this->getParameters()->shouldReturn(['name' => 'value']);
    }
}
