<?php
/**
 * Define an entry point for the Tic-Tac-Toe game in terms of a Symfony
 * CLI-based Application and Command.
 *
 * The `symfony/console` package provides an interface for handling command
 * line arguments and options as well as for generating help text. Neither of
 * those are domain concepts we need to concern ourselves with directly, so we
 * farm that work out to a popular, well-tested package instead.
 */

declare(strict_types=1);

namespace Beporter\Tictactoe;

use Beporter\Tictactoe\TicTacToeCommand;
use Symfony\Component\Console\Application;

/**
 * \Beporter\Tictactoe\TicTacToeApplication
 */
class TicTacToeApplication
{
    /**
     * The Application's declared name. Used in help text output.
     *
     * @var string
     */
    const APP_NAME = 'tic-tac-toe';

    /**
     * The Application's declared version. Used in version text output.
     *
     * @var string
     */
    const APP_VERSION = '1.0.0';

    /**
     * The Symfony Application instance.
     *
     * @var \Symfony\Component\Console\Application
     */
    protected $app = null;

    /**
     * Initialize the Symfony Application instance and inject the
     * TicTacToeCommand instance into it.
     */
    public function __construct()
    {
        // Set up a single-command Application.
        $command = new TicTacToeCommand();
        $this->app = new Application(self::APP_NAME, self::APP_VERSION);
        $this->app->add($command);
        $this->app->setDefaultCommand($command->getName(), true);

        // Remove unhelpful/unnecessary command line options provided by Symfony out of the box.
        $options = $this->app->getDefinition()->getOptions();
        unset($options['quiet']);
        unset($options['verbose']);
        unset($options['ansi']);
        unset($options['no-ansi']);
        unset($options['no-interaction']);
        $this->app->getDefinition()->setOptions($options);
    }

    /**
     * Execute the Symfony application.
     *
     * @return int Zero on successful execution, >0 on error.
     */
    public function run()
    {
        return $this->app->run();
    }
}
