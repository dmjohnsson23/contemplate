<?php

declare(strict_types=1);

namespace DMJohnson\Contemplate\Tests\Template;

use Attribute;
use DMJohnson\Contemplate\Template\ControllerDecorator;
use PHPUnit\Framework\TestCase;


#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION | Attribute::IS_REPEATABLE)]
class Dec1 extends ControllerDecorator
{
    public function __invoke($target, $next, $args)
    {
        $args[0][] = 'enter 1';
        $result = $next($args);
        $result[] = 'exit 1';
        return $result;
    }
}


#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION | Attribute::IS_REPEATABLE)]
class Dec2 extends ControllerDecorator
{
    public function __invoke($target, $next, $args)
    {
        $args[0][] = 'enter 2';
        $result = $next($args);
        $result[] = 'exit 2';
        return $result;
    }
}


#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION | Attribute::IS_REPEATABLE)]
class Psych extends ControllerDecorator
{
    public function __invoke($target, $next, $args)
    {
        return 'You thought we had to call the real function? Psych!';
    }
}


class ControllerDecoratorTest extends TestCase
{
    public function testCallOrder()
    {
        $stack = ControllerDecorator::callDecorated(
            #[Dec1]
            #[Dec2]
            function($stack){
                $stack[] = 'target';
                return $stack;
            },
            [['initial']]
        );
        $this->assertSame(
            ['initial', 'enter 1', 'enter 2', 'target', 'exit 2', 'exit 1'],
            $stack
        );
    }

    public function testShortCircuit()
    {
        $result = ControllerDecorator::callDecorated(
            #[Psych]
            function(){
                return 'I will never be called';
            },
            []
        );
        $this->assertSame(
            'You thought we had to call the real function? Psych!',
            $result
        );
    }

    public function testUndecoratedFunction()
    {
        $result = ControllerDecorator::callDecorated(
            function(){
                return 'I am totally normal';
            },
            []
        );
        $this->assertSame(
            'I am totally normal',
            $result
        );
    }

    // TODO this syntax doesn't work--seems like PHP doesn't allow attributes on anonymous classes?
    // public function testDecoratedClass()
    // {
    //     $stack = ControllerDecorator::callDecorated(
    //         #[Dec1]
    //         #[Dec2]
    //         new class{
    //             public function __invoke($stack){
    //                 $stack[] = 'target';
    //                 return $stack;
    //             }
    //         },
    //         [['initial']]
    //     );
    //     $this->assertSame(
    //         ['initial', 'enter 1', 'enter 2', 'target', 'exit 2', 'exit 1'],
    //         $stack
    //     );
    // }
}
