<?php
/**
 * Game class responsible for running the main game loop of requesting and
 * processing player moves, updating the board and drawing it to the console.
 */

declare(strict_types=1);

namespace Beporter\Tictactoe;

use Beporter\Tictactoe\Boards\Board;
use Beporter\Tictactoe\Boards\ReadOnlyBoard;
use Beporter\Tictactoe\Interfaces\PlayGameInterface;
use Beporter\Tictactoe\Interfaces\TryMovesInterface;
use Beporter\Tictactoe\Players\Player;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * \Beporter\Tictactoe\Game
 */
class Game implements PlayGameInterface, TryMovesInterface
{
    /**
     * Defines the length of a single side of the game board.
     *
     * @var int
     */
    const DIMENSION = 3;

    /**
     * The string to use between columns when drawing the game board.
     *
     * @var string
     */
    const BOARD_COLUMN_SEPARATOR = ' │ ';

    /**
     * The string to use between rows when drawing the game board.
     *
     * @var string
     */
    const BOARD_ROW_SEPARATOR = '─';

    /**
     * The string to use at line intersections (also between rows) when
     * drawing the game board.
     *
     * @var string
     */
    const BOARD_INTERSECT = '─┼─';

    /**
     * Stores an ordered array of Players. Players make moves in sequential
     * order. Typically there will only be two Players.
     *
     * @var array
     */
    protected $players = [];

    /**
     * Tracks the next Player to play as an index in ::$players.
     *
     * @var int
     */
    protected $nextPlayerIndex = 0;

    /**
     * The current official Board state.
     *
     * @var \Beporter\Tictactoe\Boards\Board
     */
    protected $board = null;

    /**
     * The current speculative Board state created by ::attempt() and removed
     * by ::undo(). When it is present, this property supercedes ::$board.
     * See ::board().
     *
     * @var \Beporter\Tictactoe\Boards\Board
     */
    protected $speculativeBoard = null;

    /**
     * Constructor. Set up the Board and add two Players.
     *
     * @param \Beporter\Tictactoe\Players\Player First player instance.
     * @param \Beporter\Tictactoe\Players\Player Second player instance.
     */
    public function __construct(Player $p1, Player $p2)
    {
        $this->setBoard(new Board(self::DIMENSION));
        $this->addPlayer($p1);
        $this->addPlayer($p2);
    }

    /**
     * Public getter for the ::$board (or ::$speculativeBoard) property.
     * Returns a ReadOnlyBoard so the state can't be directly modified.
     * Contrast with ::board(), which returns a reference to the Board object
     * itself.
     *
     * @return \Beporter\Tictactoe\Boards\ReadOnlyBoard A read-only instance
     *     of the current game Board.
     */
    public function getBoard(): ReadOnlyBoard
    {
        return new ReadOnlyBoard($this->board());
    }

    /**
     * \TryMovesInterface method for fetching the "opponent" Player compared
     * to $this->nextPlayer().
     *
     * @return \Beporter\Tictactoe\Players\Player The opponent Player.
     */
    public function getOpponent(): Player
    {
        $opponentIndex = ($this->nextPlayerIndex + 1) % count($this->players);

        return $this->players[$opponentIndex];
    }

    /**
     * Play the Game. Loops through ::$players repeatedly, soliciting and
     * applying moves until the game is over.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output The
     *     output interface provides access to write to the console.
     * @return true|string True in the event of a tie, otherwise the mark
     *     representing the winning Player.
     */
    public function run(OutputInterface $output)
    {
        while (!($winningMark = $this->gameOver())) {
            $this->printTurn($output);
            $move = $this->nextPlayer()->getBestMove($this);
            $this->play($move);
            $this->printMove($output, $move);
            $this->advancePlayerIndex();
        }

        return $winningMark;
    }

    /**
     * Shortcut conglomerate method for quickly determining game end state.
     * Takes unabashed (but appropriate) advantage of PHP's lack of type
     * strictness for its return value.
     *
     * @return string|bool The winning mark, if any. Boolean true when game
     *    has ended in a tie. False if game is on-going.
     */
    public function gameOver()
    {
        $marks = array_map(
            function ($player) {
                return $player->getMark();
            },
            $this->players
        );
        foreach ($marks as $mark) {
            if ($this->markWon($mark)) {
                return $mark;
            }
        }

        // Full board + no winner from above = tie. Otherwise false.
        return (count($this->board()->available()) === 0);
    }

    /**
     * Construct a crude but console-friendly string representation of the
     * board. Does not handle double-digit indicies very gracefully, but that
     * isn't a problem when `::DIMENSION = 3`.
     *
     * @return string A console-friendly representation of the Board.
     */
    public function printBoard(): string
    {
        $board = $this->getBoard()->toArray();
        array_walk($board, function (&$v, $k) {
            $v = $v ?? $k; // Use the numeric index for unassigned spots.
        });

        $chunked = array_chunk($board, self::DIMENSION);
        foreach ($chunked as $i => $row) {
            $chunked[$i] = ' ' . implode(self::BOARD_COLUMN_SEPARATOR, $row) . ' ';
        }

        $rowLine = self::BOARD_ROW_SEPARATOR . implode(
            self::BOARD_INTERSECT,
            array_fill(0, self::DIMENSION, self::BOARD_ROW_SEPARATOR)
        ) . self::BOARD_ROW_SEPARATOR;

        return implode(PHP_EOL . $rowLine . PHP_EOL, $chunked) . PHP_EOL;
    }

