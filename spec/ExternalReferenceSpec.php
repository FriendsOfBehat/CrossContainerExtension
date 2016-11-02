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

use PhpSpec\ObjectBehavior;

final class ExternalReferenceSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('__container_identifier__.service_identifier');
    }

    function it_returns_whether_it_is_valid()
    {
        static::isValid('not_valid')->shouldReturn(false);
        static::isValid('__not.valid')->shouldReturn(false);
        static::isValid('_not_.valid')->shouldReturn(false);
        static::isValid('__not.valid__')->shouldReturn(false);
        static::isValid('__not_valid__')->shouldReturn(false);

        static::isValid('__valid__.indeed')->shouldReturn(true);
        static::isValid('__still__valid__.indeed')->shouldReturn(true);
    }

    function it_returns_container_and_service_identifiers()
    {
        $this->containerIdentifier()->shouldReturn('container_identifier');
        $this->serviceIdentifier()->shouldReturn('service_identifier');
    }

    function it_throws_an_exception_if_trying_to_create_with_invalid_identifier()
    {
        $this->beConstructedWith('__not.valid__');

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
