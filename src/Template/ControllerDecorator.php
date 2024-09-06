<?php

namespace DMJohnson\Contemplate\Template;

use Attribute;
use Closure;
use ReflectionAttribute;
use ReflectionFunction;
use ReflectionObject;

/**
 * An attribute used to add additional functionality to a controller.
 * 
 * Extend this class and overwrite the `__invoke` method.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION | Attribute::IS_REPEATABLE)]
abstract class ControllerDecorator{
    /**
     * Called to wrap the controller
     * 
     * @param object|callable The function or class being decorated
     * @param callable $next A callable that you should call to invoke the next decorator in the 
     * chain or, if this is the last decorator, the target controller itself. You should pass
     * `$args` or a modified version of `$args` to this callable when you call it.
     * @param array $args The arguments being passed to the controller, either from the main 
     * caller or the previous decorator in the chain.
     * @return mixed The return value. Usually this will be the value returned by `$next($args)`, 
     * but this is not required.
     */
    public function __invoke($target, $next, $args){
        return $next($args);
    }

    /**
     * Execute a function or callable class along with all its decorators
     */
    public static function callDecorated($target, $args){
        if (is_object($target) && !$target instanceof Closure){
            $reflectionObject = new ReflectionObject($target);
            $attributes = $reflectionObject->getAttributes(ControllerDecorator::class, ReflectionAttribute::IS_INSTANCEOF);
        }
        elseif ((is_string($target) && is_callable($target)) || $target instanceof Closure){
            $reflectionFunction = new ReflectionFunction($target);
            $attributes = $reflectionFunction->getAttributes(ControllerDecorator::class, ReflectionAttribute::IS_INSTANCEOF);
        }
        else{
            $attributes = [];
        }
        $next = function(array $args) use ($target){return $target(...$args);};
        foreach (array_reverse($attributes) as $attr){
            $decorator = $attr->newInstance();
            $next = function(array $args) use ($decorator, $target, $next){
                return $decorator($target, $next, $args);
            };
        }
        return $next($args);
    }
}