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

        $baseContainer = new ContainerBuilder();
        $baseContainer->setDefinition('array_object', new Definition(\ArrayObject::class, [
            new Reference('__external__.array_object'),
        ]));

        $this->buildContainerWithDependencies($baseContainer, ['external' => $externalContainer]);

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

        $baseContainer = new ContainerBuilder();
        $baseContainer->setDefinition('array_object', new Definition(\ArrayObject::class, [
            ['std' => ['class' => new Reference('__external__.std_class')]],
        ]));

        $this->buildContainerWithDependencies($baseContainer, ['external' => $externalContainer]);

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

        $baseContainer = new ContainerBuilder();
        $baseContainer->setDefinition('array_object', new Definition(\ArrayObject::class, [
            new Definition(\ArrayObject::class, [
                ['std_class' => new Reference('__external__.std_class')],
            ]),
        ]));

        $this->buildContainerWithDependencies($baseContainer, ['external' => $externalContainer]);

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

        $baseContainer = new ContainerBuilder();
        $baseContainer->setParameter('parameter', '%__external__.parameter%');

        $this->buildContainerWithDependencies($baseContainer, ['external' => $externalContainer]);

        static::assertSame('42', $baseContainer->getParameter('parameter'));
    }

    /**
     * @test
     */
    public function it_resolves_cross_container_parameters_in_service_definition_array()
    {
        $externalContainer = new ContainerBuilder();
        $externalContainer->setParameter('parameter', '42');

        $baseContainer = new ContainerBuilder();
        $baseContainer->setDefinition('array_object', new Definition(\ArrayObject::class, [
            ['parameter' => '%__external__.parameter%'],
        ]));

        $this->buildContainerWithDependencies($baseContainer, ['external' => $externalContainer]);

        static::assertSame(['parameter' => '42'], $baseContainer->get('array_object')->getArrayCopy());
    }

    /**
     * @param ContainerBuilder $baseContainer
     * @param ContainerBuilder[] $externalContainers
     */
    private function buildContainerWithDependencies(ContainerBuilder $baseContainer, array $externalContainers)
    {
        $accessors = [];
        foreach ($externalContainers as $containerIdentifier => $container) {
            $container->compile();

            $accessors[$containerIdentifier] = new ContainerBasedContainerAccessor($container);
        }

        $crossContainerReferencesResolver = new ResolveCrossContainerReferencesPass($accessors);
        $crossContainerReferencesResolver->process($baseContainer);

        $baseContainer->compile();
    }
}
