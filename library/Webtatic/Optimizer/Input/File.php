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

require_once 'Webtatic/Optimizer/Input/Abstract.php';

class Webtatic_Optimizer_Input_File extends Webtatic_Optimizer_Input_Abstract
{
    public function getValue()
    {
        if ($this->_value === null) {
            $this->_value = file_get_contents($this->getKey());
        }
        return parent::getValue();
    }
    
    public function getLastModified()
    {
        return filemtime($this->getKey());
    }
}
