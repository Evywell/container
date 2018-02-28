<?php


namespace Raven\Container;

class Definition
{

    /**
     * @var string
     */
    private $id;

    /**
     * @var mixed
     */
    private $entry;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var bool
     */
    private $resolved;

    /**
     * @var bool
     */
    private $factory;

    /**
     * @var array
     */
    private $tags;

    public function __construct(string $id, $entry, array $parameters)
    {
        $this->id = $id;
        $this->entry = $entry;
        $this->parameters = $parameters;
        $this->resolved = false;
        $this->factory = false;
        $this->tags = [];
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return bool
     */
    public function isResolved(): bool
    {
        return $this->resolved;
    }

    /**
     * @param bool $resolved
     * @return $this
     */
    public function setResolved(bool $resolved): Definition
    {
        $this->resolved = $resolved;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFactory(): bool
    {
        return $this->factory;
    }

    /**
     * @param bool $factory
     * @return $this
     */
    public function setFactory(bool $factory): Definition
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * @param array $tags
     * @return Definition
     */
    public function setTags(array $tags): Definition
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @param string $tag
     * @return Definition
     */
    public function addTag(string $tag): Definition
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * @param string $tag
     * @return bool
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags);
    }
}
