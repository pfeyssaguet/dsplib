<?php

namespace DspLib;

/**
 * Permet de formater un code XML en HTML avec couleurs
 *
 * @author deuspi
 */
class XMLCodeBeautifier
{
    protected static $aColors;

    /**
     * Renders an XML string with colors based on the standard colors
     * of the PHP core highlight functions
     *
     * @param string $s The XML string to highlight
     *
     * @return string The colored XML string
     */
    public static function formatCode($s)
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
        $color_chevron_xml = $start1 . self::$aColors['default'] . $start2;
        $color_chevron = $start1 . self::$aColors['default'] . $start2;
        $color_element = $start1 . self::$aColors['comment'] . $start2;
        $color_attribute = $start1 . self::$aColors['keyword'] . $start2;
        $color_attribute_value = $start1 . self::$aColors['string'] . $start2;

        // first correct the indentation
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        if (@$dom->loadXML($s)) {
            $s = $dom->saveXML();
        }

        $s = htmlspecialchars($s);
        $s = str_replace('  ', '&nbsp;&nbsp;', $s);

        $s = preg_replace("#&gt;&lt;#sU", "&gt;<br/>&lt;", $s);
        //$s = preg_replace("#&lt;#", "<blockquote>&lt;", $s);
        //$s = preg_replace("#/&gt;#", "/&gt;</blockquote>", $s);

        // chevrons
        $s = preg_replace(
            "#&lt;([/]*?)(.*)([\s]*?)&gt;#sU",
            $color_chevron . "&lt;\\1\\2\\3&gt;" . $end,
            $s
        );
        $s = preg_replace(
            "#&lt;([\?])(.*)([\?])&gt;#sU",
            $color_chevron_xml . "&lt;\\1\\2\\3&gt;" . $end,
            $s
        );

        // opening elements
        $s = preg_replace(
            "#&lt;([^\s\?/=])(.*)([\[\s/]|&gt;)#iU",
            "&lt;" . $color_element . "\\1\\2" . $end . "\\3",
            $s
        );

        // closing elements
        $s = preg_replace(
            "#&lt;([/])([^\s]*?)([\s\]]*?)&gt;#iU",
            "&lt;\\1" . $color_element . "\\2" . $end . "\\3&gt;",
            $s
        );

        $s = preg_replace(
            "#([^\s]*?)\=(&quot;|')(.*)(&quot;|')#isU",
            $color_attribute . "\\1" . $end . "=" . $color_attribute_value . "\\2\\3\\4" . $end,
            $s
        );

        $s = preg_replace(
            "#&lt;(.*)(\[)(.*)(\])&gt;#isU",
            "&lt;\\1" . $color_attribute . "\\2\\3\\4" . $end . "&gt;",
            $s
        );

        $s = nl2br($s);

        return '<code>' . $s . '</code>';
    }
}
