<?php
namespace DMJohnson\Contemplate\Resolver;

use DMJohnson\Contemplate\Exception\TemplateNotFound;

/**
 * 
 */
class StackedFilesystemResolver extends Resolver{
    /**
     * @param array<string,string> $tiers A mapping of tier names to directory paths
     * @param array<string,string> $types A mapping of type names to file extensions
     */
    public function __construct(protected array $tiers, protected array $types = []){}

    public function resolve(string $name, ?string $type = null): StackedFilesystemResolvedObject{
        [$targetTierName, $localName] = $this->normalizeName($name);
        $tierOkay = is_null($targetTierName);
        $attemptedPaths = [];
        foreach ($this->tiers as $testTierName=>$searchPath){
            if (!$tierOkay){
                // If a tier name was specified, skip all tiers until we reach that one
                if ($targetTierName === $testTierName) $tierOkay = true;
                else continue;
            }
            $path = $this->constructPath($searchPath, $localName, $type);
            $attemptedPaths[] = $path;
            if (is_file($path)){
                return new StackedFilesystemResolvedObject($testTierName, $localName, $type, $path);
            }
        }
        throw new TemplateNotFound($name, $attemptedPaths, "Could not resolve '$name'; tried ".implode(', ', $attemptedPaths));
    }

    protected function normalizeName(string $name){
        $parts = explode('::', $name, 2);
        if (count($parts) === 2) return $parts;
        return [null, $parts[0]];
    }

    protected function constructPath(string $basePath, string $relativePath, ?string $type){
        $extension = isset($this->types[$type]) ? '.'.$this->types[$type] : '';
        if (!str_ends_with($basePath, '/')) $basePath .= '/';
        $relativePath = ltrim($relativePath, '/.\\');
        return $basePath.$relativePath.$extension;
    }
}