<?php
/**
 * Fichier de configuration du module bfw-advanced-log
 * Liste des cannaux disponibles par default
 * 
 * @author Alexandre Moittié <contact@alexandre-moittie.com>
 * @package bfw-advanced-log
 * @version 1.0
 */


$authOpt = new \BFWLog\ChannelOptions();
$authOpt->rotate = false;
$authOpt->flush_interval = 180;
$authOpt->timelog_format = '{JJ}-{MM}-{AA} {HH}:{MM}:{SS}';


$bfwLog_channels = array(
    "auth" => $authOpt, 
    "sendmail" => null
);

$bfwLog_channels = array(
    "auth" => null, 
    "sendmail" => null
);