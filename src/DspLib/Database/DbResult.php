<?php

namespace DspLib\Database;

/**
 * Conteneur générique d'un jeu d'enregistrements
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since 22 oct. 2011 21:04:16
 */

abstract class DbResult implements \Countable, \Iterator
{
    abstract public function getKeys();

    abstract public function getTotalRowCount();
}
