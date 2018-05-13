<?php
/**
 * Tests for the TicTacToeCommand class.
 */

namespace Beporter\Tictactoe\Tests;

use Beporter\Tictactoe\Players\CpuPlayer;
use Beporter\Tictactoe\Tests\ReflectionHelperTrait;
use Beporter\Tictactoe\TicTacToeCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * \Beporter\Tictactoe\Tests\TicTacToeCommandTest
 */
class TicTacToeCommandTest extends TestCase
{
    use ReflectionHelperTrait;

    /**
     * Perform set up tasks before each test method.
     */
    public function setUp()
    {
        $this->Input = $this->getMockBuilder(Input::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->Output = $this->getMockBuilder(Output::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->QuestonHelper = $this->getMockBuilder(QuestionHelper::class)
            ->getMock();
        $this->Command = new TicTacToeCommand();
        $this->Command->setApplication(new Application());
        $this->Command->getHelperSet()->set($this->QuestonHelper, 'question');
        $this->CommandTester = new CommandTester($this->Command);
    }

    /**
     * Perform tear down tasks after each test method.
     */
    public function tearDown()
    {
        unset($this->CommandTester);
        unset($this->Command);
        unset($this->QuestonHelper);

        unset($this->Output);
        unset($this->Input);
    }

    /**
     * Test ::configure().
     */
    public function testConfigure()
    {
        $expected = [
            'p1-mark',
            'p2-mark',
            'p1-level',
            'p2-level',
        ];

        $actual = array_keys($this->Command->getDefinition()->getOptions());

        // PHPUnit sure needs a better way to compare array values without
        // taking key order into account. :eyeroll:
        $this->assertEquals(
            0,
            count(array_diff($expected, $actual)),
            'The set of available options should match our expectations.'
        );
        $this->assertEquals(
            0,
            count(array_diff($actual, $expected)),
            'The set of available options should match our expectations.'
        );

        $this->assertEquals(
            'tic-tac-toe',
            $this->Command->getName(),
            'The command name must match our expectation exactly.'
        );
        $this->assertContains(
            'Play Tic-Tac-Toe with human or CPU players.',
            $this->Command->getDescription(),
            'The help text should include the expected text snippet.'
        );
    }

    /**
     * Test ::execute().
     *
     * @param array $moves A set of interleaved X/O moves to execute.
     * @param string $expected The expected substring to find in the console output.
     * @param string $msg Optional PHPUnit assertion failure message.
     * @dataProvider provideExecuteArgs
     */
    public function testExecute(array $moves, $expected, $msg = '')
    {
        $this->QuestonHelper->expects($this->atLeastOnce())
            ->method('ask')
            ->will($this->onConsecutiveCalls(...$moves));

        $this->CommandTester->execute([]);

        $this->assertContains(
            $expected,
            $this->CommandTester->getDisplay(),
            $msg
        );
    }

    /**
     * Provide move sets and expected output to ::testExecute().
     *
     * @return array Sets of [moves, expected, msg].
     */
    public function provideExecuteArgs()
    {
        return [
            [
                [ // Interleaved X/O moves.
                    // Move number => position
                    0 => '4', // X
                    1 => '5', // O
                    2 => '1', // X
                    3 => '7', // O
                    4 => '0', // X
                    5 => '8', // O
                    6 => '2', // X (win)
                ],
                /*
                Visually:
                 X(4) | X(2) | X(6) <-- win
                ------+------+------
                      | X(0) | O(1)
                ------+------+------
                      | O(3) | O(5)
                */
                'Game over: X wins!',
                'X should be declared the winner',
            ],

            [
                [
                    0 => '4', // X
                    1 => '5', // O
                    2 => '1', // X
                    3 => '7', // O
                    4 => '8', // X
                    5 => '0', // O
                    6 => '2', // X
                    7 => '6', // O
                    8 => '3', // X (tie)
                ],
                /*
                Visually:
                 O(5) | X(2) | X(6)
                ------+------+------
                 X(8) | X(0) | O(1)
                ------+------+------
                 O(7) | O(3) | X(4)
                */
                'Game over: Cat\'s game',
                'Game should result in a tie.',
            ],
        ];
    }

    /**
     * Test ::newPlayerFromArgs() separately to cover CPU cases not covered
     * by ::testExecute() above.
     */
    public function testNewPlayerFromArgs()
    {
        $mark = 'too long';
        $difficulty = 2;
        $this->Input->expects($this->at(0))
            ->method('getOption')
            ->with('p1-level')
            ->willReturn($difficulty);
        $this->Input->expects($this->at(1))
            ->method('getOption')
            ->with('p1-mark')
            ->willReturn($mark);

        $player = $this->invokeMethod(
            $this->Command,
            'newPlayerFromArgs',
            [1, $this->Input, $this->Output]
        );

        $this->assertInstanceOf(
            CpuPlayer::class,
            $player,
            'The returned Player should be a CPU due to pressence of `--p1-level=2`.'
        );
        $this->assertEquals(
            mb_substr($mark, 0, 1, 'utf-8'),
            $player->getMark(),
            'The Player mark should be the trimmed version of the command line option value.'
        );
        $this->assertEquals(
            $difficulty,
            $this->getProperty($player, 'difficulty'),
            'The CpuPlayer::$difficulty should match the command line option value.'
        );
    }
}
