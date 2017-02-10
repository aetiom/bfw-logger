<?php
/**
 * Initialisation script for the module
 * @author Alexandre Moittié <contact@alexandre-moittie.com>
 * @package bfw-advanced-log
 * @version 2.0
 */

$config = $module->getConfig();

// Instanciate our log class
$module->log = new \BfwLogger\Logger($config);