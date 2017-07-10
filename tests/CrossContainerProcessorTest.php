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

namespace tests\FriendsOfBehat\CrossContainer\Extension;

use FriendsOfBehat\CrossContainerExtension\ContainerBasedContainerAccessor;
use FriendsOfBehat\CrossContainerExtension\CrossContainerProcessor;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class CrossContainerProcessorTest extends TestCase
{
    /**
     * @test
     */
    public function it_resolves_cross_container_references_in_service_argument(): void
    {
        $externalContainer = new ContainerBuilder();
        $externalContainer->setDefinition('array_object', new Definition(\ArrayObject::class, [['foo' => 'bar']]));

        $baseContainer = new ContainerBuilder();
        $baseContainer->setDefinition('array_object', new Definition(\ArrayObject::class, [
            new Reference('__external__.array_object'),
        ]));

        $this->buildContainerWithDependencies($baseContainer, ['external' => $externalContainer]);

        Assert::assertInstanceOf(\ArrayObject::class, $baseContainer->get('array_object'));
        Assert::assertSame(['foo' => 'bar'], $baseContainer->get('array_object')->getArrayCopy());
    }

    /**
     * @test
     */
    public function it_resolves_cross_container_references_in_service_argument_array(): void
    {
        $externalContainer = new ContainerBuilder();
        $externalContainer->setDefinition('std_class', new Definition(\stdClass::class));

        $baseContainer = new ContainerBuilder();
        $baseContainer->setDefinition('array_object', new Definition(\ArrayObject::class, [
            ['std' => ['class' => new Reference('__external__.std_class')]],
        ]));

        $this->buildContainerWithDependencies($baseContainer, ['external' => $externalContainer]);

        Assert::assertInstanceOf(\ArrayObject::class, $baseContainer->get('array_object'));
        Assert::assertInstanceOf(\stdClass::class, $baseContainer->get('array_object')['std']['class']);
    }

    /**
     * @test
     */
    public function it_resolves_cross_container_references_in_service_argument_anonymous_definition(): void
    {
        $externalContainer = new ContainerBuilder();
        $externalContainer->setDefinition('std_class', new Definition(\stdClass::class));

        $baseContainer = new ContainerBuilder();
        $baseContainer->setDefinition('array_object', new Definition(\ArrayObject::class, [
            new Definition(\ArrayObject::class, [
                ['std_class' => new Reference('__external__.std_class')],
            ]),
        ]));

        $this->buildContainerWithDependencies($baseContainer, ['external' => $externalContainer]);

        Assert::assertInstanceOf(\ArrayObject::class, $baseContainer->get('array_object'));
        Assert::assertInstanceOf(\stdClass::class, $baseContainer->get('array_object')->getArrayCopy()['std_class']);
    }

    /**
     * @test
     */
    public function it_resolves_cross_container_references_in_service_factory(): void
    {
        $externalContainer = new ContainerBuilder();
        $externalContainer->setDefinition('array_object_factory', new Definition(\ArrayObject::class, [['old' => 'old']]));

        $baseContainer = new ContainerBuilder();
        $baseContainer->setDefinition('array_object',
            (new Definition(\ArrayObject::class, [['new' => 'new']]))->setFactory([new Reference('__external__.array_object_factory'), 'exchangeArray'])
        );

        $this->buildContainerWithDependencies($baseContainer, ['external' => $externalContainer]);

        Assert::assertSame(['old' => 'old'], $baseContainer->get('array_object'));
    }

    /**
     * @test
     */
    public function it_resolves_cross_container_parameters_inline_in_parameter(): void
    {
        $externalContainer = new ContainerBuilder();
        $externalContainer->setParameter('parameter', '42');

        $baseContainer = new ContainerBuilder();
        $baseContainer->setParameter('parameter', '%__external__.parameter%');

        $this->buildContainerWithDependencies($baseContainer, ['external' => $externalContainer]);

        Assert::assertSame('42', $baseContainer->getParameter('parameter'));
    }

    /**
     * @test
     */
    public function it_resolves_cross_container_parameters_depending_on_another_parameters(): void
    {
        $externalContainer = new ContainerBuilder();
        $externalContainer->setParameter('parameter', '%nested_parameter%');
        $externalContainer->setParameter('nested_parameter', '42');

        $baseContainer = new ContainerBuilder();
        $baseContainer->setParameter('parameter', '%__external__.parameter%');

        $this->buildContainerWithDependencies($baseContainer, ['external' => $externalContainer]);

        Assert::assertSame('42', $baseContainer->getParameter('parameter'));
    }

    /**
     * @test
     */
    public function it_resolves_cross_container_parameters_in_service_definition_array(): void
    {
        $externalContainer = new ContainerBuilder();
        $externalContainer->setParameter('parameter', '42');

        $baseContainer = new ContainerBuilder();
        $baseContainer->setDefinition('array_object', new Definition(\ArrayObject::class, [
            ['parameter' => '%__external__.parameter%'],
        ]));

        $this->buildContainerWithDependencies($baseContainer, ['external' => $externalContainer]);

        Assert::assertSame(['parameter' => '42'], $baseContainer->get('array_object')->getArrayCopy());
    }

    /**
     * @test
     */
    public function it_resolves_cross_container_escaped_parameters(): void
    {
        $externalContainer = new ContainerBuilder();
        $externalContainer->setParameter('parameter', '%%s?%%s');

        $baseContainer = new ContainerBuilder();
        $baseContainer->setParameter('parameter', '%__external__.parameter%');

        $this->buildContainerWithDependencies($baseContainer, ['external' => $externalContainer]);

        Assert::assertSame('%s?%s', $baseContainer->getParameter('parameter'));
    }

    /**
     * @test
     */
    public function it_resolves_cross_container_references_in_method_calls(): void
    {
        $externalContainer = new ContainerBuilder();
        $externalContainer->setDefinition('array_object', new Definition(\ArrayObject::class, [['foo' => 'bar']]));

        $baseContainer = new ContainerBuilder();
        $baseContainer->setDefinition('array_object', (new Definition(\ArrayObject::class, [[]]))->addMethodCall('exchangeArray', [new Reference('__external__.array_object')]));

        $this->buildContainerWithDependencies($baseContainer, ['external' => $externalContainer]);

        Assert::assertInstanceOf(\ArrayObject::class, $baseContainer->get('array_object'));
        Assert::assertSame(['foo' => 'bar'], $baseContainer->get('array_object')->getArrayCopy());
    }

    /**
     * @param ContainerBuilder $baseContainer
     * @param ContainerBuilder[] $externalContainers
     */
    private function buildContainerWithDependencies(ContainerBuilder $baseContainer, array $externalContainers): void
    {
        $accessors = [];
        foreach ($externalContainers as $containerIdentifier => $container) {
            $accessors[$containerIdentifier] = new ContainerBasedContainerAccessor($container);
        }

        $crossContainerReferencesResolver = new CrossContainerProcessor($accessors);
        $crossContainerReferencesResolver->process($baseContainer);

        $baseContainer->compile();
    }
}
