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

require_once 'Webtatic/Filesystem/Path.php';

class Webtatic_Optimizer_View_Helper_OptimizerLink
{
    protected $_view;
    protected $_urlModified = true;

    public function setView($view)
    {
        $this->_view = $view;
    }

    public function optimizerLink(
        Zend_View_Helper_Placeholder_Container_Standalone $scripts)
    {
        $docRoot = Webtatic_Filesystem_Path::resolve(
            $_SERVER['DOCUMENT_ROOT']
        );

        if (self::$_optimizer != null || $this->_urlModified) {
            $oldContainer = $scripts->getContainer();
            $files = $oldContainer->getArrayCopy();
            foreach ($files as $key => $link) {
                if (isset($link->href)) {
                    $k = $link->href;
                } else if (isset($link->attributes['src'])) {
                    $k = $link->attributes['src'];
                } else {
                    continue;
                }
                if ($k[0] !== '/') {
                    continue;
                }

                $tfiles = array(
                    array('filename' => $docRoot.$k),
                );

                if (self::$_optimizer != null) {
                    if (true) {
                        $tfiles = self::$_optimizer
                            ->getFiles('output', $docRoot.$k);
                    } else {
                        $tfiles = self::$_optimizer
                            ->getFiles('filename', $docRoot.$k);
                        if (count($tfiles) > 0) {
                            $tfiles = array(
                                array('filename' => $tfiles[0]['output']),
                            );
                        }
                    }
                }

                $replacement = array();
                foreach ($tfiles as $file) {
                    $newLink = clone $link;

                    $url = substr($file['filename'], strlen($docRoot));

                    if ($this->_urlModified) {
                        $url .= '?'.filemtime($file['filename']);
                    }

                    if (isset($link->href)) {
                        $newLink->href = $url;
                    } else if (isset($link->attributes->src)) {
                        $newLink->attributes['src'] = $url;
                    }
                    $replacement[] = $newLink;
                }
                array_splice($files, $key, 1, $replacement);
            }
            $container = clone $oldContainer;
            $container->exchangeArray($files);
            $scripts->setContainer($container);
        }
        return $scripts;
    }

    private static $_optimizer = null;

    public static function setOptimizer($optimizer)
    {
        self::$_optimizer = $optimizer;
    }
}
