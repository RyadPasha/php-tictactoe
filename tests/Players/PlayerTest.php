<?php
/**
 * Tests for the Player base class.
 */

namespace Beporter\Tictactoe\Tests\Players;

use Beporter\Tictactoe\Players\Player;
use Beporter\Tictactoe\Tests\ReflectionHelperTrait;
use PHPUnit\Framework\TestCase;

/**
 * \Beporter\Tictactoe\Tests\Players\PlayerTest
 */
class PlayerTest extends TestCase
{
    use ReflectionHelperTrait;

    /**
     * Perform set up tasks before each test method.
     */
    public function setUp()
    {
        $this->Player = $this->getMockForAbstractClass(Player::class, ['🦄']);
    }

    /**
     * Perform tear down tasks after each test method.
     */
    public function tearDown()
    {
        unset($this->Player);
    }

    /**
     * Test ::getMark() and Unicode support.
     */
    public function testGetMark()
    {
        $this->assertEquals(
            '🦄',
            $this->Player->getMark(),
            'The Player mark should be the one assigned at instantiation.'
        );
    }

    /**
     * Test ::setMark().
     */
    public function testSetMark()
    {
        $newMark = 'blah';
        $this->invokeMethod($this->Player, 'setMark', [$newMark]);

        $this->assertEquals(
            $newMark,
            $this->Player->getMark(),
            'The Player mark should be the freshly assigned one.'
        );
    }
}
