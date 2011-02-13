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

class Webtatic_Optimizer_Filter_Cssmin implements Webtatic_Optimizer_Filter_Interface
{
    protected $_patterns = array(
        '#(^|[^:])//[^\n]#' => '$1',
        '#\n|\r#' => '',
        '#/\*.*?\*/#' => '',
        '#\s+#' => ' ',
        '#\s?([;{}:])\s?#' => '$1',
    );
    
    public function filter($content)
    {
        return preg_replace(
            array_keys($this->_patterns), array_values($this->_patterns),
            $content
        );
    }
}
