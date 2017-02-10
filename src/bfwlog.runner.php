<?php
/**
 * Actions à effectuer lors de l'initialisation du module par le framework.
 * @author Alexandre Moittié <contact@alexandre-moittie.com>
 * @package bfw-logger
 * @version 1.0
 */

require_once($rootPath.'configs/bfw-logger/config.php');
require_once($rootPath.'configs/bfw-logger/channels.php');

if (!isset($bfwLog_channels) || $bfwLog_channels === null) {
    $bfwLog_channels = array();
}

// If we are in debugmode, we set all our record level trigger to debug log level
if ($DebugMode === true) {
    $bfwLog_loggerConfig->record_lvl_trigger = \BFWLog\LogLevel::DEBUG;
}

// Instanciate our log class
$log = new \BFWLog\Logger($rootPath.$bfwLog_dir, $bfwLog_loggerConfig, $bfwLog_logConfig, $bfwLog_channels);