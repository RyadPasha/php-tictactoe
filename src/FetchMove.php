<?php
/**
 * Invoke-able class that wraps up the messiness of requesting an interactive
 * move from a human player. Lets us avoid having to inject Symfony I/O classes
 * into the HumanPlayer class.
 */

declare(strict_types=1);

namespace Beporter\Tictactoe;

use Beporter\Tictactoe\Boards\Board;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use \RuntimeException;

/**
 * \Beporter\Tictactoe\FetchMove
 */
class FetchMove
{
    /**
     * Store the Player's mark to display during user prompting.
     *
     * @var string
     */
    protected $mark = null;

    /**
     * Hang on to a Symfony InputInterface to pass to Question::ask().
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input = null;

    /**
     * Hang on to a Symfony OutputInterface to pass to Question::ask().
     *
     * @var \Symfony\Component\Console\Input\OutputInterface
     */
    protected $output = null;

    /**
     * Hang on to a Symfony HelperInterface to pass to Question::ask().
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $questionHelper = null;

    /**
     * Constructor.
     *
     * @param string $mark
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\HelperInterface $questionHelper
     */
    public function __construct(
        string $mark,
        InputInterface $input,
        OutputInterface $output,
        HelperInterface $questionHelper
    ) {
        $this->mark = $mark;
        $this->input = $input;
        $this->output = $output;
        $this->questionHelper = $questionHelper;
    }

    /**
     * Construct a Symfony Question that restricts inputs to the available
     * spots on the Board, pose it to the user, and return the result.
     *
     * @param \Beporter\Tictactoe\Boards\Board $board The current game Board.
     * @return int The result obtained from the console.
     */
    public function __invoke(Board $board): int
    {
        $question = new Question(sprintf(
            "%s's move [0-%s] > ",
            $this->mark,
            count($board) - 1
        ));
        $question->setValidator($this->buildValidator($board));
        $answer = $this->questionHelper->ask($this->input, $this->output, $question);

        return intval($answer);
    }

    /**
     * Helper that returns a suitable anonymous function for use as a
     * validator with the Symfony Question object.
     *
     * @param \Beporter\Tictactoe\Boards\Board $board The current game Board.
     * @return callable A function that will validate user input against the
     *     current game board.
     */
    protected function buildValidator(Board $board): callable
    {
        return function ($answer) use ($board) {
            $answer = intval($answer);

            if (!in_array($answer, $board->available())) {
                throw new RuntimeException(
                    'Please select an open square.'
                );
            }

            return $answer;
        };
    }
}
