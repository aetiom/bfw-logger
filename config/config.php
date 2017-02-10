<?php
/**
 * Fichier de configuration du module bfw-advanced-log
 * @author Alexandre Moittié <contact@alexandre-moittie.com>
 * @package bfw-advanced-log
 * @version 1.0
 */

//*** GENERAL LOGGER OPTIONS ***
$loggerConfig = new \BfwLogger\LoggerOptions(); 

// Selectionne le mode de fonctionnement du logger (\BfwLogger\LoggerMode)
// ALL_LOGS_UNITED : Tous les logs sont stockés dans le journal global (un seul et même fichier).
// PARTITIONED_LOGS : Les logs sont partitionnés dans de journaux séparés (un fichier par cannal).
// ERRORS_UNITED_ONLY : Les logs sont partitionnés dans de journaux séparés (un fichier par cannal) 
// et seuls les messages d'erreurs sont copiés dans le journal global. Fonctionne de pair avec le 
// paramètre "err_events_lvl_trigger" qui permet de définir le niveau de déclenchement des erreurs.
$loggerConfig->logger_mode = \BfwLogger\LoggerMode::PARTITIONED_LOGS;

// Définit le niveau de déclenchement des erreurs (\BfwLogger\LogLevel)
$loggerConfig->err_events_lvl_trigger = \BfwLogger\LogLevel::WARNING;

// Définit le niveau de déclenchement des écritures de journaux (\BfwLogger\LogLevel)
$loggerConfig->record_lvl_trigger = \BfwLogger\LogLevel::INFO; 

// Active la gestion de l'arborescense des dossier. (boolean)
// Si true un dossier par canal pour séparer les journaux, sinon tous les journaux 
// seront dans le dossiers racines, y compris les archives (rotation + compression)
$loggerConfig->manage_directory_tree = true;

// Force l'affichage du cannal dans les journaux isolés (boolean)
// Sinon, le canal n'est affiché que dans le journal unifié.
$loggerConfig->force_display_channel = false;

// Définit le nom du journal général/global (string)
$loggerConfig->global_log_name = 'main-log';

// Définit l'extention qui sera utilisé pour les journaux (string)
$loggerConfig->extention = 'log';

//// Définition des formats ////

// Définit la mise en page du journal (string). 
// Les items disponibles sont : TIMELOG, CHANNEL, LEVEL et MESSAGE
// 1/ Chaque item peut être entouré de caractères permettant de le mettre en forme. 
// 2/ Chaque item DOIT être encadré par des accolades {} pour être reconnu par le système.
// 3/ Le nombre d'item n'est pas limité, seul MESSAGE est obligatoire
$loggerConfig->output_format = '[{TIMELOG}] [{CHANNEL}] [{LEVEL}] {MESSAGE}';

// Définit le format du 'timelog' (DATETIME FORMAT)
// http://php.net/manual/en/datetime.createfromformat.php
$loggerConfig->timelog_format = 'd-m-Y H:i:s O';



//*** DEFAULT LOG OPTIONS ***
$logConfig = new \BfwLogger\LogOptions();

// Définit la fréquence des rotations en jours (integer)
// Si la valeur est <= 0, la rotation est désactivé
$logConfig->rotation_interval = 90;

// Définit la fréquence des suppressions en jours (integer)
// Si la valeur est <= 0, la suppression est désactivé
$logConfig->flush_interval = 395;

// Active l'utilisation de la compression (gzip) lors de l'archivage des fichiers
$logConfig->use_compression = false;



//*** DEFAULT CHANNELS ***
// Liste des cannaux disponibles par default

/* EXAMPLE
// We create a new logOptions for our channel 'auth'
$authOpt = new \BfwLogger\logOptions();
$authOpt->rotate_interval = 0;
$authOpt->flush_interval = 180;

// We create a new logOptions for our channel 'routing'
$routingOpt = new \BfwLogger\logOptions();
$routingOpt->use_compression = true;

// We set our channels by creating an array with :
// * Channel name in string as key
// * Channel log options in logOptions as value

$channels = array(
        "auth" => $authOpt,
        "sendmail" => null,
        "routing" => $routingOpt
);
*/

$channels = array();


return (object) [
    'loggerConfig' => $loggerConfig,
    'logConfig' => $logConfig,
    'channels' => $channels
];