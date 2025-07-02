<?php
namespace DMJohnson\Contemplate\Resolver;

use DMJohnson\Contemplate\Template\Name;

abstract class Resolver{
    abstract function resolve(string $name, ?string $type = null): ResolvedObject;

    function resolveName(Name $name): ResolvedObject{
        return $this->resolve($name->getName(), $name->getType());
    }
}