<?php
/**
 * Tests for the TicTacToeApplication class.
 */

namespace Beporter\Tictactoe\Tests;

use Beporter\Tictactoe\Tests\ReflectionHelperTrait;
use Beporter\Tictactoe\TicTacToeApplication;
use Beporter\Tictactoe\TicTacToeCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;

/**
 * \Beporter\Tictactoe\Tests\TicTacToeApplicationTest
 */
class TicTacToeApplicationTest extends TestCase
{
    use ReflectionHelperTrait;

    /**
     * Perform set up tasks before each test method.
     */
    public function setUp()
    {
        $this->TicTacToeApplication = new TicTacToeApplication();

        // There is no direct way to access the ::$app property, nor is there
        // a non-testing reason to do so. So here we "cheat" to test its value.
        $this->appProperty = $this->getReflectionProperty($this->TicTacToeApplication, 'app');
    }

    /**
     * Perform tear down tasks after each test method.
     */
    public function tearDown()
    {
        unset($this->appProp);
        unset($this->TicTacToeApplication);
    }

    /**
     * Test ::__construct(). Building a new Application should result in a
     * Symfony Application being present in the ::$app property that itself
     * has a TicTacToeCommand assigned to it.
     */
    public function testConstruct()
    {
        $commandInstance = $this->appProperty->getValue($this->TicTacToeApplication);

        $this->assertInstanceOf(
            Application::class,
            $commandInstance,
            'A new TicTacToeApplication should have a Symfony Application present in ::$app.'
        );
        $this->assertTrue(
            $commandInstance->has((new TicTacToeCommand())->getName()),
            'The embedded Symfony Application should have a TicTacToeCommand registered.'
        );
    }

    /**
     * Test ::run() to ensure it invokes ::$app->run().
     */
    public function testRun()
    {
        $commandMock = $this->getMockBuilder(\StdClass::class)
            ->disableOriginalConstructor()
            ->setMethods(['run'])
            ->getMock();
        $commandMock
            ->expects($this->once())
            ->method('run')
            ->willReturn(42);
        $this->appProperty->setValue($this->TicTacToeApplication, $commandMock);

        $this->assertEquals(
            42,
            $this->TicTacToeApplication->run(),
            'Calling ::run() should invoke the Symfony command\'s own ::run() method.'
        );
    }
}
