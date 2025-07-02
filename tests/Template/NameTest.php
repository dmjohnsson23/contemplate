<?php

declare(strict_types=1);

namespace DMJohnson\Contemplate\Tests\Template;

use DMJohnson\Contemplate\Engine;
use DMJohnson\Contemplate\Resolver\StackedFilesystemResolver;
use DMJohnson\Contemplate\Template\Name;
use DMJohnson\Contemplate\Template\Resolvable;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    private $engine;

    protected function setUp(): void
    {
        vfsStream::setup('templates');
        vfsStream::create(
            array(
                'template.php' => '',
                'folder' => array(
                    'template.php' => '',
                ),
                'fallbacks' => array(
                    'fallback.php' => '',
                )
            )
        );

        $this->engine = new Engine(new StackedFilesystemResolver(
            [
                '' => vfsStream::url('templates'),
                'folder' => vfsStream::url('templates/folder'),
                'fallbacks' => vfsStream::url('templates/fallbacks'),
            ],
            ['' => 'php']
        ));
    }

    public function testCanCreateInstance()
    {
        $this->assertInstanceOf('DMJohnson\Contemplate\Template\Name', new Name($this->engine, 'template'));
    }

    public function testGetEngine()
    {
        $name = new Name($this->engine, 'template');

        $this->assertInstanceOf('DMJohnson\Contemplate\Engine', $name->getEngine());
    }

    public function testGetName()
    {
        $name = new Name($this->engine, 'template');

        $this->assertSame('template', $name->getName());
    }

    public function testGetPath()
    {
        $name = new Name($this->engine, 'template');

        $this->assertSame('vfs://templates/template.php', $name->getPath());
    }

    public function testGetPathWithFolder()
    {
        $name = new Name($this->engine, 'folder::template');

        $this->assertSame('vfs://templates/folder/template.php', $name->getPath());
    }

    public function testGetPathWithFolderFallback()
    {
        $name = new Name($this->engine, 'folder::fallback');

        $this->assertSame('vfs://templates/fallbacks/fallback.php', $name->getPath());
    }

    public function testTemplateExists()
    {
        $name = new Name($this->engine, 'template');

        $this->assertTrue($name->doesPathExist());
    }

    public function testTemplateDoesNotExist()
    {
        $name = new Name($this->engine, 'missing');

        $this->assertFalse($name->doesPathExist());
    }

    public function testParse()
    {
        $name = new Name($this->engine, 'template');

        $this->assertSame('template', $name->getName());
    }

    public function testParseWithEmptyTemplateName()
    {
        // The template name cannot be empty.
        $this->expectException(\LogicException::class);

        $name = new Name($this->engine, '');
    }

    public function testParseWithFolder()
    {
        $name = new Name($this->engine, 'folder::template');

        $this->assertSame('folder::template', $name->getName());
    }
}
