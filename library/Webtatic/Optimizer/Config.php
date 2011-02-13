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

require_once 'Webtatic/Filesystem/Glob.php';
require_once 'Webtatic/Filesystem/Path.php';

require_once 'Webtatic/Optimizer/Input/File.php';

class Webtatic_Optimizer_Config
{
    protected $_optimizer;
    
    protected $_version;
    protected $_defaults;
    protected $_sections;
    protected $_plugins;
    
    protected $_config;
    protected $_inputs;
    
    protected $_workingPath;
    
    protected $_keywords = array(
        'config',
        'defaults',
        'reqs',
        'plugins',
        'version',
        'workingPath',
        'files',
        'match',
    );
    
    public function getDefaults()
    {
        return $this->_defaults;
    }
    
    public function getSections()
    {
        return $this->_sections;
    }
    
    public function getConfig()
    {
        return $this->_config;
    }
    
    public function getPlugins()
    {
        return $this->_plugins;
    }
    
    public function getWorkingPath()
    {
        return $this->_workingPath;
    }
    
    public function getInputs()
    {
        return $this->_inputs;
    }
    
    public function addInput($input)
    {
        $this->_inputs[] = $input;
    }
    
    /**
     * Helper function to convert multiple objects/arrays/strings into one in
     * order of precedence of the order of the arguments
     *
     * @param mixed $arg
     * @param ..
     * @return array
     */
    protected function _convertArray()
    {
        $args = func_get_args();
        $array = array();
        foreach ($args as $arg) {
            if (is_object($arg) && method_exists($arg, 'toArray')) {
                $arg = $arg->toArray();
            } elseif (is_string($arg)) {
                $arg = preg_split('/[,\n]/', $arg);
            } else {
                $arg = (array) $arg;
            }
            $array = $arg + $array;
        }
        return $array;
    }
    
    /**
     * Constructs a optimizer configuration
     *
     * @param Webtatic_Optimizer $optimizer
     * @param Zend_Config $config
     * @param Webtatic_Optimizer_Config $parent
     */
    public function __construct($optimizer, $path, $config, $parent = null)
    {
        $this->_config = $config;
        $this->_optimizer = $optimizer;
        $this->load($path, $config, $parent);
    }
    
    public static function loadConfig($optimizer, $filename,
            $configType = null, $section = null, $path = null, $parent = null)
    {
        $config = self::loadBlagh($filename, $configType, $section, $path);
        return new self($optimizer, dirname($filename), $config, $parent);
    }
    
    protected static function loadBlagh($filename, $configType = null,
            $section = null, $path = null)
    {
        $extender = Webtatic_Extender::get();
        if (is_string($filename)) {
            if ($configType == null || $configType == 'auto') {
                if (preg_match('/\.([^\.]+)$/', $filename, $match)) {
                    $configType = $match[1];
                }
            }
            $class = $extender->loadClass('Zend_Config', $configType);
            
            $config = new $class($filename, $section, true);
            if ($path !== null) {
                foreach (explode('.', $path) as $p) {
                    $config = $config->$p;
                }
            }
            return $config;
            
        }
    }
    
    /**
     * Loads a optimizer configuration
     *
     * @param string $filename
     * @param Zend_Config $config
     * @param Zend_Config $parent
     */
    protected function load($path, $config, $parent = null)
    {
        if ($config->config) {
            $this->_version = $config->config->version;
            $this->_workingPath = Webtatic_Filesystem_Path::resolve(
                $config->config->get('workingPath', '.'), false, $path
            );
            
            if ($parent !== null) {
                $this->_plugins = $this->_getPlugins(
                    $config->config->plugins, $parent->_plugins
                );
            } else {
                $this->_plugins = $this->_getPlugins($config->config->plugins);
            }
        } else {
            $this->_version = $parent->_version;
            $this->_plugins = $parent->_plugins;
            $this->_workingPath = $parent->_workingPath;
        }
        
        if ($parent !== null) {
            $this->_defaults = $this->_convertArray(
                $config->defaults, $parent->_defaults
            );
        } else {
            $this->_defaults = $this->_convertArray($config->defaults);
        }
        
        $this->_sections = array();
        foreach ($config as $key => $value) {
            if (!in_array($key, $this->_keywords)) {
                $this->_sections[$key] = new Webtatic_Optimizer_Config(
                    $this->_optimizer, $this->_workingPath, $value, $this
                );
            }
        }
    }
    
    /**
     * Helper function to get the plugins of a configuration, which defaults 
     * can be disabled or enabled with a "-" or "+" sign, or replaced entirely
     * if no signs are in the input.
     *
     * @param array $plugins
     * @param array $parentPlugins
     * @return array
     */
    protected function _getPlugins($plugins, $parentPlugins = null)
    {
        
        $sectionPlugins = array();
        $merge = true;
        foreach ($this->_convertArray($plugins) as $key => $value) {
            preg_match('/^(\+|\-)?(.*)/', $value, $match);
            if ($match[1] == '-') {
                unset($sectionPlugins[substr($value, 1)]);
            } else if (!array_key_exists($match[2], $sectionPlugins)) {
                if (strtolower($match[2]) == 'none') {
                    $sectionPlugins = array();
                    $merge = false;
                    break;
                } else {
                    $sectionPlugins[$match[2]] =
                        $this->_optimizer->getPlugin($match[2]);
                    if ($match[1] === null) {
                        $merge = true;
                    }
                }
            }
        }
        if ($merge && $parentPlugins !== null) {
            $sectionPlugins = $parentPlugins + $sectionPlugins;
        }
        
        return $sectionPlugins;
    }
}
