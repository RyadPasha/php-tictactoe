<?php
/**
 * Extends the read/write Board to disable the ability to make changes. Used
 * when providing the Board instance to Players for making moves to prevent
 * direct changes to the board state while still allowing access to the
 * convenience methods such as ::available(), ::row(), ::column(), etc.
 */

declare(strict_types=1);

namespace Beporter\Tictactoe\Boards;

use Beporter\Tictactoe\Boards\Board;
use \BadMethodCallException;

/**
 * \Beporter\Tictactoe\Boards\ReadOnlyBoard
 */
class ReadOnlyBoard extends Board
{
    /**
     * Overriden constructor inherits the ::$board provided by the caller.
     *
     * @param \Beporter\Tictactoe\Boards\Board $board The read/write Board to
     *     use to initialze this read-only version.
     */
    public function __construct(Board $board)
    {
        $this->dimension = $board->dimension;
        $this->board = $board->board;

        // Do NOT call the parent::__construct() method.
    }

    /**
     * \ArrayAccess interface method is overridden to always throw a
     * \DomainException when used.
     *
     * @param int $offset The location in ::$board to which to assign $value.
     * @param string $value The value to assign to the provided location.
     * @throws \BadMethodCallException whenever the method is called.
     */
    public function offsetSet($offset, $value)
    {
        throw new BadMethodCallException('ReadOnlyBoard values can not be assigned.');
    }

    /**
     * \ArrayAccess interface method is overridden to prevent values from
     * being cleared from the Board.
     *
     * @param int $offset The index to unset.
     * @return void Always void per \ArrayAccess spec.
     */
    public function offsetUnset($offset)
    {
        throw new BadMethodCallException('ReadOnlyBoard values can not be unset.');
    }
}
