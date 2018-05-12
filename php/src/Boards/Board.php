<?php
/**
 * Stores a square tic-tac-toe game board. Has no knowledge of the rules of
 * the game or the players. Is only responsible for storing and reporting
 * values for each spot on the board. Provides a few convenience functions
 * for fetching rows, columns and diagonals. Once initialized, the Board can
 * be assigned-to and read-from using standard php array access notation:
 *
 *    $board = new Board();
 *    $board[0] = 'X';
 *    echo $board[0];  // Output: X
 */

declare(strict_types=1);

namespace Beporter\Tictactoe\Boards;

use \ArrayAccess;
use \Countable;
use \DomainException;
use \OutOfRangeException;

/**
 * \Beporter\Tictactoe\Boards\Board
 */
class Board implements ArrayAccess, Countable
{
    /**
     * Keep track of the Board dimension.
     *
     * @var int
     */
    protected $dimension = null;

    /**
     * Stores the internal state of the board as a single-dimension array.
     * Initialized during ::__construct().
     *
     * @var array
     */
    protected $board = null;

    /**
     * Constructor. Initializes the ::$board array.
     *
     * @param int $dimension The length of the board's width and height. Defaults to 3.
     * @throws \OutOfRangeException when the provided $dimension is zero or negative.
     */
    public function __construct(int $dimension = 3)
    {
        if ($dimension < 1) {
            throw new OutOfRangeException(sprintf(
                'Dimension must be an integer greater than zero. Recieved `%s`.',
                $dimension
            ));
        }

        $this->dimension = $dimension;
        $this->board = array_fill_keys(range(0, $dimension * $dimension - 1), null);
    }

    /**
     * \ArrayAccess interface method to determine if the provided offset exists
     * or not.
     *
     * @param int $offset The index to check.
     * @return bool True when the index is valid, false otherwise.
     */
    public function offsetExists($offset)
    {
        // Have to do our own type coersion because the \ArrayAccess
        // interface doesn't typehint ::offsetExists($offset).
        $offset = intval($offset);

        return ($offset >= 0 && $offset < count($this->board));
    }

    /**
     * \ArrayAccess interface method to fetch a value from the provided offset.
     * No bounds checking is required here as \ArrayAccess covers that.
     *
     * @param int $offset The index to fetch.
     * @return string The value stored at the provided $offset.
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            trigger_error(sprintf('Undefined offset: %s', $offset), E_USER_NOTICE);
        }

        return $this->board[intval($offset)];
    }

    /**
     * \ArrayAccess interface method for assigning a new value to a spot on
     * the Board. The value can be any string. Each spot on the Board can only
     * be assigned exactly once. Subsequent attempts will produce a
     * DomainException.
     *
     * @param int $offset The location in ::$board to which to assign $value.
     * @param string $value The value to assign to the provided location.
     * @return void Always void per \ArrayAccess spec.
     * @throws \DomainException when the provided $index has a non-null value already present.
     */
    public function offsetSet($offset, $value)
    {
        $offset = intval($offset);

        if (!$this->offsetExists($offset)) {
            trigger_error(sprintf('Undefined offset: %s', $offset), E_USER_ERROR);
        }

        if (!is_null($this->board[$offset])) {
            throw new DomainException(sprintf(
                'Provided index `%s` is already set to `%s`.',
                $offset,
                $this->board[$offset]
            ));
        }

        $this->board[$offset] = (string)$value;
    }

    /**
     * \ArrayAccess interface method to clear a value from the Board.
     *
     * @param int $offset The index to unset.
     * @return void Always void per \ArrayAccess spec.
     */
    public function offsetUnset($offset)
    {
        $offset = intval($offset);
        $this->board[$offset] = null;
        // or maybe?...
        //throw new BadMethodCallException('Board values can not be unset.');
    }

    /**
     * \Countable interface method to get the size of the Board.
     *
     * @return int The number of spots on the current Board.
     */
    public function count()
    {
        return count($this->board);
    }

