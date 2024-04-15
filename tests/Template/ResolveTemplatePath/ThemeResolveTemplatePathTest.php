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

class ThemeResolveTemplatePathTest extends TestCase
{
    private $resolver;
    private $engine;

    protected function setUp(): void
    {
        vfsStream::setup('templates');
        vfsStream::create(
            array(
                'a' => array(
                    'thing1.get.php'=>'',
                    'thing1.tpl.php'=>'',
                    'untyped.php'=>'',
                ),
                'b' => array(

                ),
            )
        );

        $this->engine = new Engine(vfsStream::url('templates'));
        $this->engine->setFileExtension('tpl.php', Resolvable::TYPE_TEMPLATE);
        $this->engine->setFileExtension('get.php', Resolvable::TYPE_CONTROLLER_HTTP_GET);
        $this->engine->setFileExtension('post.php', Resolvable::TYPE_CONTROLLER_HTTP_POST);

        $this->resolver = new ThemeResolveTemplatePath(Theme::hierarchy([
            Theme::new(vfsStream::url('templates/a'), 'A'),
            Theme::new(vfsStream::url('templates/b'), 'B'),
        ]));
    }

    public function testGetFromUntypedName()
    {
        $name = new Name($this->engine, 'untyped');
        $this->assertSame('vfs://templates/a/untyped.php', ($this->resolver)($name));
    }

    public function testGetFromTypedName()
    {
        $name = new Name($this->engine, 'thing1', Resolvable::TYPE_TEMPLATE);
        $this->assertSame('vfs://templates/a/thing1.tpl.php', ($this->resolver)($name));
    }


}
