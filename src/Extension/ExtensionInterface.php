<?php

namespace DMJohnson\Contemplate\Extension;

use DMJohnson\Contemplate\Engine;

/**
 * A common interface for extensions.
 */
interface ExtensionInterface
{
    public function register(Engine $engine);
}
