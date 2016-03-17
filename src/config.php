<?php
/**
 * Fichier de configuration du module bfw-advanced-log
 * @author Alexandre Moittié <contact@alexandre-moittie.com>
 * @package bfw-advanced-log
 * @version 1.0
 */


// Dossier où seront gérés les fichiers de journalisation 
// (string : chemin par rapport à la racine du projet)
$bfwLog_dir = 'logs';


//*** GENERAL LOGGER OPTIONS ***
$bfwLog_loggerConfig = new \BFWLog\LoggerOptions(); 

// Selectionne le mode de fonctionnement du logger (\BFWLog\LoggerMode)
// ALL_LOGS_UNITED : Tous les logs sont stockés dans le journal global (un seul et même fichier).
// PARTITIONED_LOGS : Les logs sont partitionnés dans de journaux séparés (un fichier par cannal).
// ERRORS_UNITED_ONLY : Les logs sont partitionnés dans de journaux séparés (un fichier par cannal) 
// et seuls les messages d'erreurs sont copiés dans le journal global. Fonctionne de pair avec le 
// paramètre "err_events_lvl_trigger" qui permet de définir le niveau de déclenchement des erreurs.
$bfwLog_loggerConfig->logger_mode = \BFWLog\LoggerMode::PARTITIONED_LOGS;

// Définit le niveau de déclenchement des erreurs (\BFWLog\LogLevel)
$bfwLog_loggerConfig->err_events_lvl_trigger = \BFWLog\LogLevel::WARNING;

// Définit le niveau de déclenchement des écritures de journaux (\BFWLog\LogLevel)
$bfwLog_loggerConfig->record_lvl_trigger = \BFWLog\LogLevel::INFO; 

// Active la gestion de l'arborescense des dossier. (boolean)
// Si true un dossier par canal pour séparer les journaux, sinon tous les journaux 
// seront dans le dossiers racines, y compris les archives (rotation + compression)
$bfwLog_loggerConfig->manage_directory_tree = true;

// Force l'affichage du cannal dans les journaux isolés (boolean)
// Sinon, le canal n'est affiché que dans le journal unifié.
$bfwLog_loggerConfig->force_display_channel = false;

// Définit le nom du journal général/global (string)
$bfwLog_loggerConfig->global_log_name = 'main-log';

// Définit l'extention qui sera utilisé pour les journaux (string)
$bfwLog_loggerConfig->extention = 'log';

//// Définition des formats ////

// Définit la mise en page du journal (string). 
// Les items disponibles sont : TIMELOG, CHANNEL, LEVEL et MESSAGE
// 1/ Chaque item peut être entouré de caractères permettant de le mettre en forme. 
// 2/ Chaque item DOIT être encadré par des accolades {} pour être reconnu par le système.
// 3/ Le nombre d'item n'est pas limité, seul MESSAGE est obligatoire
$bfwLog_loggerConfig->output_format = '[{TIMELOG}] [{CHANNEL}] [{LEVEL}] {MESSAGE}';

// Définit le format du 'timelog' (DATETIME FORMAT)
// http://php.net/manual/en/datetime.createfromformat.php
$bfwLog_loggerConfig->timelog_format = 'd-m-Y H:i:s O';


//*** DEFAULT LOG OPTIONS ***
$bfwLog_logConfig = new \BFWLog\LogOptions();

// Définit la fréquence des rotations en jours (integer)
// Si la valeur est <= 0, la rotation est désactivé
$bfwLog_logConfig->rotation_interval = 90;

// Définit la fréquence des suppressions en jours (integer)
// Si la valeur est <= 0, la suppression est désactivé
$bfwLog_logConfig->flush_interval = 395;

// Active l'utilisation de la compression (gzip) lors de l'archivage des fichiers
$bfwLog_logConfig->use_compression = false;