<?php

/**
 * Classes permettant de gérer la journalisation
 * @author Alexandre Moittié <contact@alexandre-moittie.com>
 * @package bfw-logger
 * @version 1.0
 */

namespace BFWLog;

/**
 * Describes Logger Options
 */
class LoggerOptions
{
    public $logger_mode             = LoggerMode::PARTITIONED_LOGS;
    public $err_events_lvl_trigger  = LogLevel::WARNING;
    public $record_lvl_trigger      = LogLevel::INFO;
    public $manage_directory_tree   = true;
    public $force_channel_display   = false;
    public $global_log_name         = 'main-log';
    public $extention               = 'log';
    public $output_format           = '[{TIMELOG}] [{CHANNEL}] [{LEVEL}] {MESSAGE}';
    public $timelog_format          = 'd-m-Y H:i:s O';
}