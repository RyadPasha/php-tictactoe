<?php
/**
 * Tests for the FetchMove class.
 */

namespace Beporter\Tictactoe\Tests;

use Beporter\Tictactoe\Boards\Board;
use Beporter\Tictactoe\FetchMove;
use Beporter\Tictactoe\Tests\ReflectionHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Question\Question;

/**
 * \Beporter\Tictactoe\FetchMoveTest
 */
class FetchMoveTest extends TestCase
{
    use ReflectionHelperTrait;

    /**
     * Perform set up tasks before each test method.
     */
    public function setUp()
    {
        $this->mark = 'M';
        $this->Board = new Board();
        $this->Board[0] = 'TAKEN';

        $this->Input = $this->getMockBuilder(Input::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->Output = $this->getMockBuilder(Output::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->Helper = $this->getMockBuilder(QuestionHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->FetchMove = new FetchMove($this->mark, $this->Input, $this->Output, $this->Helper);
    }

    /**
     * Perform tear down tasks after each test method.
     */
    public function tearDown()
    {
        unset($this->FetchMove);
        unset($this->Helper);
        unset($this->Output);
        unset($this->Input);
        unset($this->Board);
        unset($this->mark);
    }

    /**
     * Test ::__invoke().
     */
    public function testInvoke()
    {
        $this->Helper->expects($this->once())
            ->method('ask')
            ->with(
                $this->Input,
                $this->Output,
                $this->callback([$this, 'assertValidator'])
            )
            ->willReturn(42); // return type must be int.

        $this->assertEquals(
            42,
            $this->FetchMove->__invoke($this->Board),
            'The method return value should be that of the mocked Question::ask().'
        );
    }

    /**
     * This method is used by ::testInvoke() to test the Question object
     * passed as the third argument to Question::ask().
     *
     * @param \Symfony\Component\Console\Question\Question $question
     * @return bool True if validation of the param passed.
     */
    public function assertValidator(Question $question)
    {
        $this->assertInstanceOf(Question::class, $question);

        // Extract the anonymous validator function from the Question for further testing.
        $validator = $question->getValidator();

        $this->assertEquals(
            1,
            $validator(1),
            'The #1 spot in the Board should be open.'
        );

        return true;
    }

    /**
     * Test the anonymous function created by ::buildValidator() when trying
     * to play on a non-empty spot in the Board.
     */
    public function testValidator()
    {
        $validator = $this->invokeMethod($this->FetchMove, 'buildValidator', [$this->Board]);
        $this->expectException('\RuntimeException');
        $this->expectExceptionMessage('Please select an open square');
        $validator(0);
    }
}
