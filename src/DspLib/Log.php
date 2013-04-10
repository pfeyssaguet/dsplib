<?php

/**
 * Log management class
 *
 * @package DspLib
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since   30 nov. 2011 23:32:27
 */

namespace DspLib;

/**
 * Log management class
 *
 * @package DspLib
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since   30 nov. 2011 23:32:27
 */
class Log
{
    const LEVEL_ERROR = 1;
    const LEVEL_WARNING = 10;
    const LEVEL_NOTICE = 20;
    const LEVEL_INFO = 30;

    /**
     * Current general log level
     *
     * @var int
     */
    private static $iLevel = self::LEVEL_INFO;

    /**
     * Category levels
     *
     * @var array[int]
     */
    private static $aCategoryLevels = array();

    /**
     * Messages
     *
     * @var array
     */
    private static $aMessages = array();

    /**
     * Resets all the statics variables of the class (useful for test purpose...)
     */
    public static function resetStatics()
    {
        self::$iLevel = self::LEVEL_INFO;
        self::$aCategoryLevels = array();
        self::$aMessages = array();
    }

    /**
     * Defines the general log level
     *
     * @param int $iLevel Log level
     */
    public static function setLevel($iLevel)
    {
        self::$iLevel = $iLevel;
    }

    /**
     * Returns the current general log level
     *
     * @return int
     */
    public static function getLevel()
    {
        return self::$iLevel;
    }

    /**
     * Defines the log level for said category
     *
     * @param string $sCategory Category
     * @param int $iLevel Log level
     */
    public static function setCategoryLevel($sCategory, $iLevel)
    {
        self::$aCategoryLevels[$sCategory] = $iLevel;
    }

    /**
     * Returns the log level for said category
     *
     * @param string $sCategory Category
     *
     * @return int
     */
    public static function getCategoryLevel($sCategory)
    {
        if (isset(self::$aCategoryLevels[$sCategory])) {
            return self::$aCategoryLevels[$sCategory];
        }
        return self::$iLevel;
    }

    /**
     * Tells whether the level is active for said category or not
     *
     * @param string $sCategory Category
     * @param int $iLevel Level
     *
     * @return boolean
     */
    public static function isLogActive($sCategory, $iLevel)
    {
        return self::getCategoryLevel($sCategory) >= $iLevel;
    }

    /**
     * Adds a message (if configured level is above asked level)
     *
     * @param string $sCategory Category
     * @param int $iLevel Level
     * @param string $sMessage Message
     */
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

    /**
     * Adds an error
     *
     * @param string $sCategory Category
     * @param string $sMessage Message
     */
    public static function addError($sCategory, $sMessage)
    {
        self::addMessage($sCategory, self::LEVEL_ERROR, $sMessage);
    }

    /**
     * Adds a warning
     *
     * @param string $sCategory Category
     * @param string $sMessage Message
     */
    public static function addWarning($sCategory, $sMessage)
    {
        self::addMessage($sCategory, self::LEVEL_WARNING, $sMessage);
    }

    /**
     * Adds a notice
     *
     * @param string $sCategory Category
     * @param string $sMessage Message
     */
    public static function addNotice($sCategory, $sMessage)
    {
        self::addMessage($sCategory, self::LEVEL_NOTICE, $sMessage);
    }

    /**
     * Adds an info
     *
     * @param string $sCategory Category
     * @param string $sMessage Message
     */
    public static function addInfo($sCategory, $sMessage)
    {
        self::addMessage($sCategory, self::LEVEL_INFO, $sMessage);
    }

    /**
     * Returns all the messages
     *
     * @return array
     */
    public static function getMessages()
    {
        return self::$aMessages;
    }
}
