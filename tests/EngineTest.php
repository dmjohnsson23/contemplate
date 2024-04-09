<?php

namespace DMJohnson\Contemplate\Tests;

use DMJohnson\Contemplate\Engine;
use org\bovigo\vfs\vfsStream;

class EngineTest extends \PHPUnit\Framework\TestCase
{
    private $engine;

    protected function setUp(): void
    {
        vfsStream::setup('templates');

        $this->engine = new Engine(vfsStream::url('templates'));
    }

    public function testCanCreateInstance()
    {
        $this->assertInstanceOf('DMJohnson\Contemplate\Engine', $this->engine);
    }

    public function testSetDirectory()
    {
        $this->assertInstanceOf('DMJohnson\Contemplate\Engine', $this->engine->setDirectory(vfsStream::url('templates')));
        $this->assertSame(vfsStream::url('templates'), $this->engine->getDirectory());
    }

    public function testSetNullDirectory()
    {
        $this->assertInstanceOf('DMJohnson\Contemplate\Engine', $this->engine->setDirectory(null));
        $this->assertNull($this->engine->getDirectory());
    }

    public function testSetInvalidDirectory()
    {
        // The specified path "vfs://does/not/exist" does not exist.
        $this->expectException(\LogicException::class);
        $this->engine->setDirectory(vfsStream::url('does/not/exist'));
    }

    public function testGetDirectory()
    {
        $this->assertSame(vfsStream::url('templates'), $this->engine->getDirectory());
    }

    public function testSetFileExtension()
    {
        $this->assertInstanceOf('DMJohnson\Contemplate\Engine', $this->engine->setFileExtension('tpl'));
        $this->assertSame('tpl', $this->engine->getFileExtension());
    }

    public function testSetNullFileExtension()
    {
        $this->assertInstanceOf('DMJohnson\Contemplate\Engine', $this->engine->setFileExtension(null));
        $this->assertNull($this->engine->getFileExtension());
    }

    public function testGetFileExtension()
    {
        $this->assertSame('php', $this->engine->getFileExtension());
    }

    public function testAddFolder()
    {
        vfsStream::create(
            array(
                'folder' => array(
                    'template.php' => '',
                ),
            )
        );

        $this->assertInstanceOf('DMJohnson\Contemplate\Engine', $this->engine->addFolder('folder', vfsStream::url('templates/folder')));
        $this->assertSame('vfs://templates/folder', $this->engine->getFolders()->get('folder')->getPath());
    }

    public function testAddFolderWithNamespaceConflict()
    {
        // The template folder "name" is already being used.
        $this->expectException(\LogicException::class);
        $this->engine->addFolder('name', vfsStream::url('templates'));
        $this->engine->addFolder('name', vfsStream::url('templates'));
    }

    public function testAddFolderWithInvalidDirectory()
    {
        // The specified directory path "vfs://does/not/exist" does not exist.
        $this->expectException(\LogicException::class);
        $this->engine->addFolder('namespace', vfsStream::url('does/not/exist'));
    }

    public function testRemoveFolder()
    {
        vfsStream::create(
            array(
                'folder' => array(
                    'template.php' => '',
                ),
            )
        );

        $this->engine->addFolder('folder', vfsStream::url('templates/folder'));
        $this->assertTrue($this->engine->getFolders()->exists('folder'));
        $this->assertInstanceOf('DMJohnson\Contemplate\Engine', $this->engine->removeFolder('folder'));
        $this->assertFalse($this->engine->getFolders()->exists('folder'));
    }

    public function testGetFolders()
    {
        $this->assertInstanceOf('DMJohnson\Contemplate\Template\Folders', $this->engine->getFolders());
    }

    public function testAddData()
    {
        $this->engine->addData(array('name' => 'Jonathan'));
        $data = $this->engine->getData();
        $this->assertSame('Jonathan', $data['name']);
    }

    public function testAddDataWithTemplate()
    {
        $this->engine->addData(array('name' => 'Jonathan'), 'template');
        $data = $this->engine->getData('template');
        $this->assertSame('Jonathan', $data['name']);
    }

    public function testAddDataWithTemplates()
    {
        $this->engine->addData(array('name' => 'Jonathan'), array('template1', 'template2'));
        $data = $this->engine->getData('template1');
        $this->assertSame('Jonathan', $data['name']);
    }

