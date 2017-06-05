<?php
namespace Duktig\Core;

use PHPUnit\Framework\TestCase;
use Duktig\Test\Helpers\Core\AppNull;

class AppFactoryTest extends TestCase
{
    public function testFactoryMakesClass()
    {
        $app = (new AppFactory())->make(
            __DIR__.'/../../Config/configTest.php', 
            AppNull::class
        );
        $this->assertInstanceOf(\Duktig\Test\Helpers\Core\AppNull::class, $app,
            "AppFactory did not resolve an object of the expected type");
    }
    
    public function testThrowsExceptionIfIncorrectConfigFileProvided()
    {
        $this->expectException('InvalidArgumentException');
        (new AppFactory())->make(
            'nofile', 
            AppNull::class
        );
    }
}
