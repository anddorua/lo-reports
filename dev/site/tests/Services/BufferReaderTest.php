<?php
/**
 * Created by PhpStorm.
 * User: andriy
 * Date: 09.12.16
 * Time: 16:06
 */

namespace Test\Services;


class BufferReaderTest extends \PHPUnit_Framework_TestCase
{

    public function testAdd()
    {
        $br = new \App\Services\BufferReader();
        $tmp = '';
        $br->add("Hello", function($str){ $this->assertTrue(false, 'this code should not be executed'); });
        $br->add("Hello\nAg", function($str) use (&$tmp) { $tmp = $str; });
        $this->assertEquals("HelloHello\n", $tmp);
        $br->add("ain", function($str){ $this->assertTrue(false, 'this code should not be executed'); });
        $br->add("Again\nTail", function($str) use (&$tmp) { $tmp = $str; });
        $this->assertEquals("AgainAgain\n", $tmp);
        $this->assertEquals("HelloHello\nAgainAgain\nTail", $br->getBuffer());
    }
}
