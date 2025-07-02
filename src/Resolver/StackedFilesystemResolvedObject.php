<?php
namespace DMJohnson\Contemplate\Resolver;

class StackedFilesystemResolvedObject extends ResolvedObject{
    /** 
     * @param string $tierName The name of the tier (group) that this object was resolved to 
     * @param string $localName The local name of the object within the tier (group)
     * @param ?string $type The type of resource that was resolved
     * @param string $resolvedPath The full path that the resource was resolved to
     */
    public function __construct(
        public readonly string $tierName,
        public readonly string $localName,
        public readonly ?string $type,
        public readonly string $resolvedPath,
    ){}

    public function getFullyQualifiedName(){
        return "$this->tierName::$this->localName";
    }

    public function getType(){
        return $this->type;
    }

    public function getOpenPath(){
        return $this->resolvedPath;
    }
}