<?php

namespace DMJohnson\Contemplate;

use DMJohnson\Contemplate\Extension\ExtensionInterface;
use DMJohnson\Contemplate\Template\Data;
use DMJohnson\Contemplate\Template\Directory;
use DMJohnson\Contemplate\Template\FileExtension;
use DMJohnson\Contemplate\Template\Folders;
use DMJohnson\Contemplate\Template\Func;
use DMJohnson\Contemplate\Template\Functions;
use DMJohnson\Contemplate\Template\Name;
use DMJohnson\Contemplate\Template\Resolvable;
use DMJohnson\Contemplate\Template\ResolveTemplatePath;
use DMJohnson\Contemplate\Template\Template;
use DMJohnson\Contemplate\Template\Theme;

/**
 * Template API and environment settings storage.
 */
class Engine
{
    /**
     * Default template directory.
     * @var Directory
     */
    protected $directory;

    /**
     * Template file extension.
     * @var FileExtension
     */
    protected $fileExtension;

    /**
     * Collection of template folders.
     * @var Folders
     */
    protected $folders;

    /**
     * Collection of template functions.
     * @var Functions
     */
    protected $functions;

    /**
     * Collection of preassigned template data.
     * @var Data
     */
    protected $data;

    /** @var ResolveTemplatePath */
    private $resolveTemplatePath;

    /**
     * Create new Engine instance.
     * @param string $directory
     * @param string $fileExtension
     */
    public function __construct($directory = null, $fileExtension = 'php')
    {
        $this->directory = new Directory($directory);
        $this->fileExtension = new FileExtension($fileExtension);
        $this->folders = new Folders();
        $this->functions = new Functions();
        $this->data = new Data();
        $this->resolveTemplatePath = new ResolveTemplatePath\NameAndFolderResolveTemplatePath();
    }

    public static function fromTheme(Theme $theme, string $fileExtension = 'php'): self {
        $engine = new static(null, $fileExtension);
        $engine->setResolveTemplatePath(new ResolveTemplatePath\ThemeResolveTemplatePath($theme));
        return $engine;
    }

    public function setResolveTemplatePath(ResolveTemplatePath $resolveTemplatePath)
    {
        $this->resolveTemplatePath = $resolveTemplatePath;

        return $this;
    }

    public function getResolveTemplatePath(): ResolveTemplatePath
    {
        return $this->resolveTemplatePath;
    }

    /**
     * Set path to templates directory.
     * @param  string|null $directory Pass null to disable the default directory.
     * @return Engine
     */
    public function setDirectory($directory)
    {
        $this->directory->set($directory);

        return $this;
    }

    /**
     * Get path to templates directory.
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory->get();
    }

    /**
     * Set the template file extension.
     * @param  string|null $fileExtension Pass null to manually set it.
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     * @return Engine
     */
    public function setFileExtension($fileExtension, $type=null)
    {
        $this->fileExtension->set($fileExtension, $type);

        return $this;
    }

    /**
     * Get the template file extension.
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     * @return string
     */
    public function getFileExtension($type=null)
    {
        return $this->fileExtension->get($type);
    }

    /**
     * Add a new template folder for grouping templates under different namespaces.
     * @param  string  $name
     * @param  string  $directory
     * @param  boolean $fallback
     * @return Engine
     */
    public function addFolder($name, $directory, $fallback = false)
    {
        $this->folders->add($name, $directory, $fallback);

        return $this;
    }

    /**
     * Remove a template folder.
     * @param  string $name
     * @return Engine
     */
    public function removeFolder($name)
    {
        $this->folders->remove($name);

        return $this;
    }

    /**
     * Get collection of all template folders.
     * @return Folders
     */
    public function getFolders()
    {
        return $this->folders;
    }

    /**
     * Add preassigned template data.
     * @param  array             $data;
     * @param  null|string|array $templates;
     * @return Engine
     */
    public function addData(array $data, $templates = null)
    {
        $this->data->add($data, $templates);

        return $this;
    }

    /**
     * Get all preassigned template data.
     * @param  null|string $template;
     * @return array
     */
    public function getData($template = null)
    {
        return $this->data->get($template);
    }

    /**
     * Register a new template function.
     * @param  string   $name;
     * @param  callable $callback;
     * @return Engine
     */
    public function registerFunction($name, $callback)
    {
        $this->functions->add($name, $callback);

        return $this;
    }

    /**
     * Remove a template function.
     * @param  string $name;
     * @return Engine
     */
    public function dropFunction($name)
    {
        $this->functions->remove($name);

        return $this;
    }

    /**
     * Get a template function.
     * @param  string $name
     * @return Func
     */
    public function getFunction($name)
    {
        return $this->functions->get($name);
    }

    /**
     * Check if a template function exists.
     * @param  string  $name
     * @return boolean
     */
    public function doesFunctionExist($name)
    {
        return $this->functions->exists($name);
    }

    /**
     * Load an extension.
     * @param  ExtensionInterface $extension
     * @return Engine
     */
    public function loadExtension(ExtensionInterface $extension)
    {
        $extension->register($this);

        return $this;
    }

    /**
     * Load multiple extensions.
     * @param  array  $extensions
     * @return Engine
     */
    public function loadExtensions(array $extensions = array())
    {
        foreach ($extensions as $extension) {
            $this->loadExtension($extension);
        }

        return $this;
    }

    /**
     * Resolve a name to a Resolvable
     * 
     * @template T of Resolvable
     * @param string $name
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     * @param class-string<T> $class The `Resolvable` subclass to use to resolve.
     * @return T
     */
    public function resolve($name, $type = null, $class = Resolvable::class)
    {
        return new $class($this, $name, $type);
    }

    /**
     * Get a template path.
     * @param  string $name
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     * @return string
     */
    public function path($name, $type = null)
    {
        $name = new Name($this, $name, $type);

        return $name->getPath();
    }

    /**
     * Check if a template exists.
     * @param  string  $name
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     * @return boolean
     */
    public function exists($name, $type = null)
    {
        $name = new Name($this, $name, $type);

        return $name->doesPathExist();
    }

    /**
     * Create a new template.
     * @param  string   $name
     * @param  array    $data
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     * @return Template
     */
    public function make($name, array $data = array(), $type = Resolvable::TYPE_TEMPLATE)
    {
        $template = new Template($this, $name, $type);
        $template->data($data);
        return $template;
    }

    /**
     * Create a new template and render it.
     * @param  string $name
     * @param  array  $data
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     * @return string
     */
    public function render($name, array $data = array(), $type = Resolvable::TYPE_TEMPLATE)
    {
        return $this->make($name, $data, $type)->render();
    }

    /**
     * Import and return a value from a PHP script
     * @param  string $name
     * @param  array  $data
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     * @return mixed
     */
    public function import($name, array $data=[], $type = null)
    {
        return $this->resolve($name, $type)->import($data);
    }
}
