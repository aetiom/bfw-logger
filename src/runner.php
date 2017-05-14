<?php
/**
 * Initialisation script for the module
 * @author Alexandre Moittié <contact@alexandre-moittie.com>
 * @package bfw-logger
 * @version 2.0
 */

$config = $this->getConfig();

// Instanciate our log class
$this->logger = new \BfwLogger\Logger($config);