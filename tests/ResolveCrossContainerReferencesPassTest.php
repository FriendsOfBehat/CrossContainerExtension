<?php

namespace tests\FriendsOfBehat\CrossContainer\Extension;

use FriendsOfBehat\CrossContainerExtension\ContainerBasedContainerAccessor;
use FriendsOfBehat\CrossContainerExtension\ResolveCrossContainerReferencesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class ResolveCrossContainerReferencesPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_resolves_cross_container_references_in_service_argument()
    {
        $externalContainer = new ContainerBuilder();
        $externalContainer->setDefinition('array_object', new Definition(\ArrayObject::class, [['foo' => 'bar']]));
        $externalContainer->compile();

        $baseContainer = new ContainerBuilder();
        $baseContainer->setDefinition('array_object', new Definition(\ArrayObject::class, [
            new Reference('__external__.array_object'),
        ]));

        (new ResolveCrossContainerReferencesPass([
            'external' => new ContainerBasedContainerAccessor($externalContainer),
        ]))->process($baseContainer);

        $baseContainer->compile();

        static::assertTrue($baseContainer->has('array_object'));
        static::assertInstanceOf(\ArrayObject::class, $baseContainer->get('array_object'));
        static::assertSame(['foo' => 'bar'], $baseContainer->get('array_object')->getArrayCopy());
    }

    /**
     * @test
     */
    public function it_resolves_cross_container_references_in_service_argument_array()
    {
        $externalContainer = new ContainerBuilder();
        $externalContainer->setDefinition('std_class', new Definition(\stdClass::class));
        $externalContainer->compile();

        $baseContainer = new ContainerBuilder();
        $baseContainer->setDefinition('array_object', new Definition(\ArrayObject::class, [
            ['std_class' => new Reference('__external__.std_class')],
        ]));

        (new ResolveCrossContainerReferencesPass([
            'external' => new ContainerBasedContainerAccessor($externalContainer),
        ]))->process($baseContainer);

        $baseContainer->compile();

        static::assertTrue($baseContainer->has('array_object'));
        static::assertInstanceOf(\ArrayObject::class, $baseContainer->get('array_object'));
        static::assertInstanceOf(\stdClass::class, $baseContainer->get('array_object')['std_class']);
    }

    /**
     * @test
     */
    public function it_resolves_cross_container_references_in_service_argument_nested_array()
    {
        $externalContainer = new ContainerBuilder();
        $externalContainer->setDefinition('std_class', new Definition(\stdClass::class));
        $externalContainer->compile();

        $baseContainer = new ContainerBuilder();
        $baseContainer->setDefinition('array_object', new Definition(\ArrayObject::class, [
            ['std' => ['class' => new Reference('__external__.std_class')]],
        ]));

        (new ResolveCrossContainerReferencesPass([
            'external' => new ContainerBasedContainerAccessor($externalContainer),
        ]))->process($baseContainer);

        $baseContainer->compile();

        static::assertTrue($baseContainer->has('array_object'));
        static::assertInstanceOf(\ArrayObject::class, $baseContainer->get('array_object'));
        static::assertInstanceOf(\stdClass::class, $baseContainer->get('array_object')['std']['class']);
    }

    /**
     * @test
     */
    public function it_resolves_cross_container_references_in_service_argument_anonymous_definition()
    {
        $externalContainer = new ContainerBuilder();
        $externalContainer->setDefinition('std_class', new Definition(\stdClass::class));
        $externalContainer->compile();

        $baseContainer = new ContainerBuilder();
        $baseContainer->setDefinition('array_object', new Definition(\ArrayObject::class, [
            new Definition(\ArrayObject::class, [
                ['std_class' => new Reference('__external__.std_class')],
            ]),
        ]));

        (new ResolveCrossContainerReferencesPass([
            'external' => new ContainerBasedContainerAccessor($externalContainer),
        ]))->process($baseContainer);

        $baseContainer->compile();

        static::assertTrue($baseContainer->has('array_object'));
        static::assertInstanceOf(\ArrayObject::class, $baseContainer->get('array_object'));
        static::assertInstanceOf(\stdClass::class, $baseContainer->get('array_object')->getArrayCopy()['std_class']);
    }

    /**
     * @test
     */
    public function it_resolves_cross_container_parameters_inline_in_parameter()
    {
        $externalContainer = new ContainerBuilder();
        $externalContainer->setParameter('parameter', '42');
        $externalContainer->compile();

        $baseContainer = new ContainerBuilder();
        $baseContainer->setParameter('parameter', '%__external__.parameter%');

        (new ResolveCrossContainerReferencesPass([
            'external' => new ContainerBasedContainerAccessor($externalContainer),
        ]))->process($baseContainer);

        $baseContainer->compile();

        static::assertTrue($baseContainer->hasParameter('parameter'));
        static::assertSame('42', $baseContainer->getParameter('parameter'));
    }

    /**
     * @test
     */
    public function it_resolves_cross_container_parameters_in_service_definition_array()
    {
        $externalContainer = new ContainerBuilder();
        $externalContainer->setParameter('parameter', '42');
        $externalContainer->compile();

        $baseContainer = new ContainerBuilder();
        $baseContainer->setDefinition('array_object', new Definition(\ArrayObject::class, [
            ['parameter' => '%__external__.parameter%'],
        ]));

        (new ResolveCrossContainerReferencesPass([
            'external' => new ContainerBasedContainerAccessor($externalContainer),
        ]))->process($baseContainer);

        $baseContainer->compile();

        static::assertTrue($baseContainer->hasDefinition('array_object'));
        static::assertSame(['parameter' => '42'], $baseContainer->get('array_object')->getArrayCopy());
    }
}
