<?php
/**
 * Tests for the CpuPlayer class.
 */

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

namespace Beporter\Tictactoe\Tests\Players;

use Beporter\Tictactoe\Boards\Board;
use Beporter\Tictactoe\Game;
use Beporter\Tictactoe\Interfaces\TryMovesInterface;
use Beporter\Tictactoe\Players\CpuPlayer;
use Beporter\Tictactoe\Tests\ReflectionHelperTrait;
use PHPUnit\Framework\TestCase;

/**
 * \Beporter\Tictactoe\Tests\Players\TestBigGame
 *
 * Extend the Game class to override the board dimension for testing.
 */
class TestBigGame extends Game
{
    const DIMENSION = 5;
}

/**
 * \Beporter\Tictactoe\Tests\Players\CpuPlayerTest
 */
class CpuPlayerTest extends TestCase
{
    use ReflectionHelperTrait;

    /**
     * Perform set up tasks before each test method.
     */
    public function setUp()
    {
        $this->Board = new Board();
        // Set up the board for testing.
        $this->Board[0] = 'O';
        $this->Board[4] = 'X';
        $this->Board[5] = 'X';
        $this->Board[8] = 'O';

        /*
        Visually:
         O |   |
        ---+---+---
           | X | X
        ---+---+---
           |   | O
        */

        $this->P1 = (new CpuPlayer('X'))->setDifficulty(2);
        $this->P2 = (new CpuPlayer('O'))->setDifficulty(1);
        $this->Game = new Game($this->P1, $this->P2);
        $this->setProperty($this->Game, 'board', $this->Board);
    }

    /**
     * Perform tear down tasks after each test method.
     */
    public function tearDown()
    {
        unset($this->Game);
        unset($this->P2);
        unset($this->P1);
        unset($this->Board);
    }

    /**
     * Test ::setDifficulty().
     */
    public function testSetDifficultyValid()
    {
        $this->assertEquals(
            $this->P1,
            $this->P1->setDifficulty(1),
            'Setting the difficulty should return the Player instance.'
        );

        $this->assertEquals(
            1,
            $this->getProperty($this->P1, 'difficulty'),
            'The ::$difficulty property should be assigned as we expect.'
        );
    }

    /**
     * Test ::setDifficulty() with an out of range value.
     */
    public function testSetDifficultyInvalid()
    {
        $this->expectException('\OutOfRangeException');
        $this->expectExceptionMessage('Difficulty must be an integer between 1-3 inclusive');

        $this->P1->setDifficulty(5555);
    }

    /**
     * Test ::getBestMove().
     */
    public function testGetBestMove()
    {
        $mock = $this->getMockBuilder(CpuPlayer::class)
            ->setConstructorArgs(['X'])
            ->setMethods(['randomGuess'])
            ->getMock();
        $mock->expects($this->once())
            ->method('randomGuess')
            ->with($this->isInstanceOf(TryMovesInterface::class))
            ->willReturn(5555);
        $mock->setDifficulty(1);

        $this->assertEquals(
            5555,
            $mock->getBestMove($this->Game),
            'The ::getBestMove() method should call the appropriate leveled helper methods.'
        );
    }

    /**
     * Test ::getBestMove() properly throws an exception when no move is
     * produced by its helpers.
     */
    public function testGetBestMoveFailure()
    {
        $mock = $this->getMockBuilder(CpuPlayer::class)
            ->setConstructorArgs(['X'])
            ->setMethods(['moveSet'])
            ->getMock();
        $mock->expects($this->once())
            ->method('moveSet')
            ->willReturn([]);

        $this->expectException('\LogicException');
        $this->expectExceptionMessage('no possible moves');

        $mock->getBestMove($this->Game);
    }

