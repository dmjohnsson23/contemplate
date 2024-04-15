<?php

declare(strict_types=1);

namespace DMJohnson\Contemplate\Tests\Template;

use DMJohnson\Contemplate\Engine;
use DMJohnson\Contemplate\Template\Controller;
use DMJohnson\Contemplate\Template\Resolvable;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        vfsStream::setup('templates');

        $engine = new Engine(vfsStream::url('templates'));
        $engine->setFileExtension('delegate.php', Resolvable::TYPE_CONTROLLER_DELEGATE);
        $engine->setFileExtension('get.php', Resolvable::TYPE_CONTROLLER_HTTP_GET);
        $engine->setFileExtension('post.php', Resolvable::TYPE_CONTROLLER_HTTP_POST);

        $this->controller = new Controller($engine, 'controller');
    }

    public function testCanCreateInstance()
    {
        $this->assertInstanceOf(Controller::class, $this->controller);
    }

    public function testCall()
    {
        vfsStream::create(
            array(
                'controller.php' => '<?php return function(){return "Hello World";};',
            )
        );

        $this->assertSame('Hello World', $this->controller->call());
    }

    public function testCallWithParameters()
    {
        vfsStream::create(
            array(
                'controller.php' => '<?php return function($string){return $string;};',
            )
        );

        $this->assertSame('Hello World', $this->controller->call(['Hello World']));
    }

    public function testCallWithParametersViaInvoke()
    {
        vfsStream::create(
            array(
                'controller.php' => '<?php return function($string){return $string;};',
            )
        );
        
        $this->assertSame('Hello World', ($this->controller)('Hello World'));
    }

    public function testCallDoesNotExist()
    {
        // The template "controller" could not be found at "vfs://templates/controller.php".
        $this->expectException(\LogicException::class);
        var_dump($this->controller->call());
    }

    public function testCallException()
    {
        // error
        $this->expectException('Exception');
        vfsStream::create(
            array(
                'controller.php' => '<?php return function(){throw new Exception("error");}; ?>',
            )
        );
        var_dump($this->controller->call());
    }

    public function testCallDoesNotLeakVariables()
    {
        vfsStream::create(
            array(
                'controller.php' => '<?php $defined = get_defined_vars(); return function() use ($defined){return $defined;};',
            )
        );

        $this->assertSame([], $this->controller->call());
    }

    public function testAddData()
    {
        $this->controller->addData(array('name' => 'Jonathan'));
        $data = $this->controller->getEngine()->getData();
        $this->assertSame('Jonathan', $data['name']);
    }

    public function testAddDataAssociated()
    {
        $this->controller->addDataAssociated(array('name' => 'Jonathan'));
        $data = $this->controller->getEngine()->getData('controller');
        $this->assertSame('Jonathan', $data['name']);
    }

    public function testAddDataWithTemplate()
    {
        $this->controller->addData(array('name' => 'Jonathan'), 'template');
        $data = $this->controller->getEngine()->getData('template');
        $this->assertSame('Jonathan', $data['name']);
    }

    public function testDelegate()
    {
        vfsStream::create(
            array(
                'other.delegate.php' => '<?php return function(){return "Delegate to the delegate";};',
            )
        );

        $this->assertSame('Delegate to the delegate', $this->controller->delegate('other'));
    }

    public function testDelegateAssociated()
    {
        vfsStream::create(
            array(
                'controller.delegate.php' => '<?php return function(){return "Delegate to the delegate";};',
            )
        );

        $this->assertSame('Delegate to the delegate', $this->controller->delegateAssociated());
    }

    public function testDelegateAssociatedWithType()
    {
        vfsStream::create(
            array(
                'controller.get.php' => '<?php return function(){return "Delegate to the delegate";};',
            )
        );

        $this->assertSame('Delegate to the delegate', $this->controller->delegateAssociated([], Resolvable::TYPE_CONTROLLER_HTTP_GET));
    }

    public function testDelegateWithParams()
    {
        vfsStream::create(
            array(
                'other.delegate.php' => '<?php return function($string){return $string;};',
            )
        );

        $this->assertSame('Delegate to the delegate', $this->controller->delegate('other', ['Delegate to the delegate']));
    }

    public function testDelegateAssociatedWithParams()
    {
        vfsStream::create(
            array(
                'controller.delegate.php' => '<?php return function($string){return $string;};',
            )
        );

        $this->assertSame('Delegate to the delegate', $this->controller->delegateAssociated(['Delegate to the delegate']));
    }

}
