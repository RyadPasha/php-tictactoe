<?php
/**
 * Base class for defining the mandatory interface required for Player objects.
 * Players are not concerned with the length of their representative marks.
 */

declare(strict_types=1);

namespace Beporter\Tictactoe\Players;

use Beporter\Tictactoe\Boards\Board;
use Beporter\Tictactoe\Interfaces\TryMovesInterface;

/**
 * \Beporter\Tictactoe\Players\Player
 */
abstract class Player
{
    /**
     * The string that will represent this Player on the Board.
     *
     * @var string
     */
    protected $mark = null;

    /**
     * Constructor. Set the mark.
     *
     * @param string $mark The string value to use from now on to represent this Player.
     * @throws \OutOfRangeException when the provided $dimension is zero or negative.
     */
    public function __construct(string $mark)
    {
        $this->setMark($mark);
    }

    /**
     * Abstract method for obtaining an integer Board index where the Player
     * wishes to play next.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The current Game state
     *     to be used as reference for the Player in deciding their next move.
     * @return int The Board index that the Player wishes to mark next.
     */
    abstract public function getBestMove(TryMovesInterface $game): int;

    /**
     * Getter for ::$mark.
     *
     * @return string ::$mark.
     */
    final public function getMark(): string
    {
        return $this->mark;
    }

    /**
     * Setter for ::$mark.
     *
     * @param string $mark The string value to use from now on to represent this Player.
     * @return \Beporter\Tictactoe\Players\Player Always self.
     */
    final protected function setMark(string $mark): Player
    {
        $this->mark = $mark;

        return $this;
    }
}
