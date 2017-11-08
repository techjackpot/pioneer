<?php

function EWSAutoload($class_name)
{
    $include_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';

    return (file_exists($include_file) ? require_once $include_file : false);
}

if (version_compare(PHP_VERSION, '5.1.2', '>=')) {
    //SPL autoloading was introduced in PHP 5.1.2
    if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
        spl_autoload_register('EWSAutoload', true, true);
    } else {
        spl_autoload_register('EWSAutoload');
    }
} else {
    /**
     * Fall back to traditional autoload for old PHP versions
     * @param string $classname The name of the class to load
     */
    function __autoload($classname)
    {
        EWSAutoload($classname);
    }
}
