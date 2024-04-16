<?php

namespace DMJohnson\Contemplate\Template;

use DMJohnson\Contemplate\Engine;
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
     * The parsed template folder.
     * @var Folder
     */
    protected $folder;

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
        $this->name = $name;

        $parts = explode('::', $this->name);

        if (count($parts) === 1) {
            $this->setFile($parts[0], $type);
        } elseif (count($parts) === 2) {
            $this->setFolder($parts[0]);
            $this->setFile($parts[1], $type);
        } else {
            throw new LogicException(
                'The template name "' . $this->name . '" is not valid. ' .
                'Do not use the folder namespace separator "::" more than once.'
            );
        }

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
     * Set the parsed template folder.
     * @param  string $folder
     * @return Name
     */
    public function setFolder($folder)
    {
        $this->folder = $this->engine->getFolders()->get($folder);

        return $this;
    }

    /**
     * Get the parsed template folder.
     * @return string
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Set the parsed template file.
     * @param  string $file
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     * @return Name
     */
    public function setFile($file, $type=null)
    {
        if ($file === '') {
            throw new LogicException(
                'The template name "' . $this->name . '" is not valid. ' .
                'The template name cannot be empty.'
            );
        }

        $this->file = $file;

        if (!is_null($this->engine->getFileExtension($type))) {
            $this->file .= '.' . $this->engine->getFileExtension($type);
        }

        return $this;
    }

    /**
     * Get the parsed template file.
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Resolve template path.
     * @return string
     */
    public function getPath()
    {
        $path = ($this->engine->getResolveTemplatePath())($this);

        return $path;
    }

    /**
     * Check if template path exists.
     * @return boolean
     */
    public function doesPathExist()
    {
        return is_file($this->getPath());
    }

    /**
     * Get the default templates directory.
     * @return string
     */
    protected function getDefaultDirectory()
    {
        $directory = $this->engine->getDirectory();

        if (is_null($directory)) {
            throw new LogicException(
                'The template name "' . $this->name . '" is not valid. '.
                'The default directory has not been defined.'
            );
        }

        return $directory;
    }
}
