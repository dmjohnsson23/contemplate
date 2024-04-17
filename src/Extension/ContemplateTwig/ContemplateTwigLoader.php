<?php
namespace DMJohnson\Contemplate\Extension\ContemplateTwig;

use \DMJohnson\Contemplate\Engine;
use \DMJohnson\Contemplate\Exception\TemplateNotFound;
use \Twig\Error\LoaderError;
use \Twig\Source;
use \Twig\Loader\LoaderInterface;

class ContemplateTwigLoader implements LoaderInterface
{
    protected $engine;

    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
    }

    public function getSourceContext(string $name): Source
    {
        try {
            $path = $this->engine->path($name, ContemplateTwig::RESOLVABLE_TYPE_TWIG_TEMPLATE);
        }
        catch (TemplateNotFound $e) {
            throw new LoaderError($e->getMessage());
        }

        return new Source(file_get_contents($path), $name, $path);
    }

    public function exists(string $name)
    {
        return $this->engine->exists($name, ContemplateTwig::RESOLVABLE_TYPE_TWIG_TEMPLATE);
    }

    public function getCacheKey(string $name): string
    {
        return $name;
    }

    public function isFresh(string $name, int $time): bool
    {
        try {
            $path = $this->engine->path($name, ContemplateTwig::RESOLVABLE_TYPE_TWIG_TEMPLATE);
        }
        catch (TemplateNotFound $e) {
            throw new LoaderError($e->getMessage());
        }

        return filemtime($path) < $time;
    }
}