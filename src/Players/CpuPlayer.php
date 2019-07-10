<?php
/**
 * Provides logic for a CPU tic-tac-toe player of varying difficulty.
 * Difficulty defaults to `2` if not set explicitly via ::setDifficulty()
 * after instantiation.
 *
 * Implements logic for both a heuristic approach at lower difficulties as well
 * as a minimax approach for perfect play.
 */

declare(strict_types=1);

namespace Beporter\Tictactoe\Players;

use Beporter\Tictactoe\Boards\Board;
use Beporter\Tictactoe\Interfaces\TryMovesInterface;
use \LogicException;
use \OutOfRangeException;

/**
 * \Beporter\Tictactoe\Players\CpuPlayer
 */
class CpuPlayer extends Player
{
    /**
     * The configured difficulty at which the CPU should play. Default
     * difficulty is 2.
     *
     * @var int
     */
    protected $difficulty = 2;

    /**
     * Setter for ::$difficulty.
     *
     * @param int $difficulty The desired difficulty level for the CPU player.
     *     Range: 1-3.
     * @return \Beporter\Tictactoe\Players\CpuPlayer Always self.
     * @throws \OutOfRangeException when the provided $dimension is zero or negative.
     */
    public function setDifficulty(int $difficulty): CpuPlayer
    {
        if ($difficulty < 1 || $difficulty > 3) {
            throw new OutOfRangeException(sprintf(
                'Difficulty must be an integer between 1-3 inclusive. Recieved `%s`.',
                $difficulty
            ));
        }

        $this->difficulty = $difficulty;

        return $this;
    }

    /**
     * Invokes the set of individual logic helpers at the appropriate
     * difficulty level to determine the next move.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state to be used as reference for the Player in
     *     deciding their next move.
     * @return int The Board index that the Player wishes to mark next.
     * @throws \LogicException If a ::moveSet() does not result in a valid
     *     move, which is a coding failure.
     */
    public function getBestMove(TryMovesInterface $game): int
    {
        foreach ($this->moveSet() as $attemptMove) {
            $move = $this->$attemptMove($game);
            if ($move !== false) {
                return $move;
            }
        }

        throw new LogicException(sprintf(
            'CpuPlayer::$difficulty=`%s` resulted in no possible moves.',
            $this->difficulty
        ));
    }

    /**
     * Using the configured ::$difficulty, return an ordered set of internal
     * method names to call to determine the next move.
     *
     * @return array A flat array of logic helpers (method names) to call
     *     **in order** to attempt to produce a valid move.
     */
    protected function moveSet(): array
    {
        $sets = [
            /**
             * "Easy" (level 1) difficulty.
             */
            1 => [
                'randomGuess',
            ],

            /**
             * "Medium" (level 2) difficulty.
             */
            2 => [
                'winIfPossible',
                'blockIfNecessary',
                'centerIfAvailable',
                'randomGuess',
            ],

            /**
             * "Hard" (level 3) difficulty. Perfect play via minimax.
             */
            3 => [
                'minimax',
            ],
        ];

        return $sets[$this->difficulty] ?? [];
    }

    /**
     * If an available spot causes THIS player to win, play it to win. (Brute
     * force approach. Could possibly be improved by finding 2-in-a-row sets
     * first?)
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @return int|false A Board index to mark, or false if this strategy
     *     results in no potential move.
     */
    protected function winIfPossible(TryMovesInterface $game)
    {
        foreach ($game->getBoard()->available() as $openSpace) {
            $game->attempt($openSpace, $this->getMark());
            if ($game->gameOver() === $this->getMark()) {
                return $openSpace;
            }
            $game->undo();
        }

        return false;
    }

    /**
     * If an available spot causes the OTHER player to win, play it to block
     * them.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @return int|false A Board index to mark, or false if this strategy
     *     results in no potential move.
     */
    protected function blockIfNecessary(TryMovesInterface $game)
    {
        foreach ($game->getBoard()->available() as $openSpace) {
            $game->attempt($openSpace, $game->getOpponent()->getMark());
            if ($game->gameOver()) {
                return $openSpace;
            }
            $game->undo();
        }

        return false;
    }

    /**
     * Choose the center spot if it is available. This will (typically) be
     * the first move of the game when the other strategies are employed in
     * the proper order.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @return int|false A Board index to mark, or false if this strategy
     *     results in no potential move.
     */
    protected function centerIfAvailable(TryMovesInterface $game)
    {
        $center = (int)(count($game->getBoard()) / 2);

        if (in_array($center, $game->getBoard()->available())) {
            return $center;
        }

        return false;
    }

    /**
     * Choose an available spot at random. This is the typical "last resort"
     * option.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @return int A randomly chosen Board index to mark.
     */
    protected function randomGuess(TryMovesInterface $game): int
    {
        $availableSpaces = $game->getBoard()->available();
        $randomIndex = rand(0, count($availableSpaces) - 1);
        return intval($availableSpaces[$randomIndex]);
    }

    /**
     * Use the minimax algorithm with alpha-beta pruning to look ahead and
     * attempt to find the optimal move.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @return int The chose open board position on which to play.
     */
    protected function minimax(TryMovesInterface $game)
    {
        return $this->minimaxRecursive($game, $this->getMark())['position'];
    }

    /**
     * Implement the minimax algorithm. Will always run to terminal (game over) depth.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @param string $mark The mark to speculative play in the current iteration.
     * @param int $depth Track recursion depth solely to favor quicker wins.
     * @return array A tuple ['score' => int, 'position' => int].
     */
    protected function minimaxRecursive(TryMovesInterface $game, string $mark, int $depth = 0): array
    {
        if ($game->gameOver()) {
            return [
                'score' => $this->minimaxCalculateScore($game, $depth),
                'position' => $bestPosition,
            ];
        }

        $results = [];
        foreach ($game->getBoard()->available() as $possibleMove) {
            $game->attempt($possibleMove, $mark);

            $results[$possibleMove] = $this->minimaxRecursive(
                $game,
                ($mark === $this->getMark() ? $game->getOpponent()->getMark() : $this->getMark()),
                $depth + 1
            )['score'];

            $game->undo($possibleMove);
        }

        arsort($results, SORT_NUMERIC);
        return [
            'score' => reset($results),
            'position' => key($results),
        ];
    }

    /**
     * Calculate an arbitrary score for a given board state to evaluate a
     * given minimax path's effectiveness.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @param int $depth Favor quicker wins by reducing values at deeper depths.
     * @return int A positive value to indicate benefit to the calling Player;
     *     a negative value for the opponent. Zero if the game state is
     *     considered neutral.
     */
    protected function minimaxCalculateScore(TryMovesInterface $game, int $depth)
    {
        if ($game->gameOver() === $this->getMark()) {
            return 100 - $depth;
        }

        if ($game->gameOver() === $game->getOpponent()->getMark()) {
            return -100 + $depth;
        }

        return 0;
    }
}
