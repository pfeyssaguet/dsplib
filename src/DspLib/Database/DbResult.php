<?php

/**
 * Generic container for a record set
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      22 oct. 2011 21:04:16
 */

namespace DspLib\Database;

/**
 * Generic container for a record set
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      22 oct. 2011 21:04:16
 */
abstract class DbResult implements \Countable, \Iterator
{
    abstract public function getKeys();

    abstract public function getTotalRowCount();
}
