<?php

namespace DMJohnson\Contemplate\Template\ResolveTemplatePath;

use DMJohnson\Contemplate\Exception\TemplateNotFound;
use DMJohnson\Contemplate\Template\Name;
use DMJohnson\Contemplate\Template\ResolveTemplatePath;
use DMJohnson\Contemplate\Template\Theme;

final class ThemeResolveTemplatePath implements ResolveTemplatePath
{
    private $theme;

    public function __construct(Theme $theme) {
        $this->theme = $theme;
    }

    public function __invoke(Name $name): string {
        $searchedPaths = [];
        foreach ($this->theme->listThemeHierarchy() as $theme) {
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
}
