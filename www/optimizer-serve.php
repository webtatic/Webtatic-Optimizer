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
    'library/ext',
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

$type = 'auto';
$section = null;
$option = null;

$config = $_GET['config'];
if ($config[0] != DIRECTORY_SEPARATOR) {
    $config = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$config;
}
if (array_key_exists('section', $_GET)) {
    $section = $_GET['section'];
}
if (array_key_exists('option', $_GET)) {
    $option = $_GET['option'];
}

$filename = Webtatic_Filesystem_Path::resolve(
    $_SERVER['DOCUMENT_ROOT'].$_SERVER['REDIRECT_URL']
);

$opt = new Webtatic_Optimizer_Application();
$opt->addConfig(
    Webtatic_Filesystem_Path::resolve($config),
    $type, $section, $option
);
$opt->compile('output', $filename);
