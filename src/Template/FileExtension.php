<?php

namespace DMJohnson\Contemplate\Template;

/**
 * Template file extension.
 */
class FileExtension
{
    /**
     * Default resolvable file extension.
     * @var string
     */
    protected $fileExtension;
    /**
     * Additional resolvable file extensions, by type
     * @var string[]
     */
    protected $typedFileExtensions;

    /**
     * Create new FileExtension instance.
     * @param null|string $fileExtension
     */
    public function __construct($fileExtension = 'php', $typedFileExtensions = [])
    {
        $this->set($fileExtension);
        foreach ($typedFileExtensions as $key=>$value) $this->set($value, $key);
    }

    /**
     * Set the template file extension.
     * @param  null|string   $fileExtension
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     * @return FileExtension
     */
    public function set($fileExtension, $type=null)
    {
        if (!is_null($type)) {
            $this->typedFileExtensions[$type] = $fileExtension;
        }
        else {
            $this->fileExtension = $fileExtension;
        }

        return $this;
    }

    /**
     * Get the template file extension.
     * @param string|null $type An optional value specifying the type of object to resolve. This 
     * is used to allow multiple types of `Resolvable`s to exist under the same name (e.g. a 
     * template, multiple controllers, static resources, etc...).
     * @return string The extension for the given type, or the default extension if the given type 
     * did not have an extension registered.
     */
    public function get($type=null)
    {
        if (!is_null($type) && isset($this->typedFileExtensions) && array_key_exists($type, $this->typedFileExtensions)) {
            return $this->typedFileExtensions[$type];
        }
        else {
            return $this->fileExtension;
        }
    }
}
