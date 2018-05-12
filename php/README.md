# Tic Tac Toe

A command line implementation of [Tic Tac Toe](https://en.wikipedia.org/wiki/Tic-tac-toe).


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

Run: `git clone git@github.com:beporter/coding_challenges.git`


### Writing Tests

* Each class in `src/` should have a corresponding [PHPUnit test class](https://phpunit.readthedocs.io/en/7.1/writing-tests-for-phpunit.html) in `tests/`.


### Running Tests

Run once: `composer install`

To execute tests: `vendor/bin/phpunit`

To produce code coverage: `vendor/bin/phpunit --coverage-html tmp/coverage/`

To view the coverage report, open `tmp/coverage/index.html` in a web browser.


### Coding Standards

This project follows [PSR-2](https://www.php-fig.org/psr/psr-2/) for coding style standards and [PSR-4](https://www.php-fig.org/psr/psr-4/) for class autoloading.

To verify the code against the standards: `vendor/bin/phpcs`


### Generating Docs

The project informally uses [phpDocumentor](https://www.phpdoc.org/)-compatible syntax. To validate proper usage, you can [install the phpdoc phar](https://docs.phpdoc.org/getting-started/installing.html#phar). (You should have the `intl` PHP extension and [GraphViz](https://www.graphviz.org/) installed on your development system already.)

Assuming the executable is in your `$PATH`, run `$ phpdoc` from this directory, which will use the `phpdoc.dist.xml` file for configuration.

Once generated, you can open `tmp/docs/index.html` in your browser to review the documentation.


## Coding Challenge Notes

### Email invitation to complete the challenge

<blockquote>
Hi Brian,

Thank you for submitting your application. We're excited about the possibility of you joining our team! I want to encourage you to ask any questions you have about our company, our approach to software development, or our apprenticeship program. I would also like to explain the next step in the process.

All applicants are asked to submit a sample of their code. Attached is a document that explains the problem we would like you to solve. We've added you as a collaborator to [this repository](https://github.com/8thlight/coding_challenges). Please follow the instructions in the [README](../README.md) and email me a link to the pull request when you're done. There isn't a deadline. Your submission should represent your best and final work.

Your code will be judged on the following criteria:

* Adherence to SOLID principles
* Appropriate separation of concerns
* Clarity and readability
* Expressive naming

Please let me know if you have any questions. I look forward to hearing from you!

Best,
Rob Mulholand
8th Light
</blockquote>


### Copy of `problem_description.pdf` contents

<blockquote>
Hello,

I'm the project manager at a Command Line Games, Inc. I have a small dev team and we hired a consulting company to help us build an app that will feature a number of games for children, one being Tic Tac Toe.

They just demoed the basic version of the Tic Tac Toe game in the console and my boss wasn't thrilled with what he saw. The game play was rough. It didn't function as he expected. We've decided to move in a different direction and bring in someone else. While my boss doesn't have a technical background, I do, and we both understand the importance of writing code that can be maintained in the future.

We would like you to improve the existing Tic Tac Toe that the previous firm worked on. There are a number of issues with the code. Below I've listed some of those issues, but I'm sure there are more.

* The game does not gracefully handle bad user input.
* In its current form, it's supposed to be played at a difficulty level of "hard", meaning the computer player cannot be beaten, but in reality you can beat it with the right moves.
* The game play left a lot to be desired. The user messages are lacking in many ways, which I'm sure you can tell.

As you can tell, there are a lot of problems and from what our devs say, the code itself is a mess. This puts us in a difficult position because we have a number of features we would like to add and we're hoping you can help. We hope that you'll be able to help us get the code in a better state. Without that, our devs don't even think we'll be able to implement the new features my boss has requested. Below you'll see a list of the features we're hoping to add.

* Allow the user to choose the level of difficulty ("easy" means the computer can easily be beaten, "medium" means it can be beaten but only with a series of intelligent moves, and "hard" means the it is unbeatable).
* Allow the user to choose the game type (human v. human, computer v. computer, human v. computer).
* Allow the user to choose which player goes first.
* Allow the user to choose with what "symbol" the players will mark their selections on the board (traditionally it's "X" and "O").

Could you implement these features?

Thanks for your help,
Ryan
</blockquote>


### Submission steps

- [x] Fork this repo.
- [ ] In your forked repo, go to *Settings* and click *Collaborators & teams*. At the bottom, add `8th-light-reviewer` as a collaborator.
- [x] Clone the forked repo.
- [x] Create a branch off `master` called `code-submission`.
- [x] Select the language you would like to use from those available.
- [x] Submit a pull request to merge the `code-submission` branch **in your forked repo** into the `master` branch **in your forked repo**.
   - [x] The base to which you compare your changes - and ultimately submit your pull request - should be the `master` branch in your forked repo. It should **not** show `8thlight/coding_challenges` as your base fork.
