<?php

declare(strict_types=1);

namespace DMJohnson\Contemplate\Tests\Resolver;

use DMJohnson\Contemplate\Resolver\StackedFilesystemResolver;
use DMJohnson\Contemplate\Template\Resolvable;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class StackedFilesystemResolverTypeTest extends TestCase
{
    private $resolver;

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
        
        $this->resolver = new StackedFilesystemResolver(
            [
                'A' => vfsStream::url('templates/a'),
                'B' => vfsStream::url('templates/b'),
            ],
            [
                '' => 'php',
                Resolvable::TYPE_TEMPLATE => 'tpl.php',
                Resolvable::TYPE_CONTROLLER_HTTP_GET => 'get.php',
                Resolvable::TYPE_CONTROLLER_HTTP_POST => 'post.php',
            ]
        );
    }

    public function testGetFromUntypedName()
    {
        $resolved = $this->resolver->resolve('untyped');
        $this->assertSame('vfs://templates/a/untyped.php', $resolved->getOpenPath());
    }

    public function testGetFromTypedName()
    {
        $resolved = $this->resolver->resolve('thing1', Resolvable::TYPE_TEMPLATE);
        $this->assertSame('vfs://templates/a/thing1.tpl.php', $resolved->getOpenPath());
    }


}
