<?php

declare(strict_types=1);

namespace DMJohnson\Contemplate\Tests\Template;

use DMJohnson\Contemplate\Engine;
use DMJohnson\Contemplate\Template\Name;
use DMJohnson\Contemplate\Template\Resolvable;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ResolvableTest extends TestCase
{
    private $resolvable;

    protected function setUp(): void
    {
        vfsStream::setup('templates');

        $engine = new Engine(vfsStream::url('templates'));
        $engine->registerFunction('uppercase', 'strtoupper');
        $engine->setFileExtension('tpl.php', Resolvable::TYPE_TEMPLATE);
        $engine->setFileExtension('get.php', Resolvable::TYPE_CONTROLLER_HTTP_GET);
        $engine->setFileExtension('post.php', Resolvable::TYPE_CONTROLLER_HTTP_POST);

        $this->resolvable = new Resolvable($engine, 'resolvable');
    }

    public function testCanCreateInstance()
    {
        $this->assertInstanceOf(Resolvable::class, $this->resolvable);
    }

    public function testGetEngine()
    {
        $this->assertInstanceOf(Engine::class, $this->resolvable->getEngine());
    }

    public function testGetName()
    {
        $this->assertInstanceOf(Name::class, $this->resolvable->getName());
        $this->assertSame('resolvable', $this->resolvable->getName()->getName());
    }

    public function testCanCallFunction()
    {
        $this->assertSame('JONATHAN', $this->resolvable->uppercase("jonathan"));
    }

    public function testExists()
    {
        vfsStream::create(
            array(
                'resolvable.php' => '',
            )
        );

        $this->assertTrue($this->resolvable->exists());
    }

    public function testDoesNotExist()
    {
        $this->assertFalse($this->resolvable->exists());
    }

    public function testGetPath()
    {
        $this->assertSame('vfs://templates/resolvable.php', $this->resolvable->path());
    }

    public function testImport()
    {
        vfsStream::create(
            array(
                'resolvable.php' => '<?php return "Hello World";',
            )
        );

        $this->assertSame('Hello World', $this->resolvable->import());
    }

    public function testImportWithParameters()
    {
        vfsStream::create(
            array(
                'resolvable.php' => '<?php return $string;',
            )
        );

        $this->assertSame('Hello World', $this->resolvable->import(['string'=>'Hello World']));
    }

    public function testImportDoesNotExist()
    {
        // The template "resolvable" could not be found at "vfs://templates/resolvable.php".
        $this->expectException(\LogicException::class);
        var_dump($this->resolvable->import());
    }

    public function testImportException()
    {
        // error
        $this->expectException('Exception');
        vfsStream::create(
            array(
                'resolvable.php' => '<?php throw new Exception("error"); ?>',
            )
        );
        var_dump($this->resolvable->import());
    }

    public function testImportDoesNotLeakVariables()
    {
        vfsStream::create(
            array(
                'resolvable.php' => '<?php return get_defined_vars();',
            )
        );

        $this->assertSame([], $this->resolvable->import());
    }

    public function testResolveAssociated()
    {
        $other = $this->resolvable->resolveAssociated(Resolvable::TYPE_CONTROLLER_HTTP_GET);
        $this->assertSame('vfs://templates/resolvable.get.php', $other->path());
    }

    public function testMakeAssociatedTemplate()
    {
        vfsStream::create(
            array(
                'resolvable.tpl.php' => '',
            )
        );

        $this->assertInstanceOf('DMJohnson\Contemplate\Template\Template', $this->resolvable->makeAssociated());
    }

    public function testMakeAssociatedTemplateWithData()
    {
        vfsStream::create(
            array(
                'resolvable.tpl.php' => '',
            )
        );

        $template = $this->resolvable->makeAssociated(array('name' => 'Jonathan'));
        $this->assertInstanceOf('DMJohnson\Contemplate\Template\Template', $template);
        $this->assertSame(array('name' => 'Jonathan'), $template->data());
    }

    public function testPathAssociated()
    {
        $this->assertSame('vfs://templates/resolvable.get.php', $this->resolvable->pathAssociated(Resolvable::TYPE_CONTROLLER_HTTP_GET));
    }

    public function testExistsAssociated()
    {
        vfsStream::create(
            array(
                'resolvable.get.php' => '',
            )
        );

        $this->assertTrue($this->resolvable->existsAssociated(Resolvable::TYPE_CONTROLLER_HTTP_GET));
    }

    public function testDoesNotExistAssociated()
    {
        $this->assertFalse($this->resolvable->existsAssociated(Resolvable::TYPE_CONTROLLER_HTTP_GET));
    }

    public function testRenderAssociatedTemplate()
    {
        vfsStream::create(
            array(
                'resolvable.tpl.php' => 'Hello!',
            )
        );

        $this->assertSame('Hello!', $this->resolvable->renderAssociated());
    }

    public function testImportAssociated()
    {
        vfsStream::create(
            array(
                'resolvable.get.php' => '<?php return "Hello World";',
            )
        );

        $this->assertSame('Hello World', $this->resolvable->importAssociated([], Resolvable::TYPE_CONTROLLER_HTTP_GET));
    }
}
