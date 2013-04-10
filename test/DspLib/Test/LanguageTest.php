<?php

/**
 * Language test class
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since 5 avr. 2013 23:43:39
 */

namespace DspLib\Test;

use DspLib\Language;

class LanguageTest extends \PHPUnit_Framework_TestCase
{
    private $aTempFiles = array();

    public function setUp()
    {
        Language::resetStatics();
    }

    public function tearDown()
    {
        foreach ($this->aTempFiles as $sFilePath) {
            unlink($sFilePath);
        }
    }

    public function testGetDefaultLanguage()
    {
        $sActualLanguage = Language::getDefaultLanguage();
        $sExpectedLanguage = 'fr';

        $this->assertEquals($sExpectedLanguage, $sActualLanguage);
    }

    public function testSetDefaultLanguage()
    {
        Language::setDefaultLanguage('en');
        $sActualLanguage = Language::getDefaultLanguage();
        $sExpectedLanguage = 'en';

        $this->assertEquals($sExpectedLanguage, $sActualLanguage);
    }

    public function testGetCurrentLanguage()
    {
        $sActualLanguage = Language::getCurrentLanguage();
        $sExpectedLanguage = 'fr';

        $this->assertEquals($sExpectedLanguage, $sActualLanguage);
    }

    public function testSetCurrentLanguage()
    {
        Language::setCurrentLanguage('en');
        $sActualLanguage = Language::getCurrentLanguage();
        $sExpectedLanguage = 'en';

        $this->assertEquals($sExpectedLanguage, $sActualLanguage);
    }

    public function testGetString()
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

        Language::addPath(__DIR__);

        $sActual = Language::getString('MANGER');
        $sExpected = 'Manger';

        $this->assertEquals($sExpected, $sActual);

        $sActual = Language::getString('BOIRE');
        $sExpected = 'Boire';

        $this->assertEquals($sExpected, $sActual);
    }

    public function testGetStringInBothLanguages()
    {
        $sFilePath = __DIR__ . '/fr.xml';
        $this->aTempFiles[] = $sFilePath;

        $sXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<language name="Français">
    <term name="MANGER">Manger</term>
</language>
XML;
        file_put_contents($sFilePath, $sXml);

        $sFilePath = __DIR__ . '/en.xml';
        $this->aTempFiles[] = $sFilePath;

        $sXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<language name="English">
    <term name="MANGER">Eat</term>
</language>
XML;
        file_put_contents($sFilePath, $sXml);

        Language::addPath(__DIR__);

        $sActual = Language::getString('MANGER');
        $sExpected = 'Manger';

        $this->assertEquals($sExpected, $sActual);

        $sActual = Language::getString('MANGER', 'en');
        $sExpected = 'Eat';

        $this->assertEquals($sExpected, $sActual);
    }

    public function testGetStringWithMissingTranslation()
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
</language>
XML;
        file_put_contents($sFilePath, $sXml);

        Language::addPath(__DIR__);

        $this->setExpectedException('\PHPUnit_Framework_Error_Notice');
        Language::getString('BOIRE', 'en');
    }

    public function zzztestGetStringWithMissingTranslationAfterTriggerError()
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
</language>
XML;
        file_put_contents($sFilePath, $sXml);

        Language::addPath(__DIR__);

        $sActual = @Language::getString('BOIRE', 'en');

        $sExpected = 'Boire';

        $this->assertEquals($sExpected, $sActual);
    }

    public function testGetStringMissingTerm()
    {
        $sFilePath = __DIR__ . '/fr.xml';
        $this->aTempFiles[] = $sFilePath;

        $sXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<language name="Français">
    <term name="MANGER">Manger</term>
</language>
XML;
        file_put_contents($sFilePath, $sXml);

        Language::addPath(__DIR__);

        $this->setExpectedException('\PHPUnit_Framework_Error_Notice');

        Language::getString('MANGERR');
    }

    public function testGetStringMissingTermAfterTriggerError()
    {
        $sFilePath = __DIR__ . '/fr.xml';
        $this->aTempFiles[] = $sFilePath;

        $sXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<language name="Français">
    <term name="MANGER">Manger</term>
</language>
XML;
        file_put_contents($sFilePath, $sXml);

        Language::addPath(__DIR__);

        $sActual = @Language::getString('MANGERR');
        $this->assertEquals('MANGERR', $sActual);
    }

    public function testGetStringMissingTermWithDebug()
    {
        $sFilePath = __DIR__ . '/fr.xml';
        $this->aTempFiles[] = $sFilePath;

        $sXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<language name="Français">
    <term name="MANGER">Manger</term>
</language>
XML;
        file_put_contents($sFilePath, $sXml);

        Language::setDebug(true);
        Language::addPath(__DIR__);

        $this->setExpectedException('\Exception');

        $sActual = Language::getString('MANGERR');
    }

    public function testGetStringMissingFile()
    {
        Language::addPath(__DIR__);

        $this->setExpectedException('\Exception');

        $sActual = Language::getString('MANGER');
    }

    public function testGetStringMissingLanguageFile()
    {
        Language::addPath(__DIR__);
        $this->setExpectedException('\Exception');

        $sActual = Language::getString('MANGER', 'zz');
    }
}
