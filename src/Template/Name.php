<?php

namespace DMJohnson\Contemplate\Template;

use DMJohnson\Contemplate\Engine;
use DMJohnson\Contemplate\Exception\TemplateNotFound;
use LogicException;

/**
 * A template name.
 */
class Name
{
    /**
     * Instance of the template engine.
     * @var Engine
     */
    protected $engine;

    /**
     * The original name.
     * @var string
     */
    protected $name;

    /**
     * The resource type.
     * @var ?string
     */
    protected $type;

    /**
     * The parsed template filename.
     * @var string
     */
    protected $file;

    /**
     * Create a new Name instance.
     * @param Engine $engine
     * @param string $name
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     */
    public function __construct(Engine $engine, $name, $type=null)
    {
        $this->setEngine($engine);
        $this->setName($name, $type);
    }

    /**
     * Set the engine.
     * @param  Engine $engine
     * @return Name
     */
    public function setEngine(Engine $engine)
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * Get the engine.
     * @return Engine
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Set the original name and parse it.
     * @param  string $name
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     * @return Name
     */
    public function setName($name, $type=null)
    {
        if ($name === '') throw new LogicException('Name cannot be empty');
        $this->name = $name;
        $this->type = $type;

        return $this;
    }

    /**
     * Get the original name.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the type.
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Resolve template path.
     * @return string
     */
    public function getPath()
    {
        return $this->engine->resolver->resolve($this->name, $this->type)->getOpenPath();
    }

    /**
     * Check if template path exists.
     * @return boolean
     */
    public function doesPathExist()
    {
        try{
            $this->engine->resolver->resolve($this->name, $this->type);
            return true;
        }
        catch (TemplateNotFound){
            return false;
        }
    }
}
