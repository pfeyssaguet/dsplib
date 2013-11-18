<?php

/**
 * Describes the structure of a field in the database.
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      4 mars 2011 22:50:21
 */

namespace DspLib\Database;

/**
 * Describes the structure of a field in the database.
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      4 mars 2011 22:50:21
 */
class FieldInfo
{
    private $sName;

    private $sType;

    private $bNullable;

    private $sExtra;

    private $sDefault;

    private $sComment = '';

    public function __construct($sName, $sType, $bNullable = false, $sDefault = null, $sExtra = null)
    {
        $this->sName = $sName;
        $this->sType = $sType;
        $this->bNullable = $bNullable;
        $this->sDefault = $sDefault;
        $this->sExtra = $sExtra;
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
        $bNullable = false;
        if ($oElement->hasAttribute('nullable')) {
            $bNullable = $oElement->getAttribute('nullable');
        }
        $sExtra = null;
        if ($oElement->hasAttribute('extra')) {
            $sExtra = $oElement->getAttribute('extra');
        }
        $sDefault = null;
        if ($oElement->hasAttribute('default')) {
        	$sDefault = $oElement->getAttribute('default');
        }
        $oFieldInfo = new FieldInfo($sName, $sType, $bNullable, $sDefault, $sExtra);
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

    public function setDefault($sDefault)
    {
        $this->sDefault = $sDefault;
    }

    public function setComment($sComment)
    {
        $this->sComment = $sComment;
    }

    public function generateCreate()
    {
        $sString = '`' . $this->sName . '` ' . $this->sType;

        if (!$this->bNullable) {
            $sString .= ' NOT NULL';
        }

        if (!is_null($this->sDefault)) {
            $sString .= ' DEFAULT \'' . addslashes($this->sDefault) . '\'';
        }

        if (isset($this->sExtra) && !empty($this->sExtra)) {
            $sString .= ' ' . $this->sExtra;
        }

        if (!empty($this->sComment)) {
            $sString .= ' COMMENT \'' . addslashes($this->sComment) . '\'';
        }
        return $sString;
    }

    public function writeToXMLElement(\DOMElement $oElement)
    {
        $oElement->setAttribute('name', $this->sName);
        $oElement->setAttribute('type', $this->sType);
        if (isset($this->sExtra) && !empty($this->sExtra)) {
            $oElement->setAttribute('extra', $this->sExtra);
        }
        if ($this->bNullable) {
            $oElement->setAttribute('nullable', 'true');
        }
    }
}
