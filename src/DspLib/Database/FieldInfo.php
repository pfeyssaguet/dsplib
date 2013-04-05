<?php

namespace DspLib\Database;

/**
 * Cette classe permet de décrire la structure d'un champ en base de données.
 *
 * @author deuspi
 * @since 4 mars 2011 22:50:21
 */
class FieldInfo
{
    private $sName;

    private $sType;

    private $bNullable;

    private $sExtra;

    private $iSize;

    private $sComment = '';

    public function __construct($sName, $sType, $bNullable = false, $sExtra = null, $iSize = null)
    {
        $this->sName = $sName;
        $this->sType = $sType;
        $this->bNullable = $bNullable;
        $this->sExtra = $sExtra;
        $this->iSize = $iSize;
    }

    /**
     * Charge la structure du champ à partir d'un noeud XML
     *
     * @param \DOMElement $oElement Noeud du champ
     *
     * @return FieldInfo
     */
    public static function loadXMLElement(\DOMElement $oElement)
    {
        $sName = $oElement->getAttribute('name');
        $sType = $oElement->getAttribute('type');

        $sComment = null;
        if ($oElement->hasAttribute('comment')) {
            $sComment = $oElement->getAttribute('comment');
        }
        $iSize = null;
        if ($oElement->hasAttribute('size')) {
            $iSize = $oElement->getAttribute('size');
        }
        $bNullable = false;
        if ($oElement->hasAttribute('nullable')) {
            $bNullable = $oElement->getAttribute('nullable');
        }
        $sExtra = null;
        if ($oElement->hasAttribute('extra')) {
            $sExtra = $oElement->getAttribute('extra');
        }
        $oFieldInfo = new FieldInfo($sName, $sType, $bNullable, $sExtra, $iSize);
        if (isset($sComment)) {
            $oFieldInfo->setComment($sComment);
        }
        return $oFieldInfo;
    }

    public function getName()
    {
        return $this->sName;
    }

    public function getType()
    {
        return $this->sType;
    }

    public function isNullable()
    {
        return $this->bNullable;
    }

    public function getExtra()
    {
        return $this->sExtra;
    }

    public function getComment()
    {
        return $this->sComment;
    }

    public function setComment($sComment)
    {
        $this->sComment = $sComment;
    }

    public function generateCreate()
    {
        $sString = '`' . $this->sName . '` ' . $this->sType;

        if (isset($this->iSize)) {
            $sString .= '(' . $this->iSize . ')';
        }

        if (!$this->bNullable) {
            $sString .= ' NOT NULL';
        }

        if (isset($this->sExtra) && !empty($this->sExtra)) {
            $sString .= ' ' . $this->sExtra;
        }

        if (!empty($this->sComment)) {
            $sString .= ' COMMENT \'' . addslashes($this->sComment) . '\'';
        }
        return $sString;
    }

    public function writeToXMLElement(\DOMElement $oElement, \DOMDocument $oDoc)
    {
        $oElement->setAttribute('name', $this->sName);
        $oElement->setAttribute('type', $this->sType);
        if (isset($this->iSize)) {
            $oElement->setAttribute('size', $this->iSize);
        }
        if (isset($this->sExtra) && !empty($this->sExtra)) {
            $oElement->setAttribute('extra', $this->sExtra);
        }
        if ($this->bNullable) {
            $oElement->setAttribute('nullable', 'true');
        }
    }
}
