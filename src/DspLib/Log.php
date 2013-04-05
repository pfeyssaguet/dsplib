<?php

namespace DspLib;

/**
 * Classe de gestion de logs
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since 30 nov. 2011 23:32:27
 */

class Log
{

    const LEVEL_ERROR = 1;
    const LEVEL_WARNING = 10;
    const LEVEL_NOTICE = 20;
    const LEVEL_INFO = 30;

    private static $iLevel = self::LEVEL_INFO;

    private static $aCategoryLevels = array();

    private static $aMessages = array();

    /**
     * Définit le niveau de logs
     *
     * @param int $iLevel Niveau de log
     */
    public static function setLevel($iLevel)
    {
        self::$iLevel = $iLevel;
    }

    public static function setCategoryLevel($sCategory, $iLevel)
    {
        self::$aCategoryLevels[$sCategory] = $iLevel;
    }

    public static function isLogActive($sCategory, $iLevel)
    {
        if (isset(self::$aCategoryLevels[$sCategory])) {
            return self::$aCategoryLevels[$sCategory] >= $iLevel;
        }
        return self::$iLevel >= $iLevel;
    }

    public static function addMessage($sCategory, $iLevel, $sMessage)
    {
        if (self::isLogActive($sCategory, $iLevel)) {
            self::$aMessages[] = array(
                'timestamp' => date('Y-m-d H:i:s'),
                'category' => $sCategory,
                'level' => $iLevel,
                'message' => $sMessage
            );
        }
    }

    public static function addError($sCategory, $sMessage)
    {
        self::addMessage($sCategory, self::LEVEL_ERROR, $sMessage);
    }

    public static function addWarning($sCategory, $sMessage)
    {
        self::addMessage($sCategory, self::LEVEL_WARNING, $sMessage);
    }

    public static function addNotice($sCategory, $sMessage)
    {
        self::addMessage($sCategory, self::LEVEL_NOTICE, $sMessage);
    }

    public static function addInfo($sCategory, $sMessage)
    {
        self::addMessage($sCategory, self::LEVEL_INFO, $sMessage);
    }

    public static function getMessages()
    {
        return self::$aMessages;
    }
}
