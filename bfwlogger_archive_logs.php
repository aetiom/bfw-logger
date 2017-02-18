<?php
/**
 * Script for archiving log files
 * @author Alexandre Moittié <contact@alexandre-moittie.com>
 * @package bfw-logger
 * @version 2.0
 */

/*
 * We advise you to add a cron job that execute that script each 10 seconds (or so
 * depending on your emailing volume) with command line : php cli.php bfwmailer_process_q
 */

// Retrieve logger module from BFW Application
$logger = \BFW\Application::getInstance()->getModule('logger')->logger;

// Archiving log files
$logger->archive_logFiles();