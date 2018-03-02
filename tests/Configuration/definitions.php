<?php
return [
    'simple' => \Tests\Classes\SimpleClass::class,
    \Tests\Classes\SimpleInterface::class => \Tests\Classes\SimpleClass::class,
    'constructor' => [\Tests\Classes\ConstructorClass::class, ['sentence' => 'hello world', 'arg' => 'myArg']],
    \Tests\Classes\ConstructorClassTwo::class => [\Tests\Classes\ConstructorClassTwo::class, ['callback' => function () { return 'hello world'; }]],
    'simpleWithAlias' => [\Tests\Classes\SimpleClass::class, null, ['alias' => ['simpleAlias']]],
    \Tests\Classes\ConstructorClassThree::class => \Tests\Classes\ConstructorClassThree::class,
    'a' => [\Tests\Classes\SimpleClass::class, null, ['tags' => ['si']]],
    'b' => [\Tests\Classes\ConstructorClass::class,  ['sentence' => 'hello world', 'arg' => 'myArg'], ['tags' => ['si']]],
    'c' => [\Tests\Classes\ConstructorClassTwo::class, ['callback' => function () { return 'hello world'; }], ['tags' => ['si']]],
];