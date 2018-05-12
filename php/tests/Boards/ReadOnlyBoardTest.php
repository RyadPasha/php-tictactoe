<?php
/**
 * Tests for the ReadOnlyBoard class.
 */

namespace Beporter\Tictactoe\Tests\Boards;

use Beporter\Tictactoe\Boards\Board;
use Beporter\Tictactoe\Boards\ReadOnlyBoard;
use Beporter\Tictactoe\Tests\ReflectionHelperTrait;
use PHPUnit\Framework\TestCase;

/**
 * \Beporter\Tictactoe\Boards\ReadOnlyBoardTest
 */
class ReadOnlyBoardTest extends TestCase
{
    use ReflectionHelperTrait;

    /**
     * Perform set up tasks before each test method.
     */
    public function setUp()
    {
        $this->Board = new Board(3);

        // Set up the board for testing.
        $this->Board[2] = 'T';
        $this->Board[4] = 'E';
        $this->Board[5] = 'S';
        $this->Board[8] = 'T';

        /*
        Visually:
           |   | T
        ---+---+---
           | E | S
        ---+---+---
           |   | T
        */

        $this->ReadOnlyBoard = new ReadOnlyBoard($this->Board);
    }

    /**
     * Perform tear down tasks after each test method.
     */
    public function tearDown()
    {
        unset($this->ReadOnlyBoard);
        unset($this->Board);
    }

    /**
     * Make sure we replicate the parent constructor.
     */
    public function testConstruct()
    {
        $this->assertEquals(
            $this->getProperty($this->Board, 'dimension'),
            $this->getProperty($this->ReadOnlyBoard, 'dimension'),
            'The ReadOnlyBoard dimension should match the parent dimension.'
        );
        $this->assertEquals(
            $this->getProperty($this->Board, 'board'),
            $this->getProperty($this->ReadOnlyBoard, 'board'),
            'The ReadOnlyBoard internal array should match the parent internal array.'
        );
    }

    /**
     * Make sure assigning values fails.
     */
    public function testOffsetSet()
    {
        $this->expectException('\BadMethodCallException');
        $this->expectExceptionMessage('values can not be assigned');

        $this->ReadOnlyBoard[0] = 'should fail';
    }

    /**
     * Confirm that unsetting locations fails.
     */
    public function testUnset()
    {
        $this->expectException('\BadMethodCallException');
        $this->expectExceptionMessage('values can not be unset');

        unset($this->ReadOnlyBoard[0]);
    }
}
