#!/usr/bin/php
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

$includePaths = array(
    'library',
    'library/ext/webtatic/library',
    'library/ext/zend/library',
);

foreach ($includePaths as $key => $includePath) {
    if ($includePath[0] != '/') {
        $includePaths[$key] = dirname(__FILE__) . '/../' . $includePath;
    }
}
$includePaths[] = get_include_path();
set_include_path(join(PATH_SEPARATOR, $includePaths));

require_once 'Webtatic/Optimizer/Application.php';
require_once 'Webtatic/Filesystem/Path.php';

function usage($program) {
    fwrite(STDERR, "Usage: $program [options] FILES
Compile using the config FILES specifed

Options:
  -h, --help             display this message
  -s, --section SECTION  select the SECTION section from the config
  -o, --option OPTION    select the OPTION subset from the config
  -t, --type TYPE        set the config type to TYPE [default: auto]
      --auto             automatically select the config type by extension
      --ini              set the config type to ini
      --php              set the config type to php
      --xml              set the config type to xml
");
    exit;
}

$opt = new Webtatic_Optimizer_Application();

$type = null;
$section = null;
$option = null;
$configAdded = false;

$args = $_SERVER['argv'];
$program = array_shift($args);

while (count($args) > 0) {
    $arg = array_shift($args);
    if ($arg[0] == '-') {
        switch (substr($arg, 1)) {
            case 't':
            case '-type':
                $type = array_shift($args);
                if ($type == 'auto') $type = null;
                break;
            case '-auto':
            case '-ini':
            case '-php':
            case '-xml':
                $type = substr($arg, 2);
                if ($type == 'auto') $type = null;
                break;
            case 's':
            case '-section':
                $section = array_shift($args);
                break;
            case 'o':
            case '-option':
                $option = array_shift($args);
                break;
            case 'h':
            case '-help':
            default:
                usage($program);
        }
    } else {
        
        $opt->addConfig(Webtatic_Filesystem_Path::resolve($arg), $type, $section, $option);
        $configAdded = true;
    }
}

if (!$configAdded) {
    usage($program);
}

$opt->compile();
?>
