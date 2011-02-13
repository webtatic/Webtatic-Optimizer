<?php
$config = array(
    'version' => '1.0.0',
    'workingPath' => '..',
);
    
$javascript = array(
    'defaults' => array(
        'output' => 'http/includes/javascript/:package.js',
    ),
    'files' => array(
        'http/includes/javascript/:package/packages.js',
        'http/includes/javascript/:package/**.js',
    )
);
    
$template = array(
    'defaults' => array(
        'output' => 'http/includes/templates/:template/site.css',
    ),
    'files' => array(
        'http/includes/templates/:template/css/**.css',
    ),
)
?>