<?php
namespace DMJohnson\Contemplate\Template;

/**
 * Wrapper for controller objects.
 * 
 * The controller should be a function or other callable which can be imported via the resolver.
 */
class Controller extends Resolvable{
    /**
     * Execute the controller code and return its value
     */
    public function call(array $args = []){
        return ControllerDecorator::callDecorated($this->import(), $args);
    }

    /** Alias for call() */
    public function __invoke(...$args){
        return ControllerDecorator::callDecorated($this->import(), $args);
    }

    /** Shortcut for `$this->engine->addData()` */
    public function addData(array $data=[], $templates=null){
        return $this->engine->addData($data, $templates);
    }

    /** Add data for the template with the same name as this controller */
    public function addDataAssociated(array $data=[]){
        return $this->engine->addData($data, $this->name->getName());
    }

    /**
     * Delegate this action, or part of this action, to a different controller 
     * (e.g. a form handler for a specific form on a page)
     * 
     * @param string $name The controller to delegate to
     * @param string|null $type The controller type to use
     */
    public function delegate($name, array $params = [], $type = Resolvable::TYPE_CONTROLLER_DELEGATE){
        return $this->engine->callController($name, $type, $params);
    }

    /**
     * Delegate this action, or part of this action, to the controller with the same name, but
     * of a different type (e.g. call a GET or DELETE controller from the POST controller)
     * 
     * @param string $type The controller type to use.
     */
    public function delegateAssociated(array $params = [], $type = Resolvable::TYPE_CONTROLLER_DELEGATE){
        return $this->engine->callController($this->name->getName(), $type, $params);
    }
}