<?php
/**
 * Initialisation script for the module
 * @author Alexandre Moittié <contact@alexandre-moittie.com>
 * @package bfw-logger
 * @version 2.0
 */

$config = $module->getConfig();

// Instanciate our log class
$module->logger = new \BfwLogger\Logger($config);