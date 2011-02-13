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
require_once 'Webtatic/Filesystem/Path.php';

class Webtatic_Optimizer_Plugin_File extends Webtatic_Optimizer_Plugin_Abstract
{
    protected $_outputFiles = array();
    protected $_defaultOutput = '';
    
    protected $_inputModified = array();
    protected $_outputModified = array();
    
    protected $_outputKey = 'output';
    
    protected $_serveFile = null;
    
    protected $_output = null;
    
    public function __construct($optimizer)
    {
        parent::__construct($optimizer);
        if (array_key_exists('REDIRECT_URL', $_SERVER)
            && $_SERVER['REDIRECT_URL']) {
            $this->_serveFile = Webtatic_Filesystem_Path::resolve(
                $_SERVER['DOCUMENT_ROOT'].$_SERVER['REDIRECT_URL']
            );
            $this->_output = fopen('php://output', 'w');
            
            if (preg_match('#\.([^.]+)$#', $this->_serveFile, $match)) {
                switch ($match[1]) {
                    case 'css':
                        header('Content-Type: text/css');
                        break;
                    case 'js':
                        header('Content-Type: text/javascript');
                        break;
                }
            }
        }
        
        $optimizer->addHook(
            array($this, 'postConfig'),
            Webtatic_Optimizer_Application::POST_CONFIG
        );
        $optimizer->addHook(
            array($this, 'preProcess'), 
            Webtatic_Optimizer_Application::LOAD_INPUT
        );
        $optimizer->addHook(
            array($this, 'process'),
            Webtatic_Optimizer_Application::PROCESS_INPUT,
            Webtatic_Optimizer_Application::LAST
        );
    }
    
    public function postConfig($section)
    {
        $selector = new Webtatic_Filesystem_Glob(
            $section->getConfig()->files,
            $section->getDefaults() + array(
                'workingPath' => $section->getWorkingPath()
            )
        );

        foreach ($selector->run($section->getWorkingPath()) as $file) {
            $section->addInput(
                new Webtatic_Optimizer_Input_File($file['filename'], $file)
            );
        }
    }
    
    public function preProcess($section, $input)
    {
        try {
            $input->{$this->_outputKey} = $output =
                $this->_getOutputFile(
                    $section, $input->{$this->_outputKey}, $input
                );
            if (!array_key_exists($output, $this->_outputModified)
                && is_readable($output)) {
                $this->_outputModified[$output] = filemtime($output);
            }
            
            $mod = $input->getLastModified();
            
            if (!array_key_exists($output, $this->_inputModified)
                || $this->_inputModified[$output] < $mod) {
                $this->_inputModified[$output] = $mod;
            }
        } catch (Exception $e) {
            $input->{$this->_outputKey} = null;
        }
    }
    
    public function process($section, $input)
    {
        if ($input->{$this->_outputKey} && (
                !isset($input->disableOutput) 
                || !$input->disableOutput
            )) {
            $output = $input->{$this->_outputKey};
            if (!isset($this->_outputModified[$output])
                || $this->_inputModified[$output] >
                    $this->_outputModified[$output]) {
                $handle = $this->_getOutputHandle($output);
                if ($handle !== null) {
                    $content = $input->getValue();
                    if ($input->fixLastLine
                        && !in_array(substr($content, -1), array("\n", "\r"))) {
                        $content .= "\n";
                    }
                    $this->_writeFile($handle, $content);
                }
                return true;
            }
        }
        return false;
    }
    
    protected function _openFile($filename)
    {
        return fopen($filename, 'w');
    }
    
    protected function _closeFile($handle)
    {
        return fclose($handle);
    }
    
    protected function _writeFile($handle, $content)
    {
        return fwrite($handle, $content);
    }
    
    protected function _getOutputHandle($outputFilename)
    {
        if ($this->_serveFile !== null) {
            return $this->_output;
        }
        if (!array_key_exists($outputFilename, $this->_outputFiles)) {
            $this->_outputFiles[$outputFilename] =
                $this->_openFile($outputFilename);
        }
        return $this->_outputFiles[$outputFilename];
    }
    
    protected function _getOutputFile($section, $output, $input)
    {
        $filename = $output == 1 || !$output ? $this->_defaultOutput : $output;
        while (preg_match('/:/', $filename)) {
            $filename = $this->_optimizer->replaceParams($filename, $input);
        }
        if ($filename[0] !== '/') {
            $filename = $section->getWorkingPath().'/'.$filename;
        }
        while (preg_match('/:/', $filename)) {
            $filename = $this->_optimizer->replaceParams($filename, $input);
        }
        return $filename;
    }
    
    public function __destruct()
    {
        foreach ($this->_outputFiles as $filename => $handle) {
            $this->_closeFile($handle);
        }
    }
}
