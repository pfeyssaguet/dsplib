<?php

/**
 * FileUtils test class
 *
 * @package Test
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since   6 avr. 2013 10:05:57
 */

namespace DspLib\Test;

use DspLib\FileUtils;

/**
 * FileUtils test class
 *
 * @package Test
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since   6 avr. 2013 10:05:57
 */
class FileUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        mkdir(__DIR__ . '/testFileUtils/dir1', 0777, true);
        mkdir(__DIR__ . '/testFileUtils/dir2', 0777, true);
        mkdir(__DIR__ . '/testFileUtils/dir3', 0777, true);
        file_put_contents(__DIR__ . '/testFileUtils/dir1/test1.txt', 'test1');
        file_put_contents(__DIR__ . '/testFileUtils/dir1/test2.txt', 'test2');
        file_put_contents(__DIR__ . '/testFileUtils/dir1/test3.txt', 'test3');
    }

    public function tearDown()
    {
        unlink(__DIR__ . '/testFileUtils/dir1/test1.txt');
        unlink(__DIR__ . '/testFileUtils/dir1/test2.txt');
        unlink(__DIR__ . '/testFileUtils/dir1/test3.txt');
        rmdir(__DIR__ . '/testFileUtils/dir1');
        rmdir(__DIR__ . '/testFileUtils/dir2');
        rmdir(__DIR__ . '/testFileUtils/dir3');
        rmdir(__DIR__ . '/testFileUtils');
    }

    public function testGetDirs()
    {
        $aActualDirs = FileUtils::getDirs(__DIR__ . '/testFileUtils');
        $aExpectedDirs = array(
            __DIR__ . '/testFileUtils/dir1',
            __DIR__ . '/testFileUtils/dir2',
            __DIR__ . '/testFileUtils/dir3',
        );
        sort($aActualDirs);
        sort($aExpectedDirs);
        $this->assertEquals($aExpectedDirs, $aActualDirs);
    }

    public function testGetDirsWithFilter()
    {
        $aActualDirs = FileUtils::getDirs(__DIR__ . '/testFileUtils', '#dir1#');
        $aExpectedDirs = array(
            __DIR__ . '/testFileUtils/dir1',
        );
        sort($aActualDirs);
        sort($aExpectedDirs);
        $this->assertEquals($aExpectedDirs, $aActualDirs);
    }


    public function testGetDirsWithDirWhichIsFile()
    {
        $this->setExpectedException('InvalidArgumentException');
        FileUtils::getDirs(__DIR__ . '/testFileUtils/dir1/test1.txt');
    }


    public function testGetDirsWithDirWhichDoesNotExists()
    {
        $this->setExpectedException('InvalidArgumentException');
        FileUtils::getDirs(__DIR__ . '/testFileUtils/dir4');
    }

    public function testGetFiles()
    {
        $aActualFiles = FileUtils::getFiles(__DIR__ . '/testFileUtils/dir1');
        $aExpectedFiles = array(
            __DIR__ . '/testFileUtils/dir1/test1.txt',
            __DIR__ . '/testFileUtils/dir1/test2.txt',
            __DIR__ . '/testFileUtils/dir1/test3.txt',
        );
        sort($aActualFiles);
        sort($aExpectedFiles);
        $this->assertEquals($aExpectedFiles, $aActualFiles);
    }

    public function testGetFilesWithDirWhichIsFile()
    {
        $this->setExpectedException('InvalidArgumentException');
        FileUtils::getFiles(__DIR__ . '/testFileUtils/dir1/test1.txt');
    }

    public function testGetFilesWithFileWhichDoesNotExists()
    {
        $this->setExpectedException('InvalidArgumentException');
        FileUtils::getFiles(__DIR__ . '/testFileUtils/dir1/test4.txt');
    }
}
