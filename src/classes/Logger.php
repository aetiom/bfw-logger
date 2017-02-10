<?php

/**
 * Classes permettant de gérer la journalisation
 * @author Alexandre Moittié <contact@alexandre-moittie.com>
 * @package bfw-logger
 * @version 1.0
 */

namespace BFWLog;
use \Exception;

/**
 * Class that manage the logs
 * @package bfw-logger
 */
class Logger extends \Psr\Log\AbstractLogger {
    
    /**
     * Output format Keys
     */
    const TIMELOG = '{TIMELOG}';
    const CHANNEL = '{CHANNEL}';
    const LEVEL   = '{LEVEL}';
    const MESSAGE = '{MESSAGE}';
    
    /**
     * @var string $rootDir
     * Path to root directory 
     */
    protected $rootDir;
        
    /**
     * @var BFWLog\LogHandler $channel_logHandlers 
     * Array containing all channels log handlers
     */
    protected $channel_logHandlers = array();
    
    /**
     * @var BFWLog\LogHandler $currentChannel_logHandler
     * Current channel log handler
     */    
    protected $currentChannel_logHandler;
    
    /**
     * @var BFWLog\LogHandler $global_logHandler
     * Global/main log handler
     */
    protected $global_logHandler;
    
    /**
     * @var string $currentChannel_name
     * Current channel name
     */
    protected $currentChannel_name;
    
    /**
     * @var BFWLog\LoggerOptions $options
     * Options for this logger
     */
    protected $options;
    
    /**
     * @var BFWLog\LogOptions $logOptions_defaults
     * Defaults log options for channels log handlers
     */
    protected $logOptions_defaults;
    
    
    /**
     * Construtor
     * 
     * @param string                 $log_rootDir    : path to root directory
     * @param \BFWLog\LoggerOptions  $loggerOptions  : log options for this logger
     * @param \BFWLog\LogOptions     $logOptions : default channel options for channels log handlers
     * @param array                  $channels       : preconfigured channels names and options array((string)$name => (BFWLog\LogOptions)$options)
     */
    public function __construct($log_rootDir, LoggerOptions $loggerOptions, LogOptions $logOptions, array $channels = array()) 
    {
        // Init. vars and options
        $this->rootDir = (string)$log_rootDir;
        $this->options = $loggerOptions;
        $this->options_defaults = $logOptions;
        
        // Get directory or create it if it doesn't exist
        $rootDir = $this->getDir($this->rootDir); 

        // Configure channels, if our logger mode have a partitionned log
        if (LoggerMode::hasPartitionedLog($this->options->logger_mode)) {
            
            // If preconfigured channels is set, add channel one by oue
            if ($channels !== null && count($channels)!=0) {
                foreach ($channels as $name => $options) {
                    $this->addChannel($name, $options);
                }
            }
        }
        
        // Configure global log files, if our logger mode have a united log
        if (LoggerMode::hasUnitedLog($this->options->logger_mode)) {

            // Get full path to the log file and create the global log handler with dafault channel options
            $global_logFile = $rootDir.$this->options->global_log_name.$this->options->extention;
            $this->global_logHandler = new LogHandler($global_logFile, $logOptions);
        }
    }
    
    /**
     * Set current channel
     * 
     * @param string $channel
     */
    public function setChannel($channel) 
    {
        $this->currentChannel_name = (string) $channel;

        // If we are not in ALL_LOGS_UNITED mode, we set a channel log handler
        if ($this->options->logger_mode !== LoggerMode::ALL_LOGS_UNITED) {
            // If channel doesn't exist, we create a new log handler for it
            if (!isset($this->channel_logHandlers[$this->currentChannel_name])) {
                $this->addChannel($this->currentChannel_name);
            }

            // We set current channel log handler
            $this->currentChannel_logHandler = &$this->channel_logHandlers[$this->currentChannel_name];
        }
    }
    
    /**
     * Log message
     * 
     * @param string    $level   : level of the message to log
     * @param string    $message : message to log
     * @param array     $context : context of the message to log
     */
    public function log($level, $message, array $context = array()) 
    {
        if (LogLevel::comp($this->options->record_lvl_trigger, $level)>=0) {
            // Send the message to the log handler, if current channel log handler exists 
            // and if record is a private record type (or both record types)
            if ($this->currentChannel_logHandler !== null) {

                $logRecord = $this->createLogRecord($level, $message, $context, $this->options->force_channel_display);
                $this->currentChannel_logHandler->addRecord($logRecord);
            }

            // Send the message to the global log handler, if global log handler exists 
            // and if record is a global record type (or both record types)
            if ($this->global_logHandler !== null && 
                !($this->options->logger_mode === LoggerMode::ERRORS_UNITED_ONLY &&
                LogLevel::comp($this->options->err_events_lvl_trigger, $level)<0)) {

                $logRecord = $this->createLogRecord($level, $message, $context, true);
                $this->global_logHandler->addRecord($logRecord);
            }
        }
        
        
    }
    
    /**
     * Get last log record
     * 
     * @return mixed : last log record if it exists, otherwise false
     */
    public function getLastRecord() 
    {
        
        // If we are in logger mode ALL_LOG_UNITED, get contents from the global log handler, 
        if ($this->options->logger_mode === LoggerMode::ALL_LOGS_UNITED) {
            $contents = $this->global_logHandler->getRecords();
        }
        
        // Else if the current channel log handler is set, get contents from the channel log handler
        elseif ($this->currentChannel_logHandler !== null) {
            $contents = $this->currentChannel_logHandler->getRecords();
        }
        
        if ($contents !== false) { return end($contents); }
        else { return false; }
    }
    
