<?php
/**
 * This file is part of Webtatic Optimizer.
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtatic.com/license/new-bsd
 *
 * @category   Webtatic
 * @package    Webtatic_Optimizer
 * @copyright  Copyright (c) 2007-2009 Andrew Thompson. (http://www.webtatic.com)
 * @license    http://www.webtatic.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once 'Webtatic/Optimizer/Input/Interface.php';

abstract class Webtatic_Optimizer_Input_Interface
{
    protected $_key;
    protected $_value = null;
    protected $_metaData;
    
    public function __construct($key, $metaData)
    {
        $this->_key = $key;
        $this->_metaData = $metaData;
    }
    
    public function getKey()
    {
        return $this->_key;
    }
    
    public function getValue()
    {
        return $this->_value;
    }
    
    public function setValue($value)
    {
        $this->_value = $value;
    }
    
    public function getMetaData($key)
    {
        return $this->_metaData[$key];
    }
    
    public function setMetaData($key, $value)
    {
        $this->_metaData[$key] = $value;
    }
    
    public function __get($name)
    {
        switch ($name) {
        case 'key':
            return $this->getKey();
        case 'value':
            return $this->getValue();
        default:
            return $this->getMetaData($name);
        }
    }
    
    public function __set($name, $value)
    {
        switch ($name) {
        case 'key':
            return null;
        case 'value':
            return $this->setValue($value);
        default:
            return $this->setMetaData($name, $value);
        }
    }
    
    public function __isset($name)
    {
        switch ($name) {
        case 'key':
        case 'value':
            return true;
        default:
            return isset($this->_metaData[$name]);
        }
    }
}
