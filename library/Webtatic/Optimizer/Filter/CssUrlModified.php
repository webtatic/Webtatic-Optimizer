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

require_once 'Webtatic/Optimizer/Filter/Interface.php';
require_once 'Webtatic/Replace.php';

class Webtatic_Optimizer_Filter_CssUrlModified
{
    protected $_optimizer;
    
    public function setOptimizer($optimizer)
    {
        $this->_optimizer = $optimizer;
    }
    
    function url($matches, $input)
    {
        $depth = count(explode('/', $input->workingPath));
        $p = explode('/', $input->getKey(), $depth+1);
        array_pop($p);
        
        if ($d = filemtime(implode('/', $p).$matches[1])) {
            return 'url('.trim($matches[1]).'?'.$d.')';
        } else {
            return 'url('.trim($matches[1]).')';
        }
    }
    
    public function filterInput($content, $input)
    {
        return Webtatic_Replace::replace(
            '#url\((.*?)\)#', array($this, 'url'), $content, $input
        );
    }
}

