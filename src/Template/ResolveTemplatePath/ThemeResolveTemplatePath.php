<?php

namespace DMJohnson\Contemplate\Template\ResolveTemplatePath;

use DMJohnson\Contemplate\Exception\TemplateNotFound;
use DMJohnson\Contemplate\Template\Name;
use DMJohnson\Contemplate\Template\ResolveTemplatePath;
use DMJohnson\Contemplate\Template\Theme;

class ThemeResolveTemplatePath implements ResolveTemplatePath
{
    private $theme;

    public function __construct(Theme $theme) {
        $this->theme = $theme;
    }

    public function __invoke(Name $name): string {
        $searchedPaths = [];
        $state = null;
        foreach ($this->theme->listThemeHierarchy() as $theme) {
            if (!$this->checkPredicate($name, $theme, $state)){
                continue;
            }
            $path = $theme->dir() . '/' . $name->getFile();
            if (is_file($path)) {
                return $path;
            }
            $searchedPaths[] = [$theme->name(), $path];
        }

        throw new TemplateNotFound(
            $name->getName(),
            array_map(function(array $tup) {
                return $tup[1];
            }, $searchedPaths),
            sprintf('The template "%s" was not found in the following themes: %s',
                $name->getName(),
                implode(', ', array_map(function(array $tup) {
                    return implode(':', $tup);
                }, $searchedPaths))
            )
        );
    }

    /**
     * Override this to add a predicate to the search path, allowing some available themes to be 
     * excluded based on a certain condition.
     * 
     * @param Name $name The requested name
     * @param Theme $theme The theme to test the predicate on
     * @param mixed $state A by-reference value you can use to keep state between calls
     * @return bool If this theme should be searched for a template
     */
    protected function checkPredicate(Name $name, Theme $theme, mixed &$state): bool{
        return true;
    }
}
