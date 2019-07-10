<?php
/**
 * Define a Symfony Command class to process command line args & user I/O
 * and execute the game logic.
 */

declare(strict_types=1);

namespace Beporter\Tictactoe;

use Beporter\Tictactoe\Players\CpuPlayer;
use Beporter\Tictactoe\Players\HumanPlayer;
use Beporter\Tictactoe\Players\Player;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * \Beporter\Tictactoe\TicTacToeCommand
 */
class TicTacToeCommand extends Command
{
    /**
     * Configure the Command with its callable name, help description and
     * available command line arguments/options.
     */
    protected function configure()
    {
        $this
            ->setName('tic-tac-toe')
            ->setDescription('Play Tic-Tac-Toe with human or CPU players. '
                . 'You can provide your own symbols (including Unicode emoji) '
                .' to represent each player.')
            ->addOption(
                'p1-level',
                null,
                InputOption::VALUE_REQUIRED,
                'Set P1 to be played by the CPU of the provided difficulty [1-3]. '
                    . 'When this option is not provided, P1 will be played by a human.',
                null
            )
            ->addOption(
                'p2-level',
                null,
                InputOption::VALUE_REQUIRED,
                'Set P2 to be played by the CPU of the provided difficulty [1-3]. '
                    . 'When this option is not provided, P2 will be played by a human.',
                null
            )
            ->addOption(
                'p1-mark',
                null,
                InputOption::VALUE_REQUIRED,
                'Set the symbol to use on the board to represent P1.',
                'X'
            )
            ->addOption(
                'p2-mark',
                null,
                InputOption::VALUE_REQUIRED,
                'Set the symbol to use on the board to represent P2.',
                'O'
            )
        ;
    }

    /**
     * Execute this command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input The input
     *     interface provides access to args, options and interactive input.
     * @param \Symfony\Component\Console\Output\OutputInterface $output The
     *     output interface provides access to write to the console.
     * @return int Zero on successful execution, >0 on error.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $p1 = $this->newPlayerFromArgs(1, $input, $output);
        $p2 = $this->newPlayerFromArgs(2, $input, $output);

        $game = new Game($p1, $p2);
        $winner = $game->run($output);

        $footer = [
            '',
            '=====================',
        ];

        if ($winner === true) {
            $footer[] = 'Game over: Cat\'s game.';
        } else {
            $footer[] = sprintf('Game over: %s wins!', $winner);
        }

        $output->writeln($footer);
        $output->write($game->printBoard());
        $output->writeln('');
    }

    /**
     * Helper factory for constructing a new Player object using the available
     * command line options.
     *
     * @param int $num The player number. Currently only `1` and `2` are
     *     supported. Used to fetch command line args.
     * @param \Symfony\Component\Console\Input\InputInterface $input The input
     *     interface provides access to command line args, options and
     *     interactive input.
     * @param \Symfony\Component\Console\Output\OutputInterface $output The
     *     output interface provides access to write to the console.
     * @return \Beporter\Tictactoe\Players\Player The initialized Player object.
     */
    protected function newPlayerFromArgs(int $num, InputInterface $input, OutputInterface $output): Player
    {
        $cpuLevel = intval($input->getOption("p{$num}-level"));
        $playerMark = (string)$input->getOption("p{$num}-mark");
        $playerMark = mb_substr($playerMark, 0, 1, 'utf-8');

        if ($cpuLevel) {
            $player = (new CpuPlayer($playerMark))->setDifficulty($cpuLevel);
        } else {
            $moveFetcher = new FetchMove($playerMark, $input, $output, $this->getHelper('question'));
            $player = (new HumanPlayer($playerMark))->setAsker($moveFetcher);
        }

        return $player;
    }
}
