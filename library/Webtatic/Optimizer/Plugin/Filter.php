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

require_once 'Webtatic/Extender.php';
require_once 'Webtatic/Optimizer/Plugin/Abstract.php';

class Webtatic_Optimizer_Plugin_Filter extends Webtatic_Optimizer_Plugin_Abstract
{
    protected $_filters = array();
    
    public function __construct($optimizer)
    {
        parent::__construct($optimizer);
        
        $optimizer->addHook(
            array($this, 'process'),
            Webtatic_Optimizer_Application::PROCESS_INPUT,
            Webtatic_Optimizer_Application::FIRST
        );
    }
    
    public function process($section, $input)
    {
        $mgr = Webtatic_Extender::get();
        switch (gettype($input->filters)) {
            case 'string':
                $input->filters = explode(',', $input->filters);
                break;
            case 'NULL':
                $input->filters = array();
                break;
        }
        $content = $input->getValue();
        foreach ($input->filters as $key => $filter) {
            $filter = trim($filter);
            if ($filter) {
                if (!array_key_exists($filter, $this->_filters)) {
                    $class = $mgr->loadClass(
                        'Webtatic_Optimizer_Filter', $filter
                    );
                    $this->_filters[$filter] = new $class();;
                }
                
                if (method_exists($this->_filters[$filter], 'filterInput')) {
                    $content = $this->_filters[$filter]
                        ->filterInput($content, $input);
                } else {
                    $content = $this->_filters[$filter]->filter($content);
                }
            } else {
                unset($params['filters'][$key]);
            }
        }
        $input->setValue($content);
        return count($input->filters) > 0;
    }
}
