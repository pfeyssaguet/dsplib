<?php

/**
 * DataSource which provides access to CSV files (text files with semicolon separator)
 *
 * @package    DspLib
 * @subpackage DataSource
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      9 oct. 2011 22:52:52
 */

namespace DspLib\DataSource;

/**
 * DataSource which provides access to CSV files (text files with semicolon separator)
 *
 * @package    DspLib
 * @subpackage DataSource
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      9 oct. 2011 22:52:52
 */
class DataSourceCSV extends DataSource
{
    /**
     * Path of the file
     * @var string
     */
    private $path;

    /**
     * Column separator
     * @var string
     */
    private $separator = ';';

    /**
     * Column titles
     * @var array
     */
    private $columnTitles;

    /**
     * File resource pointer
     * @var resource
     */
    private $fileResource;

    /**
     * Current row
     * @var array
     */
    private $currentRow;

    /**
     * Current row number
     * @var int
     */
    private $currentRowNumber;

    /**
     * Indicates whether the file is open for reading or not
     * @var boolean
     */
    private $isOpenForRead = false;

    /**
     * Indicates whether the file is open for writing or not
     * @var boolean
     */
    private $isOpenForWrite = false;

    /**
     * Number of lines in the file (without the header)
     * @var int
     */
    private $nbLines = false;

    /**
     * Loads the CSV file
     *
     * @param string $path File path
     * @param string $separator File separator
     */
    public function __construct($path, $separator = ';')
    {
        $this->path = $path;
        $this->separator = $separator;
    }

    /**
     * Closes the file if it has been opened
     */
    public function __destruct()
    {
        $this->closeFile();
    }

    /**
     * Opens the file for reading
     */
    private function openFileForRead()
    {
        $this->isOpenForRead = true;
        $this->fileResource = fopen($this->path, 'r');
        $sFirstLine = fgets($this->fileResource);
        $sFirstLine = str_replace(array("\n", "\r"), '', $sFirstLine);
        $this->columnTitles = explode($this->separator, $sFirstLine);
        $this->currentRowNumber = 0;
    }

    /**
     * Opens the file for writing
     *
     * @param array $columnTitles Columns to use in the file
     */
    private function openFileForWrite(array $columnTitles)
    {
        $this->isOpenForWrite = true;
        if (!is_file($this->path) || filesize($this->path) == 0) {
            $line = implode($this->separator, $columnTitles) . PHP_EOL;
            file_put_contents($this->path, $line);
        }
    }

    /**
     * Closes the file if it has been opened
     */
    private function closeFile()
    {
        if (isset($this->fileResource)) {
            fclose($this->fileResource);
            $this->isOpenForRead = false;
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see DataSource::getTitles()
     */
    protected function getTitles()
    {
        if (!isset($this->fileResource)) {
            $this->openFileForRead();
        }
        return $this->columnTitles;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Countable::count()
     */
    public function count()
    {
        if ($this->nbLines === false) {
            $lines = file($this->path);
            $this->nbLines = count($lines) - 1;
        }
        return $this->nbLines;
    }

    /**
     * (non-PHPdoc)
     *
     * @see DataSource::getCurrentElement()
     */
    protected function getCurrentElement()
    {
        return $this->currentRow;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::key()
     */
    public function key()
    {
        return $this->currentRowNumber;
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
        if ($this->fileResource != null) {
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
        return ($this->fileResource != null);
    }

    /**
     * Adds a row to the DataSource
     *
     * @param array $row Row to add
     */
    public function writeRow(array $row)
    {
        if (!$this->isOpenForWrite) {
            $this->openFileForWrite(array_keys($row));
        }
        $line = implode($this->separator, $row) . PHP_EOL;
        file_put_contents($this->path, $line, FILE_APPEND);
    }

    /**
     * Fetch the next row
     */
    private function fetchNextRow()
    {
        if (feof($this->fileResource)) {
            $this->fileResource = null;
            return;
        }
        $this->currentRowNumber++;
        $line = fgets($this->fileResource);

        $line = str_replace(array("\n", "\r"), '', $line);
        if ($line == '') {
            $this->fileResource = null;
            return;
        }
        $fields = explode($this->separator, $line);

        $rowNumber = 0;
        foreach ($this->columnTitles as $key) {
            $this->currentRow[$key] = $fields[$rowNumber];
            $rowNumber++;
        }
    }
}
