<?php


namespace Tests\Classes;


class ConstructorClass
{

    /**
     * @var string
     */
    private $sentence;
    /**
     * @var string
     */
    private $arg;

    public function __construct(string $sentence = 'hello world', string $arg)
    {
        $this->sentence = $sentence;
        $this->arg = $arg;
    }

    /**
     * @return string
     */
    public function getSentence(): string
    {
        return $this->sentence;
    }

    /**
     * @return string
     */
    public function getArg(): string
    {
        return $this->arg;
    }

}