    /**
     * Archives all log files, by manually triggering archive method for each log handlers known
     */
    public function archiveLogFiles () 
    {
        if ($this->channel_logHandlers !== null) {
            foreach ($this->channel_logHandlers as $logHandler) {
                $logHandler->archive();
            }
        }

        if ($this->global_logHandler !== null) {
            $this->global_logHandler->archive();
        }
    }
    
    /**
     * Create and format a log record
     * 
     * @param \BFWLog\LogLevel $level           : level
     * @param string           $message         : text message with some {placeholders}
     * @param array            $context         : context values to interpolate with
     * @param boolean          $channel_display : set to true to force the channel display 
     * @return string : return log record in a single string
     */
    private function createLogRecord($level, $message, $context = array(), $channel_display = true)
    {
        
        $output_format = $this->options->output_format;
        
        // Reconfigure output format according to the force display channel parameter
        if ($channel_display === false) {
            $tagPos = strpos($output_format, self::CHANNEL);
            
            $prevStr = substr($output_format, 0, $tagPos);
            $search = substr($prevStr, strrpos($prevStr, '}') + 1).self::CHANNEL;

            $output_format = str_replace($search, '', $output_format);
        }
        

        // Create a new context for output format
        $newContext = array(
            self::TIMELOG => $this->getTimeLog(),
            self::CHANNEL => $this->currentChannel_name,
            self::LEVEL   => LogLevel::getTag($level),
            self::MESSAGE => $this->interpolate($message, $context) // Insert context data into message
        );

        // Insert new context data into output format in order to get our final record
        return strtr($output_format, $newContext);
    }
    
    /**
     * Interpolates context values into the message placeholders.
     * 
     * @param string $message : text message with some {placeholders}
     * @param array  $context : context values to interpolate with
     * @return string : interpolated message
     */
    private function interpolate($message, array $context = array())
    {
        $replace = array();
        
        // Build a replacement array with braces around the context keys
        foreach ($context as $key => $val) {
            $openingBrace = '';
            $closingBrace = '';
            
            // If first character is not an opening brace, we set it
            if (substr($key, 0, 1) !== '{') {
                $openingBrace = '{';
            }
            
            // If last character is not a closing brace, we set it
            if (substr($key, strlen($key)-1, 1) !== '}') {
                $openingBrace = '{';
            }

            $replace[$openingBrace.$key.$closingBrace] = $val;
        }

        // Interpolate replacement values into the message and returns it
        return strtr($message, $replace);
    }
    
    
    /**
     * Create new channel, and add a log handler to it
     * 
     * @param string                 $channelName    : channel name
     * @param \BFWLog\LogOptions $logOptions : channel options
     */
    private function addChannel($channelName, LogOptions $logOptions = null) 
    {
        // If channel options are not null we merge it with our default channel options
        if ($logOptions !== null) { 
            $logOptions = (object)array_merge((array)$logOptions, (array)$this->logOptions_defaults); 
        }
        // If not, we take our default channel options as channel options
        else { 
            $logOptions = $this->options_defaults;
        }

        // Get the full path to the channel log file and we create the channel log handlers
        $logFile = $this->getLogFile($channelName);
        $this->channel_logHandlers[$channelName] = new LogHandler($logFile, $logOptions);
    }
    
    
    /**
     * Get full path to log file 
     * 
     * @param string $logName : log file name
     * @return string : full path to the log file
     */
    private function getLogFile($logName) 
    {
        // If we manage directory tree, we create a directory for this log
        if ($this->options->manage_directory_tree === true) {
            $logDir = $this->rootDir.'/'.$logName;
        }
        // If not, we take the root directory as log directory
        else {
            $logDir = $this->rootDir;
        }
        
        // Get directory and extenstion
        $logDir = $this->getDir($logDir);
        $ext = $this->options->extention;
        
        // if extension doesn't contain '.', we add it
        if (strpos($ext, '.') === false) {
            $ext = '.'.$ext;
        }
        
        // return full path to log file
        return $logDir.'/'.$logName.$ext;
    }
    
    /**
     * Check if directory exists and create it if not. Returns directory's path
     * 
     * @param string $dirPath : full path to directory
     * @return string : returns directory's path
     * @throws Exception : if we can't create non-existent directory
     */
    private function getDir($dirPath) {
        
        // If dir doesn't exists AND mkdir fails, we throws an exeption
        if (!file_exists($dirPath)) {
            if(!mkdir ($dirPath, 0755, true)) {
                throw new Exception('Impossible d\'écrire dans le dossier: '.$this->logPath);
            }
        }
        
        return $dirPath;
    }

    /**
     * Get timestamp formatted for log time
     * 
     * @return int timestamp
     */
    private function getTimeLog() 
    {
        // Create a new datetime with microseconds
        $date = \DateTime::createFromFormat('U.u', microtime(true));

        // Get actual timezone and set it to $date
        $TZ = new \DateTimeZone(date_default_timezone_get());
        $date->setTimezone($TZ);
        
        // Return timelog in the format defined by the user
        return $date->format($this->options->timelog_format);
    }
}