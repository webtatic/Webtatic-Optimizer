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

require_once 'Webtatic/Optimizer/Plugin/Abstract.php';
require_once 'Webtatic/Optimizer/Plugin/File.php';

class Webtatic_Optimizer_Plugin_Gzip extends Webtatic_Optimizer_Plugin_File
{
    protected $_outputFiles = array();
    protected $_defaultOutput = ':output.gz';
    
    protected $_outputKey = 'gzip';
    
    protected function _openFile($filename)
    {
        return gzopen($filename, 'w');
    }
    
    protected function _closeFile($handle)
    {
        return gzclose($handle);
    }    
}
