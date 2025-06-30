<?php

declare(strict_types=1);

namespace DMJohnson\Contemplate\Tests\Template\ResolveTemplatePath;

use DMJohnson\Contemplate\Engine;
use DMJohnson\Contemplate\Template\Controller;
use DMJohnson\Contemplate\Template\Name;
use DMJohnson\Contemplate\Template\Resolvable;
use DMJohnson\Contemplate\Template\ResolveTemplatePath\ThemeResolveTemplatePath;
use DMJohnson\Contemplate\Template\Theme;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ExtensibleThemeResolveTemplatePathTest extends TestCase
{
    private $resolver;
    private $engine;

    protected function setUp(): void
    {
        vfsStream::setup('templates');
        vfsStream::create(
            array(
                'a' => array(
                    'everywhere.php'=>'',
                    'not_in_b.php'=>'',
                ),
                'b' => array(
                    'everywhere.php'=>'',
                ),
                'c' => array(
                    'everywhere.php'=>'',
                    'not_in_b.php'=>'',
                ),
            )
        );

        $this->engine = new Engine(vfsStream::url('templates'));

        $this->resolver = new ThemeResolveTemplatePath(Theme::hierarchy([
            Theme::new(vfsStream::url('templates/a'), 'A'),
            Theme::new(vfsStream::url('templates/b'), 'B'),
            Theme::new(vfsStream::url('templates/c'), 'C'),
        ]));
    }

    public function testUnpredicated()
    {
        $name = new Name($this->engine, 'everywhere');
        $this->assertSame('vfs://templates/a/everywhere.php', ($this->resolver)($name));
    }
    
    public function testPredicatedExact()
    {
        $name = new Name($this->engine, 'B::everywhere');
        $this->assertSame('vfs://templates/b/everywhere.php', ($this->resolver)($name));
    }

    public function testPredicatedParent()
    {
        $name = new Name($this->engine, 'B::not_in_b');
        $this->assertSame('vfs://templates/c/not_in_b.php', ($this->resolver)($name));
    }


}
