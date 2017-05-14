<?php

namespace BfwLogger;
use \Exception;

/**
 * Class that manages logger and logging for BFW Framework
 * @author Alexandre Moittié <contact@alexandre-moittie.com>
 * @package bfw-logger
 * @version 2.0
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
     * @var BfwLogger\LogHandler $logHandlers 
     * Array containing all channels log handlers
     */
    protected $logHandlers = array();
    
    /**
     * @var string $active_channel
     * Active channel name
     */
    protected $active_channel;
    
    /**
     * string $global_channel
     * Global channel name
     */
    protected $global_channel;
    
    /**
     * @var BfwLogger\LoggerOptions $options
     * Options for this logger
     */
    protected $options;
    
    /**
     * @var BfwLogger\LogOptions $default_logOptions
     * Defaults log options for channels log handlers
     */
    protected $default_logOptions;
    
    
    /**
     * Construtor
     * 
     * @param \BfwLogger\LoggerOptions  $loggerOptions  : log options for this logger
     * @param \BfwLogger\LogOptions     $logOptions : default channel options for channels log handlers
     * @param array                  $channels       : preconfigured channels names and options array((string)$name => (BfwLogger\LogOptions)$options)
     */
    public function __construct(\BFW\Config $config) 
    {
        // Init. vars and options
        $this->rootDir = ROOT_DIR.'/app/logs/';
        $this->options = $config->getValue('loggerConfig');
        $this->options_defaults = $config->getValue('logConfig');

        // If we are in debugmode, we set all our record level trigger to debug log level
        if (\BFW\Application::getInstance()->getConfig()->getValue('debug') === true) {
            $this->options->record_lvl_trigger = \BfwLogger\LogLevel::DEBUG;
        }
        
        // Configure channels, if our logger mode have a partitionned log
        if (LoggerMode::has_partitionedLog($this->options->logger_mode)) {
            $channels = $config->getValue('channels');
            
            // If preconfigured channels is set, add channel one by oue
            if ($channels !== null && count($channels)!=0) {
                foreach ($channels as $name => $options) {
                    $this->add_channel($name, $options);
                }
            }
        }
        
        // Configure global log files, if our logger mode have a united log
        if (LoggerMode::has_unitedLog($this->options->logger_mode)) {
            $this->global_channel = $this->options->global_log_name;
            
            // Get full path to the log file and create the global log handler with dafault channel options
            $logFile = $this->rootDir.$this->global_channel.$this->options->extention;
            $this->logHandlers[$this->global_channel] = new LogHandler($logFile, $logOptions);
        }
    }
    
    /**
     * Set current channel
     * 
     * @param string $channel
     */
    public function set_channel($channel) 
    {
        $this->active_channel = (string) $channel;

        // If we are not in ALL_LOGS_UNITED mode and channel doesn't exist, 
        // we create a new log handler for it
        if ($this->options->logger_mode !== LoggerMode::ALL_LOGS_UNITED && 
                !isset($this->logHandlers[$this->active_channel])) {
            $this->add_channel($this->active_channel);
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
            if ($this->active_channel !== null) {
                $logRecord = $this->create_logRecord($level, $message, $context, $this->options->force_channel_display);
                $this->logHandlers[$this->active_channel]->add_record($logRecord);
            }

            // Send the message to the global log handler, if global log handler exists 
            // and if record is a global record type (or both record types)
            if ($this->global_channel !== null && 
                !($this->options->logger_mode === LoggerMode::ERRORS_UNITED_ONLY &&
                LogLevel::comp($this->options->err_events_lvl_trigger, $level)<0)) {

                $logRecord = $this->create_logRecord($level, $message, $context, true);
                $this->logHandlers[$this->global_channel]->add_record($logRecord);
            }
        }
        
        
    }
    
    /**
     * Get last log record
     * 
     * @return mixed : last log record if it exists, otherwise false
     */
    public function get_lastRecord() 
    {
        // If we are in logger mode ALL_LOG_UNITED, get contents from the global log handler, 
        if ($this->options->logger_mode === LoggerMode::ALL_LOGS_UNITED) {
            $contents = $this->logHandlers[$this->global_channel]->get_records();
        } elseif ($this->active_channel !== null) {
            // Else if the current channel log handler is set, get contents from the channel log handler
            $contents = $this->logHandlers[$this->active_channel]->get_records();
        }
        
        if ($contents !== false) { 
            return end($contents); 
        } else { 
            return false;
        }
    }
    
    /**
     * Archives all log files, by manually triggering archive method for each log handlers known
     */
    public function archive_logFiles () 
    {
        foreach ($this->logHandlers as $logHandler) {
            $logHandler->archive();
        }
    }
    
    /**
     * Create and format a log record
     * 
     * @param \BfwLogger\LogLevel $level           : level
     * @param string           $message         : text message with some {placeholders}
     * @param array            $context         : context values to interpolate with
     * @param boolean          $channel_display : set to true to force the channel display 
     * @return string : return log record in a single string
     */
    private function create_logRecord($level, $message, $context = array(), $channel_display = true)
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
            self::TIMELOG => $this->get_timeLog(),
            self::CHANNEL => $this->active_channel,
            self::LEVEL   => LogLevel::get_tag($level),
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
     * @param string                $channelName : channel name
     * @param \BfwLogger\LogOptions $logOptions  : channel options
     */
    private function add_channel($channelName, LogOptions $logOptions = null) 
    {
        // If channel options are not null we merge it with our default channel options
        if ($logOptions !== null) { 
            $logOptions = (object)array_merge((array)$logOptions, (array)$this->default_logOptions); 
        }
        // If not, we take our default channel options as channel options
        else { 
            $logOptions = $this->options_defaults;
        }

        // Get the full path to the channel log file and we create the channel log handlers
        $logFile = $this->get_logFile($channelName);
        $this->logHandlers[$channelName] = new LogHandler($logFile, $logOptions);
    }
    
    
    /**
     * Get full path to log file 
     * 
     * @param string $logName : log file name
     * @return string : full path to the log file
     */
    private function get_logFile($logName) 
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
        $logDir = $this->get_dir($logDir);
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
    private function get_dir($dirPath) {
        
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
    private function get_timeLog() 
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