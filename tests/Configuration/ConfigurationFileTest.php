<?php

namespace Tests\Configuration;

use PHPUnit\Framework\TestCase;
use Raven\Container\Container;

class ConfigurationFileTest extends TestCase
{

    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        $this->container = new Container();
    }


}