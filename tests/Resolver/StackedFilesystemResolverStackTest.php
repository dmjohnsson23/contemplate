<?php

declare(strict_types=1);

namespace DMJohnson\Contemplate\Tests\Resolver;

use DMJohnson\Contemplate\Resolver\StackedFilesystemResolver;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class StackedFilesystemResolverStackTest extends TestCase
{
    private $resolver;

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

        $this->resolver = new StackedFilesystemResolver(
            [
                'A' => vfsStream::url('templates/a'),
                'B' => vfsStream::url('templates/b'),
                'C' => vfsStream::url('templates/c'),
            ],
            ['' => 'php']
        );
    }

    public function testUnpredicated()
    {
        $resolved = $this->resolver->resolve('everywhere');
        $this->assertSame('vfs://templates/a/everywhere.php', $resolved->getOpenPath());
    }
    
    public function testPredicatedExact()
    {
        $resolved = $this->resolver->resolve('B::everywhere');
        $this->assertSame('vfs://templates/b/everywhere.php', $resolved->getOpenPath());
    }

    public function testPredicatedParent()
    {
        $resolved = $this->resolver->resolve('B::not_in_b');
        $this->assertSame('vfs://templates/c/not_in_b.php', $resolved->getOpenPath());
    }


}
