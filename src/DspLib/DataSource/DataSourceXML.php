<?php

namespace DspLib\DataSource;

/**
 * DataSource d'accès à un XML (de façon extrêmement simplifiée)
 *
 * TODO expliquer la structure possible et les options
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since 20 oct. 2011 22:57:38
 */

class DataSourceXML extends DataSource
{

    private $sPath;

    private $aKeys;

    private $sNode;

    private $oDoc;

    private $oElement;

    private $oNodeList;

    private $aCurrentRow;

    private $iIndex = 0;

    public function __construct($sPath)
    {
        $this->sPath = $sPath;
        $this->oDoc = new \DOMDocument();
        $this->oDoc->load($this->sPath);
        $this->oElement = $this->oDoc->documentElement;
        $this->oNodeList = $this->oDoc->getElementsByTagName($this->oElement->nodeName);
    }

    public function setNode($sNode)
    {
        $this->sNode = $sNode;
        $this->iIndex = 0;
        $this->aKeys = null;
        $this->oNodeList = $this->oDoc->getElementsByTagName($this->sNode);
        $this->oElement = $this->oNodeList->item($this->iIndex);
    }

    protected function getTitles()
    {
        if (!isset($this->aKeys)) {
            $this->aKeys = array();
            for ($i = 0; $i < $this->oElement->childNodes->length; $i++) {
                $oElement = $this->oElement->childNodes->item($i);
                if ($oElement instanceof \DOMElement) {
                    $this->aKeys[] = $oElement->nodeName;
                }
            }
        }
        return $this->aKeys;
    }

    public function count()
    {
        return $this->oNodeList->length;
    }

    protected function getCurrentElement()
    {
        return $this->aCurrentRow;
    }

    public function key()
    {
        return $this->iIndex;
    }

    public function next()
    {
        $this->iIndex++;
        $this->extractRow();
    }

    public function rewind()
    {
        $this->iIndex = 0;
        $this->extractRow();
    }

    public function valid()
    {
        return $this->iIndex < $this->oNodeList->length && isset($this->aCurrentRow);
    }

    private function extractRow()
    {
        if ($this->iIndex >= $this->oNodeList->length) {
            return false;
        }
        $this->oElement = $this->oNodeList->item($this->iIndex);
        $this->aCurrentRow = array();
        for ($i = 0; $i < $this->oElement->childNodes->length; $i++) {
            $oElement = $this->oElement->childNodes->item($i);
            if ($oElement instanceof \DOMElement) {
                $this->aCurrentRow[$oElement->nodeName] = $oElement->nodeValue;
            }
        }
    }

    /**
     * Ajoute une ligne dans le DataSource
     *
     * @param array $aRow Ligne à ajouter
     */
    public function writeRow(array $aRow)
    {
        throw new \Exception('Method writeRow is not implemented yet in ' . __CLASS__);
    }
}
