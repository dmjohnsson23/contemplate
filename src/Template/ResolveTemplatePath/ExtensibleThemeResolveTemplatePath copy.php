<?php

namespace DMJohnson\Contemplate\Template\ResolveTemplatePath;

use DMJohnson\Contemplate\Template\Name;
use DMJohnson\Contemplate\Template\Theme;

/**
 * Extension to the theme resolver which allows you to start the search at a specific location 
 * down the hierarchy. This makes it possible for one theme to easilly extend a parent theme.
 */
final class ExtensibleThemeResolveTemplatePath extends ThemeResolveTemplatePath
{
    protected function checkPredicate(Name $name, Theme $theme, mixed &$state): bool{
        if ($state) return true;
        if ($name->getFolder() == $theme->name()){
            $state = true;
            return true;
        }
        return false;
    }
}
