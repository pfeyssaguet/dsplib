<?php

/**
 * Test of the funcLang method from Template class
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since  7 avr. 2013 10:03:00
 */

namespace DspLib\Test;

use DspLib\Template;
use DspLib\Language;

class TemplateFuncLangTest extends \PHPUnit_Framework_TestCase
{
    private $aTempFiles = array();

    private function getTempFile()
    {
        $sFilePath = tempnam('.', 'test.Template.');
        $this->aTempFiles[] = $sFilePath;
        return $sFilePath;
    }

    public function setUp()
    {
        $sFilePath = __DIR__ . '/fr.xml';
        $this->aTempFiles[] = $sFilePath;

        $sXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<language name="Français">
    <term name="MANGER">Manger</term>
    <term name="BOIRE">Boire</term>
</language>
XML;
        file_put_contents($sFilePath, $sXml);

        $sFilePath = __DIR__ . '/en.xml';
        $this->aTempFiles[] = $sFilePath;

        $sXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<language name="English">
    <term name="MANGER">Eat</term>
    <term name="BOIRE">Drink</term>
</language>
XML;
        file_put_contents($sFilePath, $sXml);

        Language::addPath(__DIR__);
    }
    public function tearDown()
    {
        Language::resetStatics();
        Template::clearDefaultParams();
        foreach ($this->aTempFiles as $sFilePath) {
            unlink($sFilePath);
        }
    }

    public function testFuncLang()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{lang:MANGER}');

        $oTemplate = new Template($sFilePath);

        $sActualResult = $oTemplate->render();

        $sExpectedResult = 'Manger';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testFuncLangWithOtherLanguage()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{lang:MANGER,en}');

        $oTemplate = new Template($sFilePath);

        $sActualResult = $oTemplate->render();

        $sExpectedResult = 'Eat';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testFuncLangWithOtherCurrentLanguage()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{lang:MANGER}');

        $oTemplate = new Template($sFilePath);
        Language::setCurrentLanguage('en');

        $sActualResult = $oTemplate->render();

        $sExpectedResult = 'Eat';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testFuncLangCallback()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{lang:DORMIR}');

        $oTemplate = new Template($sFilePath);

        $oTemplate->setCallbackLang(array(__CLASS__, 'customCallback'));

        $sActualResult = $oTemplate->render();

        $sExpectedResult = '==DORMIR==';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public static function customCallback($sParam)
    {
        return '=='.$sParam.'==';
    }
}
