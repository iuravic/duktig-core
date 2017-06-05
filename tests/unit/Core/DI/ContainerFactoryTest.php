<?php
namespace Duktig\Core\DI;

use PHPUnit\Framework\TestCase;

class ContainerFactoryTest extends TestCase
{
    public function testThrowsExceptionIfServiceIsNotAClosureWithContainerAsParam()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Configuration 'services' definition must"
            ." be a closure which takes the container as its parameter"
            ." and also returns it after configuring it.");
        $containerFactory = new ContainerFactory();
        $containerFactory->registerServices([function(){}]);
    }
    
    public function testInitObjectWithCheckThrowsExceptionIfClassNotInstanceofExpected()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Provided service class missmatch: given "
            ."Duktig\Test\Helpers\Core\AppNull, expected Duktig\Core\DI\ContainerInterface.");
        
        $containerFactory = new ContainerFactory();
        $containerFactory->make(['serviceContainer' => \Duktig\Test\Helpers\Core\AppNull::class]);
    }
}