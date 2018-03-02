<?php


namespace Tests\Classes;


class ConstructorClassThree
{

    /**
     * @var SimpleClass
     */
    private $class;

    public function __construct(SimpleInterface $class)
    {
        $this->class = $class;
    }

    /**
     * @return SimpleClass
     */
    public function getClass(): SimpleClass
    {
        return $this->class;
    }

}