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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class CrossContainerProcessor implements CompilerPassInterface
{
    /**
     * @var ContainerAccessor[]
     */
    private $containerAccessors;

    /**
     * @param ContainerAccessor[] $containerAccessors
     */
    public function __construct(array $containerAccessors = [])
    {
        foreach ($containerAccessors as $containerIdentifier => $containerAccessor) {
            $this->addContainerAccessor($containerIdentifier, $containerAccessor);
        }
    }

    /**
     * @param string $containerIdentifier
     * @param ContainerAccessor $containerAccessor
     */
    public function addContainerAccessor($containerIdentifier, ContainerAccessor $containerAccessor)
    {
        $this->containerAccessors[$containerIdentifier] = $containerAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $identifier => $definition) {
            $container->setDefinition($identifier, $this->resolveDefinition($definition));
        }

        $this->copyParameters($container);
    }

    /**
     * @param Definition $definition
     *
     * @return Definition
     */
    private function resolveDefinition(Definition $definition)
    {
        $definition->setArguments($this->resolveArguments($definition->getArguments()));
        $definition->setFactory($this->resolveFactory($definition->getFactory()));

        return $definition;
    }

    /**
     * @param array|null $factory
     *
     * @return array|null
     */
    private function resolveFactory($factory)
    {
        if ([] === $factory) {
            return [];
        }

        if (isset($factory[0]) && $factory[0] instanceof Reference) {
            $factory[0] = $this->resolveReference($factory[0]);
        }

        return $factory;
    }

    /**
     * @param array $arguments
     *
     * @return array
     */
    private function resolveArguments(array $arguments)
    {
        return array_map(function ($argument){
            return $this->resolveArgument($argument);
        }, $arguments);
    }

    /**
     * @param mixed $argument
     *
     * @return mixed
     */
    private function resolveArgument($argument)
    {
        if ($argument instanceof Definition) {
            return $this->resolveDefinition($argument);
        }

        if ($argument instanceof Reference) {
            return $this->resolveReference($argument);
        }

        if (is_array($argument)) {
            return $this->resolveArguments($argument);
        }

        return $argument;
    }

    /**
     * @param Reference $reference
     *
     * @return Definition|Reference
     */
    private function resolveReference(Reference $reference)
    {
        if (!ExternalReference::isValid($reference)) {
            return $reference;
        }

        return $this->transformReferenceToDefinition(new ExternalReference($reference));
    }

    /**
     * @param ExternalReference $externalReference
     *
     * @return Definition
     */
    private function transformReferenceToDefinition(ExternalReference $externalReference)
    {
        $this->assertExternalReferenceHasKnownContainer($externalReference);

        $definition = new Definition(null, [$externalReference->serviceIdentifier()]);
        $definition->setFactory([$this->containerAccessors[$externalReference->containerIdentifier()], 'getService']);

        return $definition;
    }

    /**
     * @param ExternalReference $externalReference
     *
     * @throws \DomainException
     */
    private function assertExternalReferenceHasKnownContainer(ExternalReference $externalReference)
    {
        if (!isset($this->containerAccessors[$externalReference->containerIdentifier()])) {
            throw new \DomainException(sprintf(
                'External container with identifier "%s" does not exist.',
                $externalReference->containerIdentifier()
            ));
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    private function copyParameters(ContainerBuilder $container)
    {
        foreach ($this->containerAccessors as $containerIdentifier => $containerAccessor) {
            foreach ($containerAccessor->getParameters() as $name => $value) {
                $container->setParameter(sprintf('__%s__.%s', $containerIdentifier, $name), $value);
            }
        }
    }
}
