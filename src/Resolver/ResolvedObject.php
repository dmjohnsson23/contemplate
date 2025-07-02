<?php
namespace DMJohnson\Contemplate\Resolver;

use DMJohnson\Contemplate\Exception\TemplateNotFound;

abstract class ResolvedObject{
    /**
     * Get a path or URL, suitable for passing to `fopen` and friends, to open the resource
     * 
     * @return string The path
     */
    abstract function getOpenPath();

    /**
     * Get the unique, normalized name or ID which will always resolve to this same object
     * 
     * @return string The name or ID
     */
    abstract public function getFullyQualifiedName();

    /**
     * Get the type of this resolved object
     * 
     * @return ?string The type ID
     */
    abstract public function getType();

    /**
     * Read the full contents of the resolved object as a string
     * 
     * @return string
     */
    public function getContentString(){
        return \file_get_contents($this->getOpenPath());
    }

    /**
     * Read the contents of the resolved object as a read-only stream resource
     * 
     * @return resource
     */
    public function getContentStream(){
        $path = $this->getOpenPath();
        $value = \fopen($path, 'r');
        if ($value === false){
            throw new TemplateNotFound($this->getFullyQualifiedName(), [$path], "Could not open $path: ");
        }
        return $value;
    }

    /**
     * Run the resolved object as PHP and return the result
     * 
     * The PHP code will be executed in a unique scope that will not have access to any variables
     * other than those passed as an argument to this function call. However, it it not sandboxed,
     * so still has the full privilages of the main process. Do not use this function to import
     * untrusted code.
     * 
     * @param array $params An associative array of variables which will be set in the scope of the
     * imported file.
     * @param ?object $context If provided, used as `$this` when executing the file
     * @return mixed The return value of the imported PHP file
     */
    public function importAsPhp($params=[], $context=null){
        $path = $this->getOpenPath();
        $closure = function() { // Wrap in function call to ensure "pure" scope
            \extract(\func_get_arg(1));
            return require(\func_get_arg(0));
        };
        if (is_null($context)) $closure = $closure->bindTo(null, 'static');
        else $closure = $closure->bindTo($context, $context);
        return $closure($path, $params);
    }
}

