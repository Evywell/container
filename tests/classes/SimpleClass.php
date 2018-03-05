<?php
namespace Tests\Classes;

class SimpleClass implements SimpleInterface
{

    public function sayHello(string $sentence = "hello"): string
    {
        return $sentence;
    }

    public function someMethod(SimpleInterface $class, $arg = 3): int
    {
        return $arg;
    }

}