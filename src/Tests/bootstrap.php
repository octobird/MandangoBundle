<?php

$vendorDir = __DIR__.'/../../vendor';

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require_once $vendorDir.'/autoload.php';
$loader->add('Model', __DIR__);
$loader->register();

/*
 * Generate Mandango model.
 */
$configClasses = array(
    'Model\Article' => array(
        'fields' => array(
            'title' => array('type' => 'string'),
        ),
    ),
);

use Mandango\Mondator\Mondator;

$mondator = new Mondator();
$mondator->setConfigClasses($configClasses);
$mondator->setExtensions(array(
    new Mandango\Extension\Core(array(
        'metadata_factory_class'  => 'Model\Mapping\Metadata',
        'metadata_factory_output' => __DIR__.'/Model/Mapping',
        'default_output'          => __DIR__.'/Model'
    )),
));
$mondator->process();