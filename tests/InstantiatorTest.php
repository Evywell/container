<?php


namespace Tests;


use PHPUnit\Framework\TestCase;
use Raven\Container\Container;
use Raven\Container\Exception\InstantiatorException;
use Raven\Container\Instantiator;
use Raven\Container\InstantiatorInterface;
use Tests\Classes\ConstructorClass;
use Tests\Classes\ConstructorClassFour;
use Tests\Classes\ConstructorClassThree;
use Tests\Classes\ConstructorClassTwo;
use Tests\Classes\SimpleClass;
use Tests\Classes\SimpleInterface;

class InstantiatorTest extends TestCase
{

    /**
     * @var InstantiatorInterface
     */
    private $instantiator;

    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        $this->container = new Container();
        $this->instantiator = new Instantiator($this->container);
    }

    public function testInstanciateClassWithoutConstructor()
    {
        $instance = $this->instantiator->resolve(SimpleClass::class);
        $this->assertInstanceOf(SimpleClass::class, $instance);
    }

    public function testInstanciateClassWithConstructor()
    {
        /** @var ConstructorClass $instance */
        $instance = $this->instantiator->resolve(ConstructorClass::class, ['arg' => 'test']);
        $this->assertInstanceOf(ConstructorClass::class, $instance);
        $this->assertEquals("hello world", $instance->getSentence());
        $this->assertEquals("test", $instance->getArg());

        $this->expectException(InstantiatorException::class);
        $this->instantiator->resolve(ConstructorClass::class, []);

        /** @var ConstructorClassTwo $instance */
        $instance = $this->instantiator->resolve(ConstructorClassTwo::class, ['callback' => function ($world) { return 'hello ' . $world; }]);
        $this->assertInstanceOf(ConstructorClassTwo::class, $instance);
        $this->assertEquals('hello world', call_user_func($instance->getCallback(), 'world'));
    }

    public function testInstanciateClassWithConstructorClass()
    {
        $this->container->addDefinition(SimpleInterface::class, SimpleClass::class);
        /** @var ConstructorClassThree $instance */
        $instance = $this->instantiator->resolve(ConstructorClassThree::class);
        $this->assertInstanceOf(ConstructorClassThree::class, $instance);
        $this->assertInstanceOf(SimpleClass::class, $instance->getClass());

        $this->container->addDefinition(ConstructorClassThree::class, ConstructorClassThree::class);
        /** @var ConstructorClassFour $instance */
        $instance = $this->instantiator->resolve(ConstructorClassFour::class);
        $this->assertInstanceOf(ConstructorClassFour::class, $instance);
        $this->assertInstanceOf(ConstructorClassThree::class, $instance->getClassThree());
        $this->assertInstanceOf(SimpleClass::class, $instance->getClassThree()->getClass());
    }

}