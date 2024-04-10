<?php

declare(strict_types=1);

namespace DMJohnson\Contemplate\Tests\Template;

use DMJohnson\Contemplate\Template\FileExtension;
use DMJohnson\Contemplate\Template\Resolvable;
use PHPUnit\Framework\TestCase;

class FileExtensionTest extends TestCase
{
    private $fileExtension;

    protected function setUp(): void
    {
        $this->fileExtension = new FileExtension();
    }

    public function testCanCreateInstance()
    {
        $this->assertInstanceOf('DMJohnson\Contemplate\Template\FileExtension', $this->fileExtension);
    }

    public function testSetFileExtension()
    {
        $this->assertInstanceOf('DMJohnson\Contemplate\Template\FileExtension', $this->fileExtension->set('tpl'));
        $this->assertSame('tpl', $this->fileExtension->get());
    }

    public function testSetTypedFileExtensionAndDefault()
    {
        $this->assertInstanceOf('DMJohnson\Contemplate\Template\FileExtension', $this->fileExtension->set('php'));
        $this->assertInstanceOf('DMJohnson\Contemplate\Template\FileExtension', $this->fileExtension->set('tpl', Resolvable::TYPE_TEMPLATE));
        $this->assertSame('php', $this->fileExtension->get());
        $this->assertSame('tpl', $this->fileExtension->get(Resolvable::TYPE_TEMPLATE));
    }

    public function testSetNullFileExtension()
    {
        $this->assertInstanceOf('DMJohnson\Contemplate\Template\FileExtension', $this->fileExtension->set(null));
        $this->assertNull($this->fileExtension->get());
    }

    public function testSetNullTypedFileExtension()
    {
        $this->assertInstanceOf('DMJohnson\Contemplate\Template\FileExtension', $this->fileExtension->set(null, 'custom type'));
        $this->assertNull($this->fileExtension->get('custom type'));
    }

    public function testGetFileExtension()
    {
        $this->assertSame('php', $this->fileExtension->get());
    }

    public function testGetTypedFileExtensionFallbackToDefault()
    {
        $this->assertSame('php', $this->fileExtension->get('not defined'));
    }
}
