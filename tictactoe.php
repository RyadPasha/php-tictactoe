<?php
/**
 * Tic-Tac-Toe main() entry point. Imports the composer autoloader, then
 * creates and executes the TicTacToeApplication instance. This file is kept
 * as small as possible to keep the difficult-to-test surface area of the
 * project low.
 *
 * Execute the game using `php tictactoe.php`. See the local README.md for details.
 */

require 'vendor/autoload.php';

use Beporter\Tictactoe\TicTacToeApplication;

(new TicTacToeApplication)->run();
