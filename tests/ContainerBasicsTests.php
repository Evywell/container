<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Raven\Container\Container;
use Raven\Container\Definition;
use Raven\Container\Exception\ContainerParameterException;
use Tests\Classes\ConstructorClass;
use Tests\Classes\SimpleClass;

class ContainerBasicsTests extends TestCase
{

    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        $this->container = new Container();
    }

    public function testCustomDefinitionKey()
    {
        $this->container->addDefinitionKey('mycustomdefinition');
        $result = $this->container->hasMyCustomDefinition('super');
        $this->assertFalse($result);
        $this->container->addDefinition('super', 'hello', [], 'mycustomdefinition');
        $this->assertEquals('hello', $this->container->getMyCustomDefinition('super'));
    }

    public function testGetParameter()
    {
        $this->container->addParameter('connection', ['host' => 'localhost', 'dbname' => 'test']);
        $connection = $this->container->getParameter('connection');
        $this->assertEquals(['host' => 'localhost', 'dbname' => 'test'], $connection);
        $this->container->addParameter('dbname', 'test');
        $this->container->addParameter('connection.default', ['host' => 'localhost', 'dbname' => '%dbname%']);
        $connection = $this->container->getParameter('connection.default');
        $this->assertEquals(['host' => 'localhost', 'dbname' => 'test'], $connection);
        $this->container->addParameter('lol', [1, 2, 3, 4, 5, 6, true]);
        $this->container->addParameter('a', ['I', 'like', 'chocolate', '%lol%']);
        $this->container->addParameter('arrayparameter', ['test', '%a%', true]);
        $param = $this->container->getParameter('arrayparameter');
        $this->assertEquals(['test', ['I', 'like', 'chocolate', [1, 2, 3, 4, 5, 6, true]], true], $param);

        $this->container->addParameter('prefix', 'super');
        $this->container->addParameter('test', '%prefix%.super');
        $this->assertEquals('super.super', $this->container->getParameter('test'));

        $this->container->addParameter('test2', '%b%');
        $this->expectException(ContainerParameterException::class);
        $this->container->getParameter('test2');
    }


    public function testResolveCallback()
    {
        $method_resolve = $this->resolveMethod();

        // Callback sans paramètres
        $definition = new Definition('callback', function () {
            return 'hello world';
        }, []);
        $callback = $method_resolve->invoke($this->container, $definition);
        $this->assertEquals('hello world', $callback);

        // Callback avec paramètres
        $definition = new Definition('callback', function (string $world) {
            return 'hello ' . $world;
        }, ['world']);
        $callback = $method_resolve->invoke($this->container, $definition);
        $this->assertEquals('hello world', $callback);

        // Callback avec paramètre erroné
        $definition = new Definition('callback', function (string $world) {
            return 'hello ' . $world;
        }, ['worl']);
        $callback = $method_resolve->invoke($this->container, $definition);
        $this->assertNotEquals('hello world', $callback);
    }

    public function testResolveSimpleInstance()
    {
        $method_resolve = $this->resolveMethod();

        $definition = new Definition('simpleClass', new \stdClass(), []);
        $class = $method_resolve->invoke($this->container, $definition);
        $this->assertInstanceOf(\stdClass::class, $class);

        $definition = new Definition('simpleClass', new SimpleClass(), []);
        $class = $method_resolve->invoke($this->container, $definition);
        $this->assertInstanceOf(SimpleClass::class, $class);

        $definition = new Definition('simpleClass', SimpleClass::class, []);
        $class = $method_resolve->invoke($this->container, $definition);
        $this->assertInstanceOf(SimpleClass::class, $class);
    }

    public function testCheckFactory()
    {
        $definition = $this->container->addDefinition('simpleClass', SimpleClass::class);
        $definition->setFactory(true);
        $instance1 = $this->container->get('simpleClass');
        $instance2 = $this->container->get('simpleClass');
        $this->assertFalse($instance1 === $instance2);
    }

    public function testResolveClassWithContainerParameter()
    {
        $this->container->addParameter('sentence', 'dlrow olleh');
        $this->container->addDefinition(ConstructorClass::class, ConstructorClass::class, ['sentence' => '%sentence%', 'arg' => 'test']);
        /** @var ConstructorClass $instance */
        $instance = $this->container->get(ConstructorClass::class);
        $this->assertInstanceOf(ConstructorClass::class, $instance);
        $this->assertEquals('dlrow olleh', $instance->getSentence());
    }

    public function testSomeExperiences()
    {
        $this->container->addDefinitionKey('bundle');
        $def = $this->container->addDefinition(SimpleClass::class, SimpleClass::class, [], 'bundle');
        $def->addTag('bundle.tag');
        $bundle = $this->container->getBundle(SimpleClass::class);
        $this->assertInstanceOf(SimpleClass::class, $bundle);
        $this->container->addAlias('bundle', $def);
        $ids = $this->container->getIdsWithTag('bundle.tag');
        $bundle = $this->container->getBundle($ids[0]);
        $this->assertInstanceOf(SimpleClass::class, $bundle);
        $this->assertInstanceOf(SimpleClass::class, $this->container->getBundle('bundle'));
    }

    public function testGetDefinitionKeys()
    {
        $this->container->addDefinitionKey('bundle');
        $keys = $this->container->getDefinitionKeys();
        $this->assertCount(3, $keys);
    }

    private function resolveMethod(): \ReflectionMethod
    {
        $reflection_container = new \ReflectionClass(Container::class);
        $method_resolve = $reflection_container->getMethod('resolve');
        $method_resolve->setAccessible(true);

        return $method_resolve;
    }

}