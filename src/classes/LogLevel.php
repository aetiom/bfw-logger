<?php

namespace BfwLogger;

/**
 * Class that manage log levels
 * @author Alexandre Moittié <contact@alexandre-moittie.com>
 * @package bfw-logger
 * @version 2.0
 */
class LogLevel Extends \Psr\Log\LogLevel {
    
    /**
     * @var array $levelPos
     * Array containing level links from string to integer 
     * making level comparing much more easy
     */
    protected static $levelPos = array (
        self::EMERGENCY => 0,
        self::ALERT     => 10,
        self::CRITICAL  => 20,
        self::ERROR     => 30,
        self::WARNING   => 40,
        self::NOTICE    => 50,
        self::INFO      => 60,
        self::DEBUG     => 70
    );
    
    /**
     * @var array $levelTag 
     * Array containing all log level tags to include into log records
     */
    protected static $levelTag = array(
        self::EMERGENCY => 'EMERGENCY',
        self::ALERT     => 'ALERT',
        self::CRITICAL  => 'CRITICAL',
        self::ERROR     => 'ERROR',
        self::WARNING   => 'warning',
        self::NOTICE    => 'notice',
        self::INFO      => 'info',
        self::DEBUG     => 'debug'
    );
    
    /**
     * Returns string tag for log level
     * 
     * @param \BfwLogger\LogLevel $level : level
     * @return string : string tag for the level
     */
    public static function get_tag($level) {
        return self::$levelTag[$level];
    }
    
    /**
     * Compare two levels between them and return the level priority difference in integer
     * 
     * @param \BfwLogger\LogLevel $level0 : first level to compare
     * @param \BfwLogger\LogLevel $level1 : second level to compare to
     * @return int : level priority difference in integer between the two levels. 
     * <0 if ref level have higher priority than compared level, 
     * >0 if ref level have lower priority than compared level and 
     * =0 if they have the same priority
     */
    public static function comp($ref_level, $compared_level) {
        return (self::$levelPos[$ref_level] - self::$levelPos[$compared_level]) / 10;
    }
}