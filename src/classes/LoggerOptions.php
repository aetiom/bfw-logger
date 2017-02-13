<?php

namespace BfwLogger;

/**
 * Class that handles logger options
 * @author Alexandre Moittié <contact@alexandre-moittie.com>
 * @package bfw-logger
 * @version 2.0
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