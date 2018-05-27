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
     * Test that ::minimaxOrHeuristic() engages minimax when the board
     * dimension is low.
     */
    public function testMinimaxOrHeuristicLowDimension()
    {
        $mock = $this->getMockBuilder(CpuPlayer::class)
            ->setConstructorArgs(['X'])
            ->setMethods(['minimax'])
            ->getMock();
        $mock->expects($this->once())
            ->method('minimax')
            ->with($this->Game)
            ->willReturn(42);

        $this->assertEquals(
            42,
            $this->invokeMethod($mock, 'minimaxOrHeuristic', [$this->Game]),
            'The board position returned should be that of our minimax() mock.'
        );
    }

    /**
     * Test that ::minimaxOrHeuristic() returns false when the board
     * dimension is high.
     */
    public function testMinimaxOrHeuristicHighDimension()
    {
        $bigGame = $this->getMockBuilder(TestBigGame::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mock = $this->getMockBuilder(CpuPlayer::class)
            ->setConstructorArgs(['X'])
            ->setMethods(['minimax'])
            ->getMock();
        $mock->expects($this->never())
            ->method('minimax');

        $this->assertFalse(
            $this->invokeMethod($mock, 'minimaxOrHeuristic', [$bigGame]),
            'The minimaxOrHeuristic() method should return false when board dimension is large.'
        );
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
                'createFork',
                [
                    'X', null, null,
                    'O', 'O', null,
                    'X', 'O', 'X',
                ],
                2,
                'createFork() should return the location with the most possible forks.',
            ],

            [
                'createFork',
                [
                    'X', null, null,
                    'O', null, null,
                    null, null, null,
                ],
                false,
                'createFork() should return false when there are no fork options.',
            ],

            [
                'blockForkPotential',
                [
                    'O', null, null,
                    'X', 'X', null,
                    'O', 'X', 'O',
                ],
                2,
                'blockForkPotential() should return the location with the most possible opponent forks.',
            ],

            [
                'blockForkPotential',
                [
                    'X', null, null,
                    'O', null, null,
                    null, null, null,
                ],
                false,
                'blockForkPotential() should return false when there are no opponent fork options.',
            ],

            [
                'createTwoInARow',
                [
                    'X', null, null,
                    'O', null, null,
                    null, 'X', null,
                ],
                1,
                'createTwoInARow() should return the open spot with the most intersects.',
            ],

            [
                'createTwoInARow',
                [
                    null, null, null,
                    'O', null, null,
                    null, null, null,
                ],
                false,
                'createTwoInARow() should return false when there are no viable intersects.',
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
                'cornerOpening',
                [
                    null, null, null,
                    null, null, null,
                    null, null, null,
                ],
                0,
                'cornerOpening() claim a corner on an empty board.',
            ],

            [
                'cornerOpening',
                [
                    null, null, null,
                    null, 'X', null,
                    null, null, null,
                ],
                false,
                'cornerOpening() should return false for a non-empty board.',
            ],

            [
                'oppositeCorner',
                [
                    null, null, null,
                    null, null, null,
                    null, null, null,
                ],
                false,
                'oppositeCorner() should return false for an empty board.',
            ],

            [
                'oppositeCorner',
                [
                    null, null, null,
                    null, null, null,
                    'O', null, null,
                ],
                2,
                'oppositeCorner() should return an opposite corner after a corner open.',
            ],

            [
                'oppositeCorner',
                [
                    null, 'O', null,
                    null, null, null,
                    null, null, null,
                ],
                false,
                'oppositeCorner() should return false if first move was not a corner.',
            ],

            [
                'emptyCorner',
                [
                    'O', null,'O',
                    null, null, null,
                    null, null, null,
                ],
                6,
                'emptyCorner() should return the first available corner.',
            ],

            [
                'emptyCorner',
                [
                    'O', null,'X',
                    null, null, null,
                    'X', null,'O',
                ],
                false,
                'emptyCorner() should false when all corners are claimed.',
            ],

            [
                'emptySide',
                [
                    null, 'X', null,
                    'O', null,'O',
                    null, null, null,
                ],
                7,
                'emptySide() should return the first available side.',
            ],

            [
                'emptySide',
                [
                    null, 'X', null,
                    'O', null,'O',
                    null, 'X', null,
                ],
                false,
                'emptySide() should false when all sides are claimed.',
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
}