    /**
     * Make a speculative move without affecting the official Game state.
     *
     * @param int $location The location on the Board on which to play.
     * @param string $mark The Player mark to place at the location. No
     *     checking is done to confirm the mark matches any of the registered
     *     ::$players.
     * @return void
     */
    public function attempt(int $location, string $mark): void
    {
        $this->speculativeBoard = $this->speculativeBoard ?? clone $this->board;
        $this->speculativeBoard[$location] = $mark;
    }

    /**
     * Clear a single or all speculative moves made via ::attempt().
     *
     * @param int $location Optional position on the board to clear. If not
     *     provided, **ALL** speculative moves will be cleared.
     * @return void
     */
    public function undo(int $location = null): void
    {
        if (is_null($location)) {
            $this->speculativeBoard = null;
        } else {
            unset($this->speculativeBoard[$location]);
        }
    }

    /**
     * Internal getter for the "active" Board. If a ::$speculativeBoard is in
     * use via ::attempt(), it will be returned. Otherwise, the official game
     * ::$board will be returned.
     *
     * @return \Beporter\Tictactoe\Boards\Board The active speculative or
     *     official game Board.
     */
    protected function board(): Board
    {
        return ($this->speculativeBoard ?: $this->board);
    }

    /**
     * Helper to ::run() for formatting and outputting a single turn.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output The
     *     output interface provides access to write to the console.
     * @return void
     */
    protected function printTurn(OutputInterface $output): void
    {
        $output->writeln([
            '',
            '',
            '=====================',
            sprintf("%s's turn", $this->nextPlayer()->getMark()),
            $this->printBoard(),
        ]);
    }

    /**
     * Helper to ::run() for formatting and outputting a Player's chosen move.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output The
     *     output interface provides access to write to the console.
     * @param int $location The Board index played.
     * @return void
     */
    protected function printMove(OutputInterface $output, int $location): void
    {
        $output->writeln([
            sprintf('%s plays at %s.', $this->nextPlayer()->getMark(), $location),
        ]);
    }

    /**
     * Make a move for the next Player by placing the Player's mark at the
     * specified $location on the Board. First clears any speculative
     * ::attempt()s that may have been made.
     *
     * @param int $location The location on the Board on which the current
     *     Player will play.
     * @return void
     */
    protected function play(int $location): void
    {
        $this->undo();
        $this->board()[$location] = $this->nextPlayer()->getMark();
    }

    /**
     * Advance the internal pointer to the index of the next Player to play.
     *
     * @return void
     */
    protected function advancePlayerIndex()
    {
        $this->nextPlayerIndex = ($this->nextPlayerIndex + 1) % count($this->players);
    }

    /**
     * Return the next Player to make a move.
     *
     * @return \Beporter\Tictactoe\Players\Player The next Player to play.
     */
    protected function nextPlayer(): Player
    {
        return $this->players[$this->nextPlayerIndex];
    }

    /**
     * Setter for the ::$board property.
     *
     * @param \Beporter\Tictactoe\Boards\Board $board The board object to play on.
     * @return \Beporter\Tictactoe\Game Always self.
     */
    protected function setBoard(Board $board): Game
    {
        $this->undo();
        $this->board = $board;

        return $this;
    }

    /**
     * Add a new Player to the game. Under normal circumstances, this happens
     * during object construction. Adding Players after the game has started
     * is not recommended, so the method is protected for now.
     *
     * @param \Beporter\Tictactoe\Players\Player $player The Player to add to the game.
     * @return \Beporter\Tictactoe\Game Always self.
     */
    protected function addPlayer(Player $player): Game
    {
        $this->players[] = $player;

        return $this;
    }

    /**
     * Returns true when the provided $mark has at least one complete row,
     * column or diagonal on the Board.
     *
     * @param string $mark A Player mark with which to check the Board for win
     *    conditions.
     * @return bool True when the given $mark has a complete row, column or
     *    diagonal. False otherwise.
     */
    protected function markWon(string $mark): bool
    {
        for ($i = 0; $i < self::DIMENSION; $i++) {
            if ($this->allSame($this->board()->row($i), $mark)
                || $this->allSame($this->board()->column($i), $mark)
            ) {
                return true;
            }
        }

        return ($this->allSame($this->board()->diagonal(true), $mark)
            || $this->allSame($this->board()->diagonal(false), $mark)
        );
    }

    /**
     * Returns true if all elements in $a are the same value as $s.
     *
     * @param array $a The array to analyze for "sameness".
     * @param string $s The string value we wish to match the array against.
     * @return bool True if all elements in $a match $s. False otherwise.
     */
    protected function allSame(array $a, string $s): bool
    {
        $unique = array_unique($a);

        return (count($unique) === 1 && reset($unique) === $s);
    }
}
