<?php
/**
 * Tests for the main Game application entry point.
 */

namespace Beporter\Tictactoe\Tests;

use Beporter\Tictactoe\Boards\Board;
use Beporter\Tictactoe\Boards\ReadOnlyBoard;
use Beporter\Tictactoe\Game;
use Beporter\Tictactoe\Players\Player;
use Beporter\Tictactoe\Tests\ReflectionHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\Output;

/**
 * \Beporter\Tictactoe\Tests\GameTest
 *
 * @coversDefaultClass \Beporter\Tictactoe\Game
 */
class GameTest extends TestCase
{
    use ReflectionHelperTrait;

    /**
     * Perform set up tasks before each test method.
     */
    public function setUp()
    {
        $this->Output = $this->getMockBuilder(Output::class)
            ->getMock();
        $this->P1 = $this->getMockForAbstractClass(Player::class, ['X']);
        $this->P2 = $this->getMockForAbstractClass(Player::class, ['O']);
        $this->Game = new Game($this->P1, $this->P2);
        $this->Board = $this->getProperty($this->Game, 'board');
    }

    /**
     * Perform tear down tasks after each test method.
     */
    public function tearDown()
    {
        unset($this->Board);
        unset($this->Game);
        unset($this->P2);
        unset($this->P1);
        unset($this->Output);
    }

    /**
     * Test ::construct().
     *
     * @covers ::__construct
     * @covers ::addPlayer
     * @covers ::setBoard
     */
    public function testConstruct()
    {
        $this->assertInstanceOf(
            ReadOnlyBoard::class,
            $this->Game->getBoard(),
            'A new Board should be initialized.'
        );
        $this->assertEquals(
            $this->P1,
            $this->invokeMethod($this->Game, 'nextPlayer'),
            'Player 1 should be the next player to play.'
        );
    }

    /**
     * Test ::run() using a more "integration" style. We engage a lot of
     * first-party helper methods and external Board methods; mocking only
     * the Player moves.
     *
     * @covers ::run
     * @covers ::gameOver
     * @covers ::printTurn
     * @covers ::play
     * @covers ::printMove
     * @covers ::nextPlayer
     * @covers ::advancePlayerIndex
     */
    public function testRun()
    {
        $this->P1->expects($this->atLeastOnce())
            ->method('getBestMove')
            ->with($this->Game)
            ->will($this->onConsecutiveCalls(0, 1, 2, 7)); // Interleaved with P2 moves.
        $this->P2->expects($this->atLeastOnce())
            ->method('getBestMove')
            ->with($this->Game)
            ->will($this->onConsecutiveCalls(3, 4, 5, 6)); // Interleaved with P1 moves.

        $winner = $this->Game->run($this->Output);

        $this->assertEquals(
            'X',
            $winner,
            'The winning player should be X.'
        );
    }

    /**
     * Test ::getOpponent().
     */
    public function testGetOpponent()
    {
        $this->assertSame(
            $this->P1,
            $this->invokeMethod($this->Game, 'nextPlayer'),
            'Baseline: The next player should be P1.'
        );

        $this->assertSame(
            $this->P2,
            $this->Game->getOpponent(),
            'The opponent to P1 should be P2.'
        );
    }

    /**
     * Specifically test ::printTurn(). There should at least be a line
     * containing a prompt for next Player's mark present.
     *
     * @covers ::printTurn
     * @covers ::printBoard
     */
    public function testPrintTurn()
    {
        $this->Output->expects($this->once())
            ->method('writeln')
            ->with($this->callback(function ($msg) {
                $msg = implode(PHP_EOL, $msg);
                return (strpos($msg, "X's turn") !== false);
            }));

        $this->assertNull(
            $this->invokeMethod(
                $this->Game,
                'printTurn',
                [$this->Output]
            ),
            'There should be no direct return value.'
        );
    }

    /**
     * Test ::gameOver() and its associated helper methods [::markWon(),
     * ::tieGame() and ::allSame()] separately from ::run().
     *
     * @param array $board A pre-set Board with which to seed the Game.
     * @param bool $expected The expected ::gameOver() return value.
     * @param string $msg Optional PHPUnit assertion failure message.
     * @dataProvider provideGameOverArgs
     * @covers ::gameOver
     * @covers ::markWon
     * @covers ::allSame
     */
    public function testGameOver($board, $expected, $msg = '')
    {
        $this->setProperty(
            $this->Board,
            'board',
            $board
        );

        $this->assertEquals(
            $expected,
            $this->Game->gameOver(),
            $msg
        );
    }

    /**
     * Data provider for ::testGameOver().
     *
     * @return array Sets of [board, expected, msg].
     */
    public function provideGameOverArgs()
    {
        return [
            [
                [
                    'O', 'X', 'O',
                    'X', 'O', 'X',
                    'X', 'O', 'X',
                ],
                true, // game over, tie
                '::gameOver() should return true in the event of a tie.',
            ],

            [
                [
                    'X', 'X', 'X',
                    'O', null, null,
                    'O', 'O', null,
                ],
                'X', // game over, X wins
                '::gameOver() should return the winning mark.',
            ],

            [
                [
                    'X', 'O', 'X',
                    'O', 'X', 'O',
                    'O', 'O', 'X',
                ],
                'X', // game over, X wins
                '::gameOver() should return the winning mark on a full board.',
            ],

            [
                [
                    'X', null, 'X',
                    'O', null, null,
                    'O', 'O', 'X',
                ],
                false, // game is on-going
                '::gameOver() should return false when there is no winner and the board is not full.',
            ],
        ];
    }

    /**
     * Test ::attempt() and ::undo() separately since they represent the
     * implementation of a specific Interface.
     */
    public function testAttemptAndUndo()
    {
        // Set up a nearly-won board.
        $this->setProperty(
            $this->Board,
            'board',
            [
                'X', null, 'X',
                'O', null, null,
                'O', 'O', null,
            ]
        );

        $this->assertFalse(
            $this->Game->gameOver(),
            'The Game should not yet be over.'
        );

        $this->Game->attempt(4, 'O'); // O tries playing at index=4.
        $this->Game->attempt(1, 'X'); // Make a second change for X at index=1.

        $this->assertEquals(
            'X',
            $this->Game->gameOver(),
            'X should now be indicated as the potential winner.'
        );

        $this->Game->undo(1); // Reset the single ::attempt(1, X).

        $this->assertNull(
            $this->Game->getBoard()[1],
            'Only the single move at [1] should be reset.'
        );
        $this->assertEquals(
            'O',
            $this->Game->getBoard()[4],
            'O\'s move at [4] should still be in place.'
        );

        $this->Game->undo(); // Reset all ::attempt()s.

        $this->assertFalse(
            $this->Game->gameOver(),
            'The Game should once again no longer be over.'
        );
        $this->assertNull(
            $this->Game->getBoard()[1],
            'The move at [1] should be reset.'
        );
        $this->assertNull(
            $this->Game->getBoard()[4],
            'The move at [4] should be reset.'
        );
    }
}
