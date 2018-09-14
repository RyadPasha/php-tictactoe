<?php
/**
 * Tests for the HumanPlayer class.
 */

namespace Beporter\Tictactoe\Tests\Players;

use Beporter\Tictactoe\Game;
use Beporter\Tictactoe\Players\HumanPlayer;
use Beporter\Tictactoe\Tests\ReflectionHelperTrait;
use PHPUnit\Framework\TestCase;

/**
 * \Beporter\Tictactoe\Tests\Players\HumanPlayerTest
 */
class HumanPlayerTest extends TestCase
{
    use ReflectionHelperTrait;

    /**
     * Perform set up tasks before each test method.
     */
    public function setUp()
    {
        $this->mark = 'X';
        $this->asker = function () {
            return '42';
        };
        $this->Player = (new HumanPlayer($this->mark))->setAsker($this->asker);
        $this->Game = new Game($this->Player, $this->Player);
    }

    /**
     * Perform tear down tasks after each test method.
     */
    public function tearDown()
    {
        unset($this->Game);
        unset($this->Player);
        unset($this->asker);
        unset($this->mark);
    }

    /**
     * Test ::setAsker().
     */
    public function testSetAsker()
    {
        $this->assertEquals(
            $this->Player,
            $this->Player->setAsker($this->asker),
            'Setting the asker should return the Player instance.'
        );

        $this->assertEquals(
            $this->asker,
            $this->getProperty($this->Player, 'asker'),
            'The ::$asker property should be assigned as we expect.'
        );
    }

    /**
     * Test ::getBestMove().
     */
    public function testGetBestMove()
    {
        $this->assertEquals(
            42, // Should always be an int.
            $this->Player->getBestMove($this->Game),
            'The move should be the return value from the asker anon func.'
        );
    }

    /**
     * Test ::getBestMove() when no ::$asker has been set yet.
     */
    public function testGetBestMoveWithoutAsker()
    {
        $this->expectException('\BadMethodCallException');
        $this->expectExceptionMessage('HumanPlayer::$asker is not callable');

        (new HumanPlayer($this->mark))->getBestMove($this->Game);
    }
}
