<?php

/**
 * Configuration management
 *
 * @package DspLib
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since   22 oct. 2011 19:56:54
 */

namespace DspLib;

/**
 * Configuration management
 *
 * @package DspLib
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since   22 oct. 2011 19:56:54
 */
class Config
{
    private static $instance = null;

    private $options = array();

    /**
     * Returns the singleton instance
     *
     * @return Config
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Private constructor (singleton, use getInstance() instead)
     */
    private function __construct()
    {

    }

    /**
     * Defines a parameter
     *
     * @param string $name Parameter name
     * @param mixed $value Value
     *
     * @return void
     */
    public function setParam($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * Returns a parameter
     *
     * @param string $name Parameter name
     * @param string $default Value to return if the parameter is undefined
     *
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        if (isset($default)) {
            return $default;
        }

        return null;
    }
}
