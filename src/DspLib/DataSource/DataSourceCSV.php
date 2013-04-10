<?php

namespace DspLib\DataSource;

/**
 * DataSource d'accès aux fichiers CSV (fichiers textes avec séparateur ";")
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since 9 oct. 2011 22:52:52
 */

class DataSourceCSV extends DataSource
{
    private $sPath;

    private $aKeys;

    private $mFile;

    private $aCurrentRow;

    private $iNumRow;

    private $bIsOpenForRead = false;

    private $bIsOpenForWrite = false;

    /**
     * Charge le fichier CSV
     *
     * @param string $sPath Chemin du fichier
     */
    public function __construct($sPath)
    {
        $this->sPath = $sPath;
    }

    /**
     * Ouvre le fichier en lecture
     */
    public function openFileForRead()
    {
        $this->bIsOpenForRead = true;
        $this->mFile = fopen($this->sPath, 'r');
        $sFirstLine = fgets($this->mFile);
        $sFirstLine = str_replace(array("\n", "\r"), '', $sFirstLine);
        $this->aKeys = explode(';', $sFirstLine);
        $this->iNumRow = 0;
    }

    /**
     * Ouvre le fichier en écriture
     *
     * @param array $aKeys Clefs à utiliser
     */
    public function openFileForWrite(array $aKeys)
    {
        $this->bIsOpenForWrite = true;
        if (!is_file($this->sPath) || filesize($this->sPath) == 0) {
            $sRow = implode(';', $aKeys) . PHP_EOL;
            file_put_contents($this->sPath, $sRow);
        }
    }

    private function closeFile()
    {
        if (isset($this->mFile)) {
            fclose($this->mFile);
            $this->bIsOpenForRead = false;
        }
    }

    /**
     * Ferme le fichier si besoin
     */
    public function __destruct()
    {
        $this->closeFile();
    }

    /**
     * (non-PHPdoc)
     *
     * @see DataSource::getTitles()
     */
    protected function getTitles()
    {
        if (!isset($this->mFile)) {
            $this->openFileForRead();
        }
        return $this->aKeys;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Countable::count()
     */
    public function count()
    {
        if (!$this->bIsOpenForRead) {
            $this->openFileForRead();
        }
        $iCount = 0;
        foreach ($this as $aLine) {
            $iCount++;
        }

        return $iCount;
    }

    /**
     * (non-PHPdoc)
     *
     * @see DataSource::getCurrentElement()
     */
    protected function getCurrentElement()
    {
        return $this->aCurrentRow;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::key()
     */
    public function key()
    {
        return $this->iNumRow;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::next()
     */
    public function next()
    {
        $this->fetchNextRow();
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        if ($this->mFile != null) {
            $this->closeFile();
        }
        $this->openFileForRead();
        $this->fetchNextRow();
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::valid()
     */
    public function valid()
    {
        return ($this->mFile != null);
    }

    /**
     * Ajoute une ligne dans le DataSource
     *
     * @param array $aRow Ligne à ajouter
     */
    public function writeRow(array $aRow)
    {
        if (!$this->bIsOpenForWrite) {
            $this->openFileForWrite(array_keys($aRow));
        }
        $sRow = implode(';', $aRow) . PHP_EOL;
        file_put_contents($this->sPath, $sRow, FILE_APPEND);
    }

    /**
     * Récupère la ligne suivante
     *
     * @param string $sRow Ligne
     */
    private function fetchNextRow()
    {
        if (feof($this->mFile)) {
            $this->mFile = null;
            return;
        }
        $this->iNumRow++;
        $sRow = fgets($this->mFile);

        $sRow = str_replace(array("\n", "\r"), '', $sRow);
        if ($sRow == '') {
            $this->mFile = null;
            return;
        }
        $aRow = explode(';', $sRow);

        $iRowNum = 0;
        foreach ($this->aKeys as $sKey) {
            $this->aCurrentRow[$sKey] = $aRow[$iRowNum];
            $iRowNum++;
        }
    }
}
