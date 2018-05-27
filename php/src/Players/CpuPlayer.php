<?php
/**
 * Provides logic for a CPU tic-tac-toe player of varying difficulty.
 * Difficulty defaults to `2` if not set explicitly via ::setDifficulty()
 * after instantiation.
 *
 * Implements logic for both a neuristic approach (by following perfect-play
 * move strategies in-order) as well as a minimax approach. This duality is
 * due to the rest of the codebase supporting a variable Board::DIMENSION. At
 * 3x3, minimax is practical for brute-forcing an optimal move all the way to
 * every possible end-game, but at higher DIMENSIONs a different approach
 * begins to make sense. The code is stubbed out to make future use of the
 * heuristic helper methods to optimize the order in which minimax evaluates
 * potential moves so as to further increase performce in the future.
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
     * Maximum recursive depth when using a minimax strategy. 8 is almost
     * enough to enumerate all possible outcomes for a 3x3 board, yet still
     * small enough to be somewhat practical when the board dimension is
     * higher.
     *
     * @var int
     */
    const MINIMAX_MAX_DEPTH = 8;

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
     * @return \Beporter\Tictactoe\Players\Player Always self.
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
             * "Easy" (level 1) difficulty. Choses an empty spot from the
             * game Board at random.
             */
            1 => [
                'randomGuess',
            ],

            /**
             * "Medium" (level 2) difficulty. Attempts to chose a spot on
             * the Board that will lead to a win. Tuned for a 3x3 Board
             * (array indices 0-8).
             */
            2 => [
                'winIfPossible',
                'blockIfNecessary',
                'centerIfAvailable',
                'randomGuess',
            ],

            /**
             * "Hard" (level 3) difficulty. Always makes the best possible
             * move. Should always result in a win or a tie. Uses
             * minimax+alphabeta when Board is 3x3 and a heuristic approach
             * for larger boards.
             */
            3 => [
                'minimaxOrHeuristic', // Meta helper to branch on board size.
                'winIfPossible',
                'blockIfNecessary',
                'createFork',
                'blockForkPotential',
                'createTwoInARow',
                'cornerOpening',
                // @TODO: There is a logic gap here. In a perfect game, (corner->center->oppposite corner) needs to be followed by an edge move to pre-empt a fork by X.
                'centerIfAvailable',
                'oppositeCorner',
                'emptyCorner',
                'emptySide',

                // Intentionally avoid using 'randomGuess' as a last resort so
                // we know if our logic chain above is imperfect via a thrown
                // \LogicException.
            ],
        ];

        return $sets[$this->difficulty] ?? [];
    }

    /**
     * ======================================================================
     *
     * From here down are methods that each implement a specific move strategy.
     *
     * For these methods, the signature is:
     *
     *     protected function strategyName(TryMovesInterface $game)
     *
     * The method should return an integer Board index on success, and boolean
     * `false` if applying the strategy to the current $game produces no valid
     * move (meaning the next strategy in order should be tried.)
     *
     * @TODO: These could (should?) be moved out into their own
     * \Beporter\Tictactoe\Strategies namespace, each as an invokable class,
     * to keep the CpuPlayer class tightly focused on choosing and using the
     * appropriate strategies for the configured ::$difficulty. Given the
     * smallish scope of Tic-Tac-Toe, it doesn't seem like a productive use
     * of time though and potentially further complicates code review.
     *
     * @see https://en.wikipedia.org/wiki/Tic-tac-toe#Strategy
     */

    /**
     * A meta helper that attempts to conserve computing resources by selecting
     * a strategy based on the board size. At 3x3, just use straight minimax
     * to brute-force an answer. Higher than that-- fall-through and use a
     * purely heuristic approach.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @return int|false A minimax-produced move when the board is 3x3,
     *     otherwise false to drop through to the heuristic helpers in
     *     `::moveSet()` when ::$difficulty = 3.
     */
    protected function minimaxOrHeuristic(TryMovesInterface $game)
    {
        if ($game::DIMENSION <= 3) {
            return $this->minimax($game);
        }

        return false;
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
     * Create a situation where a Player has two ways to win on their _next_
     * move.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @return int|false A Board index to mark, or false if this strategy
     *     results in no potential move.
     */
    protected function createFork(TryMovesInterface $game)
    {
        return $this->forkHelper($game, $this->getMark());
    }

    /**
     * Anticipate and block an opponent's potential to create a fork.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @return int|false A Board index to mark, or false if this strategy
     *     results in no potential move.
     */
    protected function blockForkPotential(TryMovesInterface $game)
    {
        return $this->forkHelper($game, $game->getOpponent()->getMark());
    }

    /**
     * Locate one of our existing marks in an open row/col/diag to mark.
     *
     * For each available space, see if each intersecting row/col/diag contains any of our marks and none of the opponent's. Choose the intersect with the most existing marks.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @return int|false A Board index to mark, or false if this strategy
     *     results in no potential move.
     */
    protected function createTwoInARow(TryMovesInterface $game)
    {
        $candidates = $this->findCandidateSpots($game, $this->getMark());

        // Use the open space that adds to the most unblocked intersects.
        if (count($candidates)) {
            return key($candidates);
        }

        return false;
    }

    /**
     * Helper for ::createFork() and ::blockForkPotential().
     *
     * The conditions for a fork are:
     *     - Two of the Player's existing marks on the board,
     *     - An empty spot that intersects the marks rows/cols/diags.
     *
     * Fork options can be found by speculatively placing the mark in each
     * open square, then analyzing that spot's row/col/diags to see if we now
     * have two of the mark and one empty spot. At least two of the
     * row/col/diag have to exhibit this for the fork to be valid.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @param string $mark The Player to examine.
     * @return int|false A Board index to mark, or false if this strategy
     *     results in no potential move.
     */
    protected function forkHelper(TryMovesInterface $game, string $mark)
    {
        $candidates = $this->findCandidateSpots($game, $mark);

        // Use the "best" available fork option, if any.
        if (count($candidates) && reset($candidates) > 1) {
            return key($candidates);
        }

        return false;
    }

    /**
     * Helper for ::forkHelper() and ::createTwoInARow().
     *
     * Locates and ranks available spots on the board that will add to
     * existing "lines" of the given $mark.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @param string $mark The Player to examine.
     * @return array A sorted array of [position => count] pairs where the
     *     count is the number of intersecting lines that benefit from placing
     *     $mark at $location.
     */
    protected function findCandidateSpots(TryMovesInterface $game, string $mark): array
    {
        $dimension = $game::DIMENSION;
        $candidates = [];

        foreach ($game->getBoard()->available() as $possibleMove) {
            $game->attempt($possibleMove, $mark);

            foreach ($this->getAllIntersects($game->getBoard(), $possibleMove) as $intersect) {
                if ($this->twoInARow($intersect, $mark)) {
                    $candidates[$possibleMove] = ($candidates[$possibleMove] ?? 0) + 1;
                }
            }

            $game->undo($possibleMove);
        }

        arsort($candidates, SORT_NUMERIC);

        return $candidates;
    }

    /**
     * Given a board and an index, return the set of intersecting rows,
     * columns and diagonals.
     *
     * @param Beporter\Tictactoe\Boards\Board $board The current game Board state.
     * @param int $location The location from which to generate intersects.
     * @return array An array of arrays, each representing an intersecting row,
     *     column, or diagonal. (Diagonals only if they contain $location.)
     */
    protected function getAllIntersects(Board $board, int $location): array
    {
        $dimension = (int)sqrt(count($board));
        $intersects = [
            $board->row((int)($location / $dimension)),
            $board->column($location % $dimension),
        ];

        // Check and add diagonals if they overlap with $location.
        $diags = [
            $board->diagonal(true),
            $board->diagonal(false),
        ];
        foreach ($diags as $diag) {
            if (in_array($location, array_keys($diag))) {
                $intersects[] = $diag;
            }
        }

        return $intersects;
    }

    /**
     * Returns true if the given array contains one empty space and the
     * remaining values are $mark.
     *
     * @param array $line A single row/column/diagonal from the game board.
     * @param string $mark The Player mark to confirm.
     * @return bool True if $line consists of one empty spot and the rest $marks.
     */
    protected function twoInARow(array $line, string $mark): bool
    {
        $emptyMark = '.'; // @TODO: This could potentially conflict with a valid Player mark.
        $markCounts = array_count_values(array_map(
            function ($v) use ($emptyMark) {
                return $v ?? $emptyMark;
            },
            $line
        ));

        return (
            array_key_exists($mark, $markCounts)
            && array_key_exists($emptyMark, $markCounts)
            && ($markCounts[$mark] === count($line) - 1)
            && ($markCounts[$emptyMark] === 1)
        );
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
        // Falls apart a bit when ::DIMENSION is even.
        $center = (int)(count($game->getBoard()) / 2);

        if (in_array($center, $game->getBoard()->available())) {
            return $center;
        }

        return false;
    }

    /**
     * Alternate to `::centerIfAvailable()`. Against imperfect players, an
     * opening corner move leaves more room for the opponent to make mistakes.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @return int|false A Board index to mark, or false if this strategy
     *     results in no potential move.
     */
    protected function cornerOpening(TryMovesInterface $game)
    {
        if (count($game->getBoard()->available()) === count($game->getBoard())) {
            return 0;
        }

        return false;
    }

    /**
     * If the opponent has played in a corner, play the opposite corner.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @return int|false A Board index to mark, or false if this strategy
     *     results in no potential move.
     */
    protected function oppositeCorner(TryMovesInterface $game)
    {
        $board = $game->getBoard();
        $corners = $board->corners();

        foreach ($corners as $i => $cornerIndex) {
            $counterpartIndex = 3 - $i; // Index into $corners.

            if ($board[$cornerIndex] === $game->getOpponent()->getMark()
                && in_array($corners[$counterpartIndex], $board->available())
            ) {
                return $corners[$counterpartIndex];
            }
        }

        return false;
    }

    /**
     * If an empty corner is available, choose it.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @return int|false A Board index to mark, or false if this strategy
     *     results in no potential move.
     */
    protected function emptyCorner(TryMovesInterface $game)
    {
        $board = $game->getBoard();
        $corners = $board->corners();

        foreach ($corners as $cornerIndex) {
            if (in_array($cornerIndex, $board->available())) {
                return $cornerIndex;
            }
        }

        return false;
    }

    /**
     * If the middle of any edge is available, choose it.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @return int|false A Board index to mark, or false if this strategy
     *     results in no potential move.
     */
    protected function emptySide(TryMovesInterface $game)
    {
        $available = $game->getBoard()->available();
        $edges = [ // @TODO: Getting tired. Hardcoding for a 3x3 board. -_-
            1,
            3,
            5,
            7,
        ];

        foreach ($edges as $edgeIndex) {
            if (in_array($edgeIndex, $available)) {
                return $edgeIndex;
            }
        }

        return false;
    }

    /**
     * Choose an available spot at random. This is the typical "last resort"
     * option.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @return int|false A Board index to mark, or false if this strategy
     *     results in no potential move.
     * @codeCoverageIgnore Can't easily test a method that depends on rand().
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
     * Implement the minimax algorithm with alpha-beta pruning.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @param string $mark The mark to speculative play in the current iteration.
     * @param int $depth A positive depth counter. The algorithm returns the
     *     best score it can when $depth decrements to zero. Deault:
     *     self::MINIMAX_MAX_DEPTH.
     * @param int $alpha Minimum pruning value. Default: PHP_INT_MIN.
     * @param int $beta Maximum pruning value. Default: PHP_INT_MAX.
     * @return array A tuple ['score' => int, 'position' => int].
     * @see https://git.io/phpunit-mock-objects-419 (PHPUnit bug w/ PHP_INT_MIN.)
     */
    protected function minimaxRecursive(
        TryMovesInterface $game,
        string $mark,
        int $depth = self::MINIMAX_MAX_DEPTH,
        int $alpha = -\PHP_INT_MAX,
        int $beta = \PHP_INT_MAX
    ): array {
        $bestPosition = false;
        if ($game->gameOver() || $depth === 0) {
            return [
                'score' => $this->minimaxCalculateScore($game),
                'position' => $bestPosition,
            ];
        }

        foreach ($this->minimaxSortAvailable($game, $mark) as $possibleMove) {
            $game->attempt($possibleMove, $mark);

            if ($mark === $this->getMark()) {
                $score = $this->minimaxRecursive(
                    $game,
                    $game->getOpponent()->getMark(),
                    $depth - 1,
                    $alpha,
                    $beta
                )['score'];
                if ($score > $alpha) {
                    $alpha = $score;
                    $bestPosition = $possibleMove;
                }
            } else {
                $score = $this->minimaxRecursive(
                    $game,
                    $this->getMark(),
                    $depth - 1,
                    $alpha,
                    $beta
                )['score'];
                if ($score < $beta) {
                    $beta = $score;
                    $bestPosition = $possibleMove;
                }
            }

            $game->undo($possibleMove);

            if ($alpha >= $beta) {
                break;
            }
        }

        $bestScore = ($mark === $this->getMark() ? $alpha : $beta);
        return [
            'score' => $bestScore,
            'position' => $bestPosition,
        ];
    }

    /**
     * Calculate an arbitrary score for a given board state to evaluate a
     * given minimax path's effectiveness.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @return int A positive value to indicate benefit to the calling Player;
     *     a negative value for the opponent. Zero if the game state is
     *     considered neutral.
     */
    protected function minimaxCalculateScore(TryMovesInterface $game)
    {
        if ($game->gameOver() === $this->getMark()) {
            return 100;
        }

        if ($game->gameOver() === $game->getOpponent()->getMark()) {
            return -100;
        }

        // @TODO: Additional nuance in scoring would be important here for lower ::MINIMAX_MAX_DEPTH values (or larger `Board::DIMENSION`s) where reaching an end-game was not guaranteed.

        return 0;
    }

    /**
     * Sort available board positions by a crude "optimal-ness" score. This
     * can help optimize the minimax+alphabeta process exponentially.
     *
     * @param \Beporter\Tictactoe\Interfaces\TryMovesInterface $game The
     *     current Game state.
     * @param string $mark The Player mark for which we wish to optimize.
     * @return array An array containing all of the same values from
     *     $available, sorted in order of likelihood of producing a favorable
     *     move result for $mark.
     */
    protected function minimaxSortAvailable(TryMovesInterface $game, string $mark): array
    {
        // @TODO: Sort available spots in order: existing-2-in-a-rows, forks, blocks, adjacencies, centers, corners, edges.

        return $game->getBoard()->available();
    }
}
