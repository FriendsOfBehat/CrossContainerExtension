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

namespace FriendsOfBehat\CrossContainerExtension;

use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
final class ExternalReference
{
    /**
     * @var string
     */
    private $containerIdentifier;

    /**
     * @var string
     */
    private $serviceIdentifier;

    /**
     * @param string|Reference $identifierOrReference
     *
     * @throw \InvalidArgumentException If given argument is not an external reference.
     */
    public function __construct($identifierOrReference)
    {
        if (!preg_match('/^__(?P<container_identifier>.+?)__\.(?P<service_identifier>.++)$/', (string) $identifierOrReference, $matches)) {
            throw new \InvalidArgumentException(sprintf(
                'Given argument "%s" is not an external reference.',
                $identifierOrReference
            ));
        }

        $this->containerIdentifier = $matches['container_identifier'];
        $this->serviceIdentifier = $matches['service_identifier'];
    }

    /**
     * @param string|Reference $identifier
     *
     * @return bool
     */
    public static function isValid($identifier): bool
    {
        try {
            new static($identifier);

            return true;
        } catch (\InvalidArgumentException $exception) {
            return false;
        }
    }

    /**
     * @return string
     */
    public function containerIdentifier(): string
    {
        return $this->containerIdentifier;
    }

    /**
     * @return string
     */
    public function serviceIdentifier(): string
    {
        return $this->serviceIdentifier;
    }
}
