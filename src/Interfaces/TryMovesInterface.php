<?php
/**
 * Defines a contract that provides a "read-only" interface for Player
 * objects to try out moves and check the state of the game without
 * affecting the "official" Game state. This allows the Game instance to be
 * provided to Players to help them plan their next move without them being
 * able to modify the Game directly (cheat).
 */

declare(strict_types=1);

namespace Beporter\Tictactoe\Interfaces;

use Beporter\Tictactoe\Boards\Board;
use Beporter\Tictactoe\Boards\ReadOnlyBoard;
use Beporter\Tictactoe\Players\Player;

/**
 * \Beporter\Tictactoe\Interfaces\TryMovesInterface
 */
interface TryMovesInterface
{
    /**
     * Must return the current **speculative** Board state, including any
     * applied ::attempt() calls, _as a read-only object_ (meaning it can not
     * be further changed directly.)
     *
     * @return \Beporter\Tictactoe\Boards\ReadOnlyBoard $board The
     *     speculative Board state.
     */
    public function getBoard(): ReadOnlyBoard;

    /**
     * Return the "opponent" Player object as seen from the perspective of
     * the Player who's turn it is to play next.
     *
     * @return \Beporter\Tictactoe\Players\Player The opponent Player.
     */
    public function getOpponent(): Player;

    /**
     * Must return not-false if the Board state, incuding any ::attempt()
     * calls, represents a finished game (tied or won).
     *
     * @return string|bool The winning mark as a string, true if the Game
     *     tied or false if the game is on-going.
     */
    public function gameOver();

    /**
     * Make a speculative move for the current Player in the Game. This move
     * any any further calls to ::attempt() will be undone by a call to
     * ::play() or ::undo().
     *
     * @param int $location The Board index on which to speculatively play.
     * @param string $mark The Player mark to place at the location.
     * @return void
     */
    public function attempt(int $location, string $mark): void;

    /**
     * Reset a single or all speculative moves applied via ::attempt(). Also
     * called when ::play() is next called.
     *
     * @param int $location Optional position on the board to clear. If not
     *     provided, **ALL** speculative moves will be cleared from the Board.
     * @return void
     */
    public function undo(int $location = null): void;
}
