<?php
/**
 * Define an empty Symfony Command class to process command line args & user I/O
 * and eventually execute the game logic.
 */

namespace Beporter\Tictactoe;

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
        ;
    }

    /**
     * Execute this command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input The input
     *   interface provides access to args, options and interactive input.
     * @param \Symfony\Component\Console\Input\OutputInterface $output The
     *   output interface provides access to write to the console.
     * @return int Zero on successful execution, >0 on error.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('@TODO: Implement.');
    }
}