    public function testRegisterFunction()
    {
        vfsStream::create(
            array(
                'template.php' => '<?=$this->uppercase($name)?>',
            )
        );

        $this->engine->registerFunction('uppercase', 'strtoupper');
        $this->assertInstanceOf('DMJohnson\Contemplate\Template\Func', $this->engine->getFunction('uppercase'));
        $this->assertSame('strtoupper', $this->engine->getFunction('uppercase')->getCallback());
    }

    public function testDropFunction()
    {
        $this->engine->registerFunction('uppercase', 'strtoupper');
        $this->assertTrue($this->engine->doesFunctionExist('uppercase'));
        $this->engine->dropFunction('uppercase');
        $this->assertFalse($this->engine->doesFunctionExist('uppercase'));
    }

    public function testDropInvalidFunction()
    {
        // The template function "some_function_that_does_not_exist" was not found.
        $this->expectException(\LogicException::class);
        $this->engine->dropFunction('some_function_that_does_not_exist');
    }

    public function testGetFunction()
    {
        $this->engine->registerFunction('uppercase', 'strtoupper');
        $function = $this->engine->getFunction('uppercase');

        $this->assertInstanceOf('DMJohnson\Contemplate\Template\Func', $function);
        $this->assertSame('uppercase', $function->getName());
        $this->assertSame('strtoupper', $function->getCallback());
    }

    public function testGetInvalidFunction()
    {
        // The template function "some_function_that_does_not_exist" was not found.
        $this->expectException(\LogicException::class);
        $this->engine->getFunction('some_function_that_does_not_exist');
    }

    public function testDoesFunctionExist()
    {
        $this->engine->registerFunction('uppercase', 'strtoupper');
        $this->assertTrue($this->engine->doesFunctionExist('uppercase'));
    }

    public function testDoesFunctionNotExist()
    {
        $this->assertFalse($this->engine->doesFunctionExist('some_function_that_does_not_exist'));
    }

    public function testLoadExtension()
    {
        $this->assertFalse($this->engine->doesFunctionExist('uri'));
        $this->assertInstanceOf('DMJohnson\Contemplate\Engine', $this->engine->loadExtension(new \DMJohnson\Contemplate\Extension\URI('')));
        $this->assertTrue($this->engine->doesFunctionExist('uri'));
    }

    public function testLoadExtensions()
    {
        $this->assertFalse($this->engine->doesFunctionExist('uri'));
        $this->assertFalse($this->engine->doesFunctionExist('asset'));
        $this->assertInstanceOf(
            'DMJohnson\Contemplate\Engine',
            $this->engine->loadExtensions(
                array(
                    new \DMJohnson\Contemplate\Extension\URI(''),
                    new \DMJohnson\Contemplate\Extension\Asset('public'),
                )
            )
        );
        $this->assertTrue($this->engine->doesFunctionExist('uri'));
        $this->assertTrue($this->engine->doesFunctionExist('asset'));
    }

    public function testGetTemplatePath()
    {
        $this->assertSame('vfs://templates/template.php', $this->engine->path('template'));
    }

    public function testTemplateExists()
    {
        $this->assertFalse($this->engine->exists('template'));

        vfsStream::create(
            array(
                'template.php' => '',
            )
        );

        $this->assertTrue($this->engine->exists('template'));
    }

    public function testMakeTemplate()
    {
        vfsStream::create(
            array(
                'template.php' => '',
            )
        );

        $this->assertInstanceOf('DMJohnson\Contemplate\Template\Template', $this->engine->make('template'));
    }

    public function testMakeTemplateWithData()
    {
        vfsStream::create(
            array(
                'template.php' => '',
            )
        );

        $template = $this->engine->make('template', array('name' => 'Jonathan'));
        $this->assertInstanceOf('DMJohnson\Contemplate\Template\Template', $template);
        $this->assertSame(array('name' => 'Jonathan'), $template->data());
    }

    public function testRenderTemplate()
    {
        vfsStream::create(
            array(
                'template.php' => 'Hello!',
            )
        );

        $this->assertSame('Hello!', $this->engine->render('template'));
    }
}
