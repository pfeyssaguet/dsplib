<?php

namespace DspLib\DataSource;

/**
 * DataSource gérant un simple array
 *
 * @author deuspi
 * @since 9 oct. 2011 15:52:04
 */

class DataSourceArray extends DataSource
{

    private $aData = array();

    private $aKeys = array();

    private $iKey = 0;

    public function __construct(array $aData = array())
    {
        if (!empty($aData)) {
            $aRowIndexes = array_keys($aData);
            if (!is_array($aData[$aRowIndexes[0]])) {
                throw new \InvalidArgumentException('Argument must be an array with 2 dimensions');
            }
            foreach ($aData as $aRow) {
                $this->writeRow($aRow);
            }
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see DataSource::getTitles()
     */
    protected function getTitles()
    {
        return $this->aKeys;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Countable::count()
     */
    public function count()
    {
        return count($this->aData);
    }

    /**
     * (non-PHPdoc)
     *
     * @see DataSource::getCurrentElement()
     */
    protected function getCurrentElement()
    {
        $aRowIndexes = array_keys($this->aData);
        return $this->aData[$aRowIndexes[$this->iKey]];
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::key()
     */
    public function key()
    {
        $aRowIndexes = array_keys($this->aData);
        return $aRowIndexes[$this->iKey];
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::next()
     */
    public function next()
    {
        $this->iKey++;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        $this->iKey = 0;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::valid()
     */
    public function valid()
    {
        return $this->iKey < count($this);
    }

    /**
     * Ajoute une ligne dans le DataSource
     *
     * @param array $aRow Ligne à ajouter
     */
    public function writeRow(array $aRow)
    {
        //var_dump($aRow);
        if (empty($this->aKeys)) {
            $this->aKeys = array_keys($aRow);
        }
        $aNewRow = array();
        foreach ($this->aKeys as $sKey) {
            $aNewRow[$sKey] = '';
            if (isset($aRow[$sKey])) {
                $aNewRow[$sKey] = $aRow[$sKey];
            }
        }
        $this->aData[] = $aNewRow;
    }
}
