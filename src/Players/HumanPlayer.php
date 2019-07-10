<?php
/**
 * Provides an interface for querying a human player (using the console) for moves.
 */

declare(strict_types=1);

namespace Beporter\Tictactoe\Players;

use Beporter\Tictactoe\Boards\Board;
use Beporter\Tictactoe\Interfaces\TryMovesInterface;
use Symfony\Component\Console\Input\InputInterface;
use \BadMethodCallException;

/**
 * \Beporter\Tictactoe\Players\HumanPlayer
 */
class HumanPlayer extends Player
{
    /**
     * The callback method used to request user input from the console.
     *
     * @var callable
     */
    protected $asker = null;

    /**
     * Setter for ::$asker.
     *
     * @param callable $asker A callback function that will prompt the user
     *    for input and return an integer Board index value when called.
     * @return \Beporter\Tictactoe\Players\HumanPlayer Always self.
     */
    public function setAsker(callable $asker): HumanPlayer
    {
        $this->asker = $asker;

        return $this;
    }

    /**
     * Invokes the callable ::$asker to obtain the next move from the human
     * user via the CLI console.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The current Game state
     *     to be used as reference for the Player in deciding their next move.
     * @return int The Board index that the Player wishes to mark next.
     */
    public function getBestMove(TryMovesInterface $game): int
    {
        if (!is_callable($this->asker)) {
            throw new BadMethodCallException(
                'HumanPlayer::$asker is not callable. Set with $humanPlayser->setAsker($callable) first.'
            );
        }

        return intval(call_user_func($this->asker, $game->getBoard()));
    }
}
