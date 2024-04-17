<?php
namespace DMJohnson\Contemplate\Extension\ContemplateTwig;

use \DMJohnson\Contemplate\Extension\ExtensionInterface;
use \DMJohnson\Contemplate\Engine;
use \Twig\Environment;

/**
 * An extension allowing Twig templates to be integrated into Contemplate.
 * 
 * "Hmm...how 'bout those twigs though?"
 * 
 *  - Contemplate will be used as a loader for Twig. 
 *  - Two template functions will be registered in Contemplate:
 *     - `renderTwig`, which is an alias for `$twig->render`
 *     - `renderTwigBlock`, which is an alias for `$twig->load(...)->renderBlock`
 *  - A global `contemplate` object will be exposed to Twig templates, which is the Contemplate `Engine`
 * 
 * This extension will not work with vanilla Plates; it relies on features unique to Contemplate.
 */
class ContemplateTwig implements ExtensionInterface
{
    const RESOLVABLE_TYPE_TWIG_TEMPLATE = '__TWIG_TEMPLATE__';

    private Environment $twig;
    private Engine $contemplate;
    
    public function __construct(private array $twigEnvironmentOptions)
    {
    }

    public function register(Engine $engine)
    {
        $this->contemplate = $engine;
        $loader = new ContemplateTwigLoader($engine);
        $this->twig = new Environment($loader, $this->twigEnvironmentOptions);
        $this->twig->addGlobal('contemplate', $engine);
        $engine->registerFunction('renderTwig', [$this, 'render']);
        $engine->registerFunction('renderTwigBlock', [$this, 'renderBlock']);
        $engine->setFileExtension('twig', ContemplateTwig::RESOLVABLE_TYPE_TWIG_TEMPLATE);
    }

    public function render(string $name, array $data = array())
    {
        return $this->twig->render($name, array_merge($this->contemplate->getData($name), $data));
    }

    public function renderBlock(string $name, string $block, array $data = array())
    {
        return $this->twig->load($name)->renderBlock($block, array_merge($this->contemplate->getData($name), $data));
    }
}