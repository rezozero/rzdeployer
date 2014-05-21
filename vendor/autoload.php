<?php 
/**
 * Copyright REZO ZERO 2014
 * 
 * 
 * PSR0 Autoload
 *
 * @file autoload.php
 * @copyright REZO ZERO 2014
 * @author Ambroise Maupate
 */
spl_autoload_register('autoload');

function autoload($className)
{
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
 
    require $fileName;
}