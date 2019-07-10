# Tic Tac Toe

A command line implementation of [Tic Tac Toe](https://en.wikipedia.org/wiki/Tic-tac-toe).

[![CircleCI](https://circleci.com/gh/beporter/php-tictactoe.svg?style=svg)](https://circleci.com/gh/beporter/php-tictactoe)


## Requirements

* [php](https://secure.php.net/downloads.php) v7.1+
    * `mbstring` extension


## Usage

Play the game with `./play.sh`. This script will prompt you to configure available options.


### Advanced usage

Available `tictactoe.php` command line options:
* `--p1-level 1|2|3`
* `--p2-level 1|2|3`
* `--p1-mark X`
* `--p2-mark O`

Setting a difficulty level for any player will cause that player to be controlled by the CPU.

Examples:
* `php tictactoe.php  # Defaults used: P1=human (X), P2=human (O)`
* `php tictactoe.php --p1-mark 🍺 --p2-mark 🍶  # Human vs human (beer vs sake)`
* `php tictactoe.php --p1-level 1 --p2-level 3  # Easy cpu vs hard cpu`


## Development

* [git](https://git-scm.com/)
* [composer](https://getcomposer.org/download/)
* [PHP's xdebug extension](https://xdebug.org/docs/install)


### Getting the Code

Run: `git clone https://github.com/beporter/php-tictactoe.git`


### Writing Tests

* Each class in `src/` should have a corresponding [PHPUnit test class](https://phpunit.readthedocs.io/en/7.1/writing-tests-for-phpunit.html) in `tests/`.


### Running Tests

Run once: `composer install`

To execute tests: `vendor/bin/phpunit`

To produce code coverage: `vendor/bin/phpunit --coverage-html tmp/coverage/`

To view the coverage report, open `tmp/coverage/index.html` in a web browser.


### Static Analysis

Run `vendor/bin/phpstan analyse -l max src`


### Coding Standards

This project follows [PSR-2](https://www.php-fig.org/psr/psr-2/) for coding style standards and [PSR-4](https://www.php-fig.org/psr/psr-4/) for class autoloading.

To verify the code against the standards: `vendor/bin/phpcs`


### Generating Docs

The project informally uses [phpDocumentor](https://www.phpdoc.org/)-compatible syntax. To validate proper usage, you can [install the phpdoc phar](https://docs.phpdoc.org/getting-started/installing.html#phar). (You should have the `intl` PHP extension and [GraphViz](https://www.graphviz.org/) installed on your development system.)

Assuming the executable is in your `$PATH`, run `$ phpdoc` from this directory, which will use the `phpdoc.dist.xml` file for configuration.

Once generated, you can open `tmp/docs/index.html` in your browser to review the documentation.
