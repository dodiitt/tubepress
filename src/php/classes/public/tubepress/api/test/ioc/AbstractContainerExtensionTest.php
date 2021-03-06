<?php
/**
 * Copyright 2006 - 2018 TubePress LLC (http://tubepress.com)
 *
 * This file is part of TubePress (http://tubepress.com)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
abstract class tubepress_api_test_ioc_AbstractContainerExtensionTest extends tubepress_api_test_TubePressUnitTest
{
    /**
     * @var Mockery\MockInterface
     */
    private $_mockDefinition;

    /**
     * @var Mockery\MockInterface
     */
    private $_mockContainer;

    /**
     * @var tubepress_spi_ioc_ContainerExtensionInterface
     */
    private $_sut;

    /**
     * @var array
     */
    private $_expectedServiceConstructions;

    /**
     * @var array
     */
    private $_expectedDecoratedServices;

    public function onSetup()
    {
        $this->_sut = $this->buildSut();

        $this->_mockContainer = $this->mock('tubepress_api_ioc_ContainerBuilderInterface');

        $this->_expectedServiceConstructions = array();
        $this->_expectedDecoratedServices    = array();
    }

    public function testLoad()
    {
        $this->prepareForLoad();

        $this->_sut->load($this->_mockContainer);

        $realContainerBuilder = new tubepress_internal_ioc_ContainerBuilder();

        $realContainerBuilder->registerExtension($this->_sut);

        foreach ($this->getExpectedExternalServicesMap() as $id => $type) {

            if (is_string($type)) {

                $realContainerBuilder->set($id, $this->mock($type));

            } else {

                $realContainerBuilder->set($id, $type);
            }
        }

        foreach ($this->getExpectedParameterMap() as $key => $value) {

            $realContainerBuilder->setParameter($key, $value);
        }

        foreach ($this->_expectedDecoratedServices as $serviceId) {

            $decoratedDefinition = new tubepress_internal_ioc_Definition($serviceId);
            $decoratedDefinition->setFactoryClass('Mockery');
            $decoratedDefinition->setFactoryMethod('mock');
            $decoratedDefinition->addArgument($serviceId);

            $realContainerBuilder->setDefinition(strtolower($serviceId), $decoratedDefinition);
        }

        $this->preCompile($realContainerBuilder);

        $realContainerBuilder->compile();

        $this->postCompile($realContainerBuilder);

        foreach ($this->_expectedServiceConstructions as $id => $type) {

            $this->assertTrue($realContainerBuilder->hasDefinition($id), "Expected that container has definition for $id");
            $this->assertTrue($realContainerBuilder->has($id), "Expected that container has definition for $id");

            $service = $realContainerBuilder->get($id);

            if (is_string($type)) {

                $this->assertInstanceOf($type, $service, "$id is not an instance of $type (actually is " . get_class($service) . ')');

            } else {

                /**
                 * @var $def tubepress_api_ioc_DefinitionInterface
                 */
                $def = $type;
                $this->assertInstanceOf($def->getClass(), $service);
            }
        }
    }

    protected function expectRegistration($id, $class)
    {
        $this->_startChain($class);

        $this->_mockContainer->shouldReceive('register')->once()->with($id, $class)->andReturn($this->_mockDefinition);

        $this->_expectedServiceConstructions[$id] = $class;

        return $this;
    }

    protected function expectDefinition($id, tubepress_api_ioc_DefinitionInterface $definition)
    {
        $this->_startChain($definition->getClass());

        $this->_expectedServiceConstructions[$id] = $definition;

        $this->_mockContainer->shouldReceive('setDefinition')->once()->with($id, Mockery::on(function ($actualDefinition) use ($definition) {

            return $actualDefinition instanceof tubepress_api_ioc_DefinitionInterface
            && $actualDefinition->getClass() === $definition->getClass();

        }))->andReturn($this->_mockDefinition);

        return $this;
    }

    protected function expectParameter($name, $value)
    {
        $this->_mockContainer->shouldReceive('setParameter')->once()->with($name, $value);
    }

    protected function withArgument($expected)
    {
        $argumentComparator = function ($actual) use ($expected) {

            if (is_array($actual)) {

                return is_array($expected) && $actual == $expected;
            }

            if (is_array($expected)) {

                return is_array($actual) && $actual === $expected;
            }

            if (is_bool($actual)) {

                return $actual === $expected;
            }

            return "$actual" === "$expected";
        };

        $this->_mockDefinition->shouldReceive('addArgument')->once()->with(Mockery::on($argumentComparator))->andReturn($this->_mockDefinition);

        return $this;
    }

    protected function withFactoryService($service)
    {
        $this->_mockDefinition->shouldReceive('setFactoryService')->once()->with(Mockery::on(function ($actual) use ($service) {

            return "$actual" === "$service";

        }))->andReturn($this->_mockDefinition);

        return $this;
    }

    protected function withDecoratedService($serviceId)
    {
        $this->_mockDefinition->shouldReceive('setDecoratedService')->once()->with($serviceId)->andReturn($this->_mockDefinition);

        $this->_expectedDecoratedServices[] = $serviceId;

        return $this;
    }

    protected function withMethodCall($methodName, array $arguments)
    {
        $this->_mockDefinition->shouldReceive('addMethodCall')->once()->with($methodName, $arguments)->andReturn($this->_mockDefinition);

        return $this;
    }

    protected function withFactoryClass($class)
    {
        $this->_mockDefinition->shouldReceive('setFactoryClass')->once()->with(Mockery::on(function ($actual) use ($class) {

            return $actual === $class;

        }))->andReturn($this->_mockDefinition);

        return $this;
    }

    protected function withFactoryMethod($method)
    {
        $this->_mockDefinition->shouldReceive('setFactoryMethod')->once()->with(Mockery::on(function ($actual) use ($method) {

            return $actual === $method;

        }))->andReturn($this->_mockDefinition);

        return $this;
    }

    protected function withTag($tag, $tagAttributes = array())
    {
        if (empty($tagAttributes)) {

            $this->_mockDefinition->shouldReceive('addTag')->once()->with($tag)->andReturn($this->_mockDefinition);

        } else {

            $this->_mockDefinition->shouldReceive('addTag')->once()->with($tag, $tagAttributes)->andReturn($this->_mockDefinition);
        }

        return $this;
    }

    protected function andReturnDefinition()
    {
        return $this->_mockDefinition;
    }

    protected function getDefinition($id)
    {
        if (!isset($this->_expectedServiceConstructions)) {

            throw new LogicException("$id is not registered yet");
        }

        $mockDefinition = $this->mock('tubepress_api_ioc_DefinitionInterface');

        $this->_mockContainer->shouldReceive('getDefinition')->once()->with($id)->andReturn($mockDefinition);

        return $mockDefinition;
    }

    /**
     * @return tubepress_spi_ioc_ContainerExtensionInterface
     */
    protected abstract function buildSut();

    protected abstract function prepareForLoad();

    protected abstract function getExpectedExternalServicesMap();

    protected function getExpectedParameterMap()
    {
        return array();
    }

    protected function postCompile(tubepress_internal_ioc_ContainerBuilder $containerBuilder)
    {
        return;
    }

    protected function preCompile(tubepress_internal_ioc_ContainerBuilder $containerBuilder)
    {
        return;
    }

    private function _startChain($class)
    {
        $this->_mockDefinition = $this->mock('tubepress_api_ioc_DefinitionInterface');

        $this->_mockDefinition->shouldReceive('getClass')->andReturn($class);
    }
}