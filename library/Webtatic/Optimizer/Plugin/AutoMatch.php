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

class Webtatic_Optimizer_Plugin_AutoMatch extends Webtatic_Optimizer_Plugin_Abstract
{
    
    public function __construct($optimizer)
    {
        parent::__construct($optimizer);
        
        $optimizer->addHook(
            array($this, 'preProcess'), 
            Webtatic_Optimizer_Application::LOAD_INPUT,
            Webtatic_Optimizer_Application::FIRST
        );
    }
    
    public function preProcess($section, $input)
    {
        foreach ((array)$input->autoMatch as $autoMatch) {
            $match = true;
            foreach ($autoMatch['match'] as $key => $pattern) {
                if (!preg_match($pattern, $input->{$key})) {
                    $match = false;
                    break;
                }
            }
            
            if (count($autoMatch['match']) > 0 && $match) {
                foreach ($autoMatch['defaults'] as $key => $value) {
                    $input->setMetaData($key, $value);
                }
            }
        }
    }
}