    /**
     * Reduce the Board object down to an indexed array.
     *
     * @return array The array version of the current Board.
     */
    public function toArray(): array
    {
        return $this->board;
    }

    /**
     * Convenience method that returns a flat array of indices that do not
     * yet contain a player mark.
     *
     * @return array A list of unset locations on the Board.
     */
    public function available(): array
    {
        return array_keys(array_filter(
            $this->board,
            function ($v) {
                return is_null($v);
            }
        ));
    }

    /**
     * Returns an array of spots on the Board for the row identified by $offset.
     *
     * Example:
     *
     *     $board = [
     *         0, 1, 2,
     *         3, 4, 5,
     *         6, 7, 8,
     *     ];
     *     $board->row(0); // = [0, 1, 2]
     *
     * @param int $offset The row number (zero indexed) to fetch.
     * @return array The subset of spots on the Board from the row identified
     *     by $offset.
     * @throws \OutOfRangeException If the provided $offset is < 0 or larger
     *     than the number of rows.
     */
    public function row(int $offset): array
    {
        if ($offset < 0 || $offset >= $this->dimension) {
            throw new OutOfRangeException(sprintf(
                'Invalid row requested. Valid: 0-%s. Recieved `%s`',
                $this->dimension - 1,
                $offset
            ));
        }

        return array_slice(
            $this->board,
            $this->dimension * $offset,
            $this->dimension,
            true
        );
    }

    /**
     * Returns an array of spots on the Board for the column identified by $offset.
     *
     * Example:
     *
     *     $board = [
     *         0, 1, 2,
     *         3, 4, 5,
     *         6, 7, 8,
     *     ];
     *     $board->column(0); // = [0, 3, 6]
     *
     * @param int $offset The row number (zero indexed) to fetch.
     * @return array The subset of spots on the Board from the column
     *     identified by $offset.
     * @throws \OutOfRangeException If the provided $offset is < 0 or larger
     *     than the number of columns.
     */
    public function column(int $offset): array
    {
        if ($offset < 0 || $offset >= $this->dimension) {
            throw new OutOfRangeException(sprintf(
                'Invalid column requested. Valid: 0-%s. Recieved `%s`',
                $this->dimension - 1,
                $offset
            ));
        }

        return array_column(
            array_chunk($this->board, $this->dimension, false),
            $offset
        );
    }

    /**
     * Returns an array of spots on the Board for either the "left" diagonal
     * or the "right" diagonal.
     *
     * Example:
     *
     *     $board = [
     *         0, 1, 2,
     *         3, 4, 5,
     *         6, 7, 8,
     *     ];
     *     $board->diagonal(true); // = [0, 4, 8]
     *     $board->diagonal(false); // = [2, 4, 6]
     *
     * @param bool $left Whether to get the 'left' or 'right' diagonal.
     * @return array The subset of spots on the Board from the diagonal
     *     identified by $left.
     */
    public function diagonal(bool $left): array
    {
        $start = ($left ? 0 : $this->dimension - 1);
        $end = count($this->board) - $start;
        $increment = $this->dimension + ($left ? 1 : -1);
        $diagonal = [];

        for ($i = $start; $i < $end; $i += $increment) {
            $diagonal[$i] = $this->board[$i];
        }

        return $diagonal;
    }


    /**
     * Returns an array of board indices representing the corners of the board.
     *
     * Example:
     *
     *     $board = [
     *         0, 1, 2,
     *         3, 4, 5,
     *         6, 7, 8,
     *     ];
     *     $board->corners(); // = [0, 2, 6, 8]
     *
     * @return array The subset of spots on the Board representing corners.
     */
    public function corners(): array
    {
        $boardSize = count($this->board);

        // The order the indices are returned can matter. Keep them sequential.
        return [
            0,
            $this->dimension - 1,
            $boardSize - $this->dimension,
            $boardSize - 1,
        ];
    }
}
