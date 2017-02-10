<?php
/**
 * Fichier de configuration du module bfw-logger
 * Liste des cannaux disponibles par default
 * 
 * @author Alexandre Moittié <contact@alexandre-moittie.com>
 * @package bfw-logger
 * @version 1.0
 */

/* EXAMPLE

// We create a new logOptions for our channel 'auth'
$authOpt = new \BFWLog\logOptions();
$authOpt->rotate_interval = 0;
$authOpt->flush_interval = 180;

// We create a new logOptions for our channel 'routing'
$routingOpt = new \BFWLog\logOptions();
$routingOpt->use_compression = true;

// We set our channels by creating an array with :
// * Channel name in string as key
// * Channel log options in logOptions as value
$bfwLog_channels = array(
    "auth" => $authOpt,
    "sendmail" => null,
    "routing" => $routingOpt
);

*/