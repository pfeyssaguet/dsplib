<?php

/**
 * Log test class
 *
 * @package Test
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since   10 avr. 2013 09:03:20
 */

namespace DspLib\Test;

use DspLib\Log;

/**
 * Log test class
 *
 * @package Test
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since   10 avr. 2013 09:03:20
 */
class LogTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Log::resetStatics();
    }

    public function testSetLevel()
    {
        Log::setLevel(Log::LEVEL_NOTICE);
        $iActual = Log::getLevel();

        $this->assertEquals(Log::LEVEL_NOTICE, $iActual);
    }

    public function testSetCategoryLevel()
    {
        Log::setCategoryLevel('LogTest', Log::LEVEL_NOTICE);

        $this->assertEquals(Log::LEVEL_NOTICE, Log::getCategoryLevel('LogTest'));
        $this->assertEquals(Log::LEVEL_INFO, Log::getCategoryLevel(''));
    }

    public function testIsLogActive()
    {
        Log::setLevel(Log::LEVEL_WARNING);
        Log::setCategoryLevel('LogTest', Log::LEVEL_NOTICE);

        $this->assertTrue(Log::isLogActive('LogTest', Log::LEVEL_NOTICE));
        $this->assertTrue(Log::isLogActive('LogTest', Log::LEVEL_WARNING));

        $this->assertFalse(Log::isLogActive('', Log::LEVEL_NOTICE));
    }

    public function testAddError()
    {
        Log::addError('LogTest', 'Test error');
        $aActual = Log::getMessages();
        $aExpected = array(
            array(
                'timestamp' => date('Y-m-d H:i:s'),
                'message' => 'Test error',
                'category' => 'LogTest',
                'level' => Log::LEVEL_ERROR,
            )
        );
        $this->assertEquals($aExpected, $aActual);
    }

    public function testAddWarning()
    {
        Log::addWarning('LogTest', 'Test warning');
        $aActual = Log::getMessages();
        $aExpected = array(
            array(
                'timestamp' => date('Y-m-d H:i:s'),
                'message' => 'Test warning',
                'category' => 'LogTest',
                'level' => Log::LEVEL_WARNING,
            )
        );
        $this->assertEquals($aExpected, $aActual);
    }

    public function testAddNotice()
    {
        Log::addNotice('LogTest', 'Test notice');
        $aActual = Log::getMessages();
        $aExpected = array(
            array(
                'timestamp' => date('Y-m-d H:i:s'),
                'message' => 'Test notice',
                'category' => 'LogTest',
                'level' => Log::LEVEL_NOTICE,
            )
        );
        $this->assertEquals($aExpected, $aActual);
    }

    public function testAddMessageOutOfLevelRange()
    {
        Log::setLevel(Log::LEVEL_ERROR);
        Log::addNotice('LogTest', 'Test notice');
        $aActual = Log::getMessages();

        $this->assertEmpty($aActual);
    }
}
