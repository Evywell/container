<?php


namespace Tests\Classes;


class ConstructorClassFour
{

    /**
     * @var ConstructorClassThree
     */
    private $classThree;

    public function __construct(ConstructorClassThree $classThree)
    {
        $this->classThree = $classThree;
    }

    /**
     * @return ConstructorClassThree
     */
    public function getClassThree(): ConstructorClassThree
    {
        return $this->classThree;
    }

}