    /**
     * Test a variety of internal strategy helper methods.
     *
     * @param string $method The name of the CpuPlayer helper method to test.
     * @param array $board The internal Board state to set.
     * @param int|false $expected The expected helper return value.
     * @param string $msg Optional PHPUnit assertion failure message.
     * @dataProvider provideTestStrategyHelpersArgs
     * @large Thanks to minimax.
     */
    public function testStrategyHelpers(string $method, array $board, $expected, string $msg = '')
    {
        $this->Game->undo(); // Make sure ::$speculativeBoard is always reset.
        $this->setProperty($this->Board, 'board', $board);

        $this->assertSame(
            $expected,
            $this->invokeMethod($this->P1, $method, [$this->Game]),
            $msg
        );
    }

    /**
     * Provide method names, board states and expected results to
     * ::testStrategyHelpers().
     *
     * @return array Sets of [methodName, [board state], expected, message].
     */
    public function provideTestStrategyHelpersArgs()
    {
        return [
            [
                'winIfPossible',
                [
                    'O', 'X', 'O',
                    'O', null, null,
                    'X', null, 'X',
                ],
                7,
                'winIfPossible() should return the third (open) space in an existing row-of-two.',
            ],

            [
                'winIfPossible',
                [
                    'X', null, null,
                    'O', null, null,
                    null, null, null,
                ],
                false,
                'winIfPossible() should return false when there are no existing rows-of-two.',
            ],

            [
                'blockIfNecessary',
                [
                    'O', null, null,
                    'X', 'O', null,
                    null, null, null,
                ],
                8,
                'blockIfNecessary() should block an opponent\'s two-in-a-row.',
            ],

            [
                'blockIfNecessary',
                [
                    'O', null, null,
                    'X', null, null,
                    null, null, null,
                ],
                false,
                'blockIfNecessary() should return false when no blocks are possible.',
            ],

            [
                'centerIfAvailable',
                [
                    null, null, null,
                    null, null, null,
                    null, null, null,
                ],
                4,
                'centerIfAvailable() should claim an available center spot.',
            ],

            [
                'centerIfAvailable',
                [
                    null, null, null,
                    null, 'X', null,
                    null, null, null,
                ],
                false,
                'centerIfAvailable() should return false for an already-claimed center spot.',
            ],

            [
                'minimax',
                [
                    null, 'X', null,
                    'O', null,'O',
                    null, 'X', null,
                ],
                4,
                'minimax() should claim a win.',
            ],

            [
                'minimax',
                [
                    'X', 'O', null,
                    'O', 'X','O',
                    'O', 'X','O',
                ],
                2,
                'minimax() should finish a cats game.',
            ],
        ];
    }

    /**
     * Test that ::minimax() invokes its recursive helper.
     */
    public function testMinimaxWrapper()
    {
        $expected = [
            'score' => 100,
            'position' => 5555,
        ];

        $mock = $this->getMockBuilder(CpuPlayer::class)
            ->setConstructorArgs(['X'])
            ->setMethods(['minimaxRecursive'])
            ->getMock();
        $mock->expects($this->once())
            ->method('minimaxRecursive')
            ->with($this->Game, 'X')
            ->willReturn($expected);

        $this->assertEquals(
            $expected['position'],
            $this->invokeMethod($mock, 'minimax', [$this->Game]),
            'The minimax strategy should invoke its recursive helper.'
        );
    }

    /**
     * Test that ::randomGuess() is (statistically) correct.
     */
    public function testRandomGuess()
    {
        $iterations = 100;
        $positions = [];
        for ($i = 0; $i < $iterations; $i++) {
            $positions[] = $position = $this->invokeMethod($this->P1, 'randomGuess', [$this->Game]);
            $this->assertTrue(
                in_array(
                    $position,
                    $this->Game->getBoard()->available()
                ),
                'randomGuess should always return a position actually playable on the board.'
            );
        }

        foreach (array_count_values($positions) as $position => $count) {
            $this->assertLessThan(
                $iterations / 2,
                $count,
                "Each position should never be used the majority of the time. (position = $position, count = $count)"
            );
        }
    }
}
