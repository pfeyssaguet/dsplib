<?php

namespace DspLib;

/**
 * Classe utilitaire pour les chaînes de caractères.
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */
class StringUtils
{

    /**
     * Renvoie TRUE si la chaîne sujet finit par la chaîne demandée
     *
     * @param string  $sSubject    Sujet
     * @param string  $sString     Chaîne demandée
     * @param boolean $bIgnoreCase Mettre à FALSE pour prendre en compte la casse (optional)
     *
     * @return boolean
     */
    public static function endsWith($sSubject, $sString, $bIgnoreCase = true)
    {
        if ($bIgnoreCase) {
            $sSubject = strtolower($sSubject);
            $sString = strtolower($sString);
        }

        if (substr($sSubject, strlen($sSubject) - strlen($sString)) == $sString) {
            return true;
        }
        return false;
    }

    /**
     * Renvoie TRUE si la chaîne sujet commence par la chaîne demandée
     *
     * @param string  $sSubject    Sujet
     * @param string  $sString     Chaîne demandée
     * @param boolean $bIgnoreCase Mettre à FALSE pour prendre en compte la casse (optional)
     *
     * @return boolean
     */
    public static function beginsWith($sSubject, $sString, $bIgnoreCase = true)
    {
        if ($bIgnoreCase) {
            $sSubject = strtolower($sSubject);
            $sString = strtolower($sString);
        }

        if (substr($sSubject, 0, strlen($sString)) == $sString) {
            return true;
        }
        return false;
    }

    /**
     * Convertit une chaîne 'snake_case' en chaîne 'CamelCase'
     *
     * @param string $sSnakeCase La chaîne au format snake_case
     *
     * @return string
     */
    public static function toCamelCase($sSnakeCase)
    {
        // on découpe la chaîne en mots
        $asWords = explode('_', $sSnakeCase);

        $sString = "";
        foreach ($asWords as $sWord) {
            // on met la première lettre de chaque mot en majuscule
            $sString .= ucfirst($sWord);
        }
        return $sString;
    }

    /**
     * Convertit une chaîne 'CamelCase' en chaîne 'snake_case'
     *
     * @param string $sCamelCase La chaîne au format CamelCase
     *
     * @return string
     */
    public static function toSnakeCase($sCamelCase)
    {
        if (!preg_match('/^[A-Za-z]+$/', $sCamelCase)) {
            $sOutput = strtolower($sCamelCase);
        } else {
            $sOutput = preg_replace_callback('/[A-Z]/', function ($match) {
                return "_" . strtolower($match[0]);
            }, $sCamelCase);

            $sOutput = substr($sOutput, 1);;
        }
        return $sOutput;
    }

    /**
     * Supprime les accents d'une chaîne de caractères
     *
     * @param string $sString Chaîne à nettoyer
     *
     * @return string
     */
    public static function stripAccents($sString)
    {
        $sOutput = $sString;

        $aReplaceData = array(
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'ç' => 'c', 'è' => 'e',
            'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ù' => 'u',
            'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'ÿ' => 'y', 'À' => 'A', 'Á' => 'A',
            'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E',
            'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O',
            'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U',
            'Ü' => 'U', 'Ý' => 'Y',
            chr(140) => 'OE',
            chr(156) => 'oe',
            chr(230) => 'AE'
        );

        $aReplaceFrom = array_keys($aReplaceData);
        $aReplaceTo = array_values($aReplaceData);

        $sOutput = str_replace($aReplaceFrom, $aReplaceTo, $sOutput);

        return $sOutput;
    }
}
