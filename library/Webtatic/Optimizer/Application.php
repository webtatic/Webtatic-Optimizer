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
require_once 'Webtatic/Optimizer/Config.php';
require_once 'Webtatic/Filesystem/Path.php';
require_once 'Webtatic/Parser/Replace.php';

class Webtatic_Optimizer_Application
{
    protected $_masterConfg;
    /**
     * An array of configurations.
     *
     * @var array
     */
    protected $_configs = array();
    /**
     * An array of plugins loaded.
     *
     * @var array
     */
    protected $_plugins = array();
    
    const POST_CONFIG = 'post-config';
    const PRE_DESTRUCT = 'pre-destruct';
    
    const LOAD_INPUT = 'load-input';
    const PROCESS_INPUT = 'process-input';
    const UNLOAD_INPUT = 'unload-input';
    
    const REALLY_FIRST = -2;
    const FIRST = -1;
    const MIDDLE = 0;
    const LAST = 1;
    const REALLY_LAST = 2;
    
    /**
     * Hooks
     * 
     * @var array
     */
    protected $_hooks = array(
    );
    
    public function addHook($callback, $hook, $priority = self::MIDDLE)
    {
        $this->_hooks[$hook][$priority][] = $callback;
    }
    
    public function callHook($hook, $params)
    {
        foreach ($this->_hooks[$hook] as $priority) {
            foreach ($priority as $callback) {
                call_user_func_array($callback, $params);
            }
        }
    }
    
    /**
     * Constructs a Webtatic_Optimizer object. If the parameters are supplied
     * it will also add the first configuration file.
     *
     * @param string $filename
     * @param string $configType
     * @param string $section
     * @param string $path
     */
    public function __construct($filename = null, $configType = null,
            $section = null, $path = null)
    {
        $extender = Webtatic_Extender::get();
        $extender->addExtensionPoint('Webtatic_Optimizer_Filter');
        $extender->addExtensionPoint('Webtatic_Optimizer_Plugin');
        
        if (!$extender->extensionPointExists('Zend_Config')) {
            $extender->addExtensionPoint('Zend_Config');
            $extender->addExtensionPath(
                'Zend_Config', 'Webtatic/Config', 'Webtatic_Config'
            );
        }
        if ($filename !== null) {
            $this->addConfig($filename, $configType, $section, $path);
        }
        $configFile = dirname(__FILE__) . 
            '/../../../config/optimizer.defaults.ini';
        $this->_masterConfig = Webtatic_Optimizer_Config::loadConfig(
            $this, Webtatic_Filesystem_Path::resolve($configFile)
        );
    }
    
    /**
     * Add a configuration file to the optimizer for later processing.
     *
     * @param string $filename
     * @param string $configType
     * @param string $section
     * @param string $path
     */
    public function addConfig($filename, $configType = null, $section = null,
            $path = null)
    {
        $this->_configs[] = Webtatic_Optimizer_Config::loadConfig(
            $this, $filename, $configType, $section, $path, $this->_masterConfig
        );
    }
    
    public function addC($path, $config)
    {
        $this->_configs[] = new Webtatic_Optimizer_Config($this, $path,
            $config, $this->_masterConfig
        );
    }
    
    /**
     * Loads (if not already loaded) a Webtatic_Optimizer Plugin, and returns
     * it.
     *
     * @param string $name
     * @return Webtatic_Optimizer_Plugin_Interface
     */
    public function getPlugin($name)
    {
        $name = trim($name);
        if (!array_key_exists($name, $this->_plugins)) {
            $mgr = Webtatic_Extender::get();
            $class = $mgr->loadClass('Webtatic_Optimizer_Plugin', $name);
            if ($class !== null) {
                $this->_plugins[$name] = new $class($this);
            } else {
                return null;
            }
        }
        return $this->_plugins[$name];
    }
    
    /**
     * Compiles a specified section, running each of the plugins in order
     * three times (preProcess, process, postProcess)
     *
     * @param mixed $config
     * @param string $name
     * @param string $section
     */
    protected function _compileSection($section, $paramMatch, $match)
    {
        $this->callHook(self::POST_CONFIG, array($section));
        
        foreach ($section->getInputs() as $fileKey => $input) {
            $this->callHook(self::PHASE1, array($section, $input));
        }
        foreach ($section->getInputs() as $fileKey => $input) {
            if ($paramMatch === null || $file->{$paramMatch} == $match) {
                $this->callHook(self::PHASE2, array($section, $input));
            }
        }
    }
    
    public function getFiles($paramMatch, $match)
    {
        $files = array();
        foreach ($this->_configs as $config) {
            foreach ($config->getSections() as $key => $section) {
                $this->callHook(self::POST_CONFIG, array($section));
                
                foreach ($section->getInputs() as $input) {
                    $this->callHook(self::PRE_PROCESS, array($section, $input));
                    
                    if ($input->{$paramMatch} == $match) {
                        $files[] = $input;
                    }
                }
            }
        }
        return $files;
    }
    
    /**
     * Compiles a configuration, selecting each section from it, and
     * compiling each one.
     *
     * @param string $filename
     * @param mixed $config
     */
    protected function _compileConfig($filename, $config, $paramMatch, $match)
    {
        foreach ($config->getSections() as $key => $section) {
            $this->_compileSection($section, $paramMatch, $match);
        }
    }
    
    /**
     * Compiles all of the loaded configurations
     *
     */
    public function compile($paramMatch = null, $match = null)
    {
        foreach ($this->_configs as $filename => $config) {
            $this->_compileConfig($filename, $config, $paramMatch, $match);
        }
    }
    
    /**
     * Replaces an input pattern with the associated parameter
     *
     * @param string $pattern
     * @param array $params
     * @return string
     */
    public function replaceParams($pattern, $params)
    {
        $result = Webtatic_Parser_Replace::replace(
            '/\{\:([^}]+)}|:([a-z]+)/',
            array($this, '_replace'), $pattern, $params
        );
        return $result;
    }
    
    /**
     * Returns the parameter associated with a matched parameter name.
     * Do not call directly, instead use the replaceParams method.
     *
     * @param array $matches
     * @param array $params
     * @return string
     */
    public function _replace($matches, $params)
    {
        $name = strlen($matches[1]) == 0 ? $matches[2] : $matches[1];
        if (is_object($params)) {
            if (isset($params->{$name})) {
                return $params->{$name};
            } else {
                throw new Exception('Param \''.$name.'\' not specified');
            }
        } else {
            if (array_key_exists($name, $params)) {
                return $params[$name];
            } else {
                throw new Exception('Param \''.$name.'\' not specified');
            }
        }
    }
}
