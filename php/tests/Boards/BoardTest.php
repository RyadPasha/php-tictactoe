<?php
/**
 * Tests for the Board class.
 */

namespace Beporter\Tictactoe\Tests\Boards;

use Beporter\Tictactoe\Boards\Board;
use PHPUnit\Framework\TestCase;

/**
 * \Beporter\Tictactoe\Boards\BoardTest
 */
class BoardTest extends TestCase
{
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
    }

    /**
     * Perform tear down tasks after each test method.
     */
    public function tearDown()
    {
        unset($this->Board);
    }

    /**
     * Test ::__construct(). Building a new Board with a positive $dimension
     * should produce a Board with ($dimension * $dimension) spots.
     */
    public function testConstructAndCount()
    {
        $this->assertCount(
            9,
            new Board(3),
            'A new Board with dimension=3 should have 9 spots.'
        );
    }

    /**
     * Attempting to build a new Board with a negative int should throw an
     * OutOfRangeException.
     */
    public function testConstructWithNegativeDimension()
    {
        $badDimension = -3;
        $this->expectException('\OutOfRangeException');
        $this->expectExceptionMessage($badDimension);

        $trigger = new Board($badDimension);
    }

    /**
     * Setting a location to a value should succeed the first time.
     */
    public function testSetFirstAttempt()
    {
        $value = 'X';

        $this->Board[0] = $value;
        $this->assertEquals(
            $value,
            $this->Board[0],
            'The value should have been properly assigned.'
        );
    }

    /**
     * Setting a location to a value should fail after the first time.
     */
    public function testSetSecondAttempt()
    {
        $this->Board[0] = 'X'; // Should succeed.

        $this->expectException('\DomainException');
        $this->expectExceptionMessage('already set');

        $this->Board[0] = 'Y'; // Should trigger the Exception.
    }

    /**
     * Attempting to set an out-of-range location should always fail.
     */
    public function testSetOutOfRange()
    {
        $this->expectException('\PHPUnit\Framework\Error\Error');
        $this->expectExceptionMessage('Undefined offset');

        $this->Board[5555] = 'X';
    }

    /**
     * Confirm that locations can be unset().
     */
    public function testUnset()
    {
        $this->Board[0] = 'X';

        unset($this->Board[0]);

        $this->assertNull(
            $this->Board[0],
            'The attempt to unset the location should result in a NULL value on subsequent access.'
        );
    }

    /**
     * Verify that only expcted offsets exist.
     */
    public function testOffsetExists()
    {
        $this->assertTrue(
            isset($this->Board[0]),
            'A valid index should exist.'
        );

        $this->assertFalse(
            isset($this->Board[5555]),
            'An invalid index should not exist.'
        );
    }

    /**
     * Attempting to access an out-of-range index should produce a PHP warning.
     */
    public function testGetOutOfRange()
    {
        $this->expectException('\PHPUnit\Framework\Error\Notice');
        $this->expectExceptionMessage('Undefined offset');

        $trigger = $this->Board[5555]; // Should trigger the warning.
    }

    /**
     * Test ability to down-convert to a plain array.
     */
    public function testToArray()
    {
        $this->assertEquals(
            [
                0 => null,
                1 => null,
                2 => 'T',
                3 => null,
                4 => 'E',
                5 => 'S',
                6 => null,
                7 => null,
                8 => 'T',
            ],
            $this->Board->toArray(),
            'The vanilla array version of the Board should match our expectations.'
        );
    }

    /**
     * Make sure only non-assigned spots are returned from ::available().
     */
    public function testAvailable()
    {
        $this->assertEquals(
            [0, 1, 3, 6, 7],
            $this->Board->available(),
            'Only unassigned indices should be returned from ::available().'
        );
    }

    /**
     * Check ::row() with valid input.
     */
    public function testRowValid()
    {
        $expected = [
            0 => null,
            1 => null,
            2 => 'T',
        ];

        $this->assertEquals(
            $expected,
            $this->Board->row(0),
            'Requesting a valid ::row() should produce the expected subset array.'
        );
    }

    /**
     * Check ::row() with an invalid row number.
     */
    public function testRowInvalid()
    {
        $this->expectException('\OutOfRangeException');
        $this->expectExceptionMessage('Invalid row requested');

        $this->Board->row(5555);
    }

    /**
     * Check ::column() with valid input.
     */
    public function testColumnValid()
    {
        $expected = [
            0 => 'T',
            1 => 'S',
            2 => 'T',
        ];

        $this->assertEquals(
            $expected,
            $this->Board->column(2),
            'Requesting a valid ::column() should produce the expected subset array.'
        );
    }

    /**
     * Check ::column() with an invalid column number.
     */
    public function testColumnInvalid()
    {
        $this->expectException('\OutOfRangeException');
        $this->expectExceptionMessage('Invalid column requested');

        $this->Board->column(5555);
    }

    /**
     * Check ::diagonal().
     */
    public function testDiagonal()
    {
        $this->assertEquals(
            [
                0 => null,
                4 => 'E',
                8 => 'T',
            ],
            $this->Board->diagonal(true),
            'Requesting a ::diagonal(true) should produce the expected subset array.'
        );
        $this->assertEquals(
            [
                2 => 'T',
                4 => 'E',
                6 => null,
            ],
            $this->Board->diagonal(false),
            'Requesting a ::diagonal(false) should produce the expected subset array.'
        );
    }

    /**
     * Check ::corners().
     */
    public function testCorners()
    {
        $this->assertEquals(
            [0, 2, 6, 8],
            $this->Board->corners(),
            'There should always be exactly 4 corners.'
        );
    }
}
