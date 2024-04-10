<?php

namespace DMJohnson\Contemplate\Template;

use Exception;
use DMJohnson\Contemplate\Engine;
use DMJohnson\Contemplate\Exception\TemplateNotFound;
use LogicException;
use Throwable;

/**
 * Container which holds template data and provides access to template functions.
 */
class Resolvable
{
    /**
     * Instance of the template engine.
     * @var Engine
     */
    protected $engine;

    /**
     * The name of the template.
     * @var Name
     */
    protected $name;

    const TYPE_TEMPLATE = '__TEMPLATE__';
    const TYPE_CONTROLLER_GET = '__HTTP_GET__';
    const TYPE_CONTROLLER_POST = '__HTTP_POST__';

    /**
     * Create new Resolvable instance.
     * @param Engine $engine
     * @param string $name
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     */
    public function __construct(Engine $engine, $name, $type=null)
    {
        $this->engine = $engine;
        $this->name = new Name($engine, $name, $type);
    }

    /**
     * Magic method used to call extension functions.
     * @param  string $name
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->engine->getFunction($name)->call($this, $arguments);
    }

    /**
     * Check if the template exists.
     * @return boolean
     */
    public function exists()
    {
        try {
            ($this->engine->getResolveTemplatePath())($this->name);
            return true;
        } catch (TemplateNotFound $e) {
            return false;
        }
    }

    /**
     * Get the template path.
     * @return string
     */
    public function path()
    {
        try {
            return ($this->engine->getResolveTemplatePath())($this->name);
        } catch (TemplateNotFound $e) {
            return $e->paths()[0];
        }
    }

    /**
     * Fetch the returned value of the called script
     * @return mixed
     */
    public function import($params=[])
    {
        $path = ($this->engine->getResolveTemplatePath())($this->name);
        return (function() { // Wrap in function call to ensure "pure" scope
            \extract(\func_get_arg(1));
            return require(\func_get_arg(0));
        })($path, $params);
    }

    /**
     * Resolve another Resolvable with the same name as this one, but a different type
     * 
     * @template T of Resolvable
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     * @param class-string<T> $class The `Resolvable` subclass to use to resolve.
     * @return T
     */
    public function resolveAssociated($type = null, $class = Resolvable::class)
    {
        return new $class($this->engine, $this->name->getName(), $type);
    }

    /**
     * Get a path for another Resolvable with the same name as this one, but a different type.
     * 
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     * @return string
     */
    public function pathAssociated($type = null)
    {
        $name = new Name($this->engine, $this->name->getName(), $type);

        return $name->getPath();
    }

    /**
     * Check if a another Resolvable with the same name as this one, but a different type, exists.
     * 
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     * @return boolean
     */
    public function existsAssociated($type = null)
    {
        $name = new Name($this->engine, $this->name->getName(), $type);

        return $name->doesPathExist();
    }

    /**
     * Create a new Template with the same name as this Resolvable, but a different type.
     * 
     * @param  array    $data
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     * @return Template
     */
    public function makeAssociated(array $data = array(), $type = Resolvable::TYPE_TEMPLATE)
    {
        $template = new Template($this->engine, $this->name->getName(), $type);
        $template->data($data);
        return $template;
    }

    /**
     * Create a new Template with the same name as this Resolvable, but a different type, and render it.
     * 
     * @param  array  $data
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     * @return string
     */
    public function renderAssociated(array $data = array(), $type = Resolvable::TYPE_TEMPLATE)
    {
        return $this->makeAssociated($data, $type)->render();
    }

    /**
     * Import and return a value from a PHP script
     * 
     * @param  array  $data
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     * @return mixed
     */
    public function importAssociated(array $data=[], $type = null)
    {
        return $this->resolveAssociated($type)->import($data);
    }
}
