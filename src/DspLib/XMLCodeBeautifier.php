<?php

namespace DspLib;

/**
 * Permet de formater un code XML en HTML avec couleurs
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */
class XMLCodeBeautifier
{
    protected static $aColors;

    /**
     * Renders an XML string with colors based on the standard colors
     * of the PHP core highlight functions
     *
     * @param string $sString The XML string to highlight
     *
     * @return string The colored XML string
     */
    public static function formatCode($sString)
    {
        // load ini values if necessary
        if (!isset(self::$aColors)) {
            self::$aColors = array(
                'string' => ini_get('highlight.string'),
                'comment' => ini_get('highlight.comment'),
                'keyword' => ini_get('highlight.keyword'),
                'default' => ini_get('highlight.default'),
                'html' => ini_get('highlight.html')
            );
        }

        $start1 = '<span style="color: ';
        $start2 = '">';
        $end = '</span>';

        // set values for XML
        $sColTagXml = $start1 . self::$aColors['default'] . $start2;
        $sColTag = $start1 . self::$aColors['default'] . $start2;
        $sColElement = $start1 . self::$aColors['comment'] . $start2;
        $sColAttribute = $start1 . self::$aColors['keyword'] . $start2;
        $sColAttributeValue = $start1 . self::$aColors['string'] . $start2;

        // first correct the indentation
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        if (@$dom->loadXML($sString)) {
            $sString = $dom->saveXML();
        }

        $sString = htmlspecialchars($sString);
        $sString = str_replace('  ', '&nbsp;&nbsp;', $sString);

        $sString = preg_replace("#&gt;&lt;#sU", "&gt;<br/>&lt;", $sString);
        //$sString = preg_replace("#&lt;#", "<blockquote>&lt;", $sString);
        //$sString = preg_replace("#/&gt;#", "/&gt;</blockquote>", $sString);

        // chevrons
        $sString = preg_replace(
            "#&lt;([/]*?)(.*)([\s]*?)&gt;#sU",
            $sColTag . "&lt;\\1\\2\\3&gt;" . $end,
            $sString
        );
        $sString = preg_replace(
            "#&lt;([\?])(.*)([\?])&gt;#sU",
            $sColTagXml . "&lt;\\1\\2\\3&gt;" . $end,
            $sString
        );

        // opening elements
        $sString = preg_replace(
            "#&lt;([^\s\?/=])(.*)([\[\s/]|&gt;)#iU",
            "&lt;" . $sColElement . "\\1\\2" . $end . "\\3",
            $sString
        );

        // closing elements
        $sString = preg_replace(
            "#&lt;([/])([^\s]*?)([\s\]]*?)&gt;#iU",
            "&lt;\\1" . $sColElement . "\\2" . $end . "\\3&gt;",
            $sString
        );

        $sString = preg_replace(
            "#([^\s]*?)\=(&quot;|')(.*)(&quot;|')#isU",
            $sColAttribute . "\\1" . $end . "=" . $sColAttributeValue . "\\2\\3\\4" . $end,
            $sString
        );

        $sString = preg_replace(
            "#&lt;(.*)(\[)(.*)(\])&gt;#isU",
            "&lt;\\1" . $sColAttribute . "\\2\\3\\4" . $end . "&gt;",
            $sString
        );

        $sString = nl2br($sString);

        return '<code>' . $sString . '</code>';
    }
}
