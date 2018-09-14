<?php
/**
 * Defines a contract for executing a game of Tic-tac-toe.
 */

declare(strict_types=1);

namespace Beporter\Tictactoe\Interfaces;

use Beporter\Tictactoe\Boards\Board;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * \Beporter\Tictactoe\Interfaces\PlayGameInterface
 */
interface PlayGameInterface
{
    /**
     * Runs the game loop.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output The
     *     output interface provides access to ::write() or ::writeln() to
     *     the console.
     * @return true|string True in the event of a tie, otherwise the mark
     *     representing the winning Player.
     */
    public function run(OutputInterface $output);

    /**
     * Construct a string representation of the current Board state.
     *
     * @return string A console-friendly representation of the Board.
     */
    public function printBoard(): string;
}
