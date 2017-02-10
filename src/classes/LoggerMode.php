<?php

namespace BfwLogger;

/**
 * Class that handles logger modes
 * @author Alexandre Moittié <contact@alexandre-moittie.com>
 * @package bfw-advanced-log
 * @version 2.0
 */
class LoggerMode {
    const ALL_LOGS_UNITED    = 0;
    const PARTITIONED_LOGS   = 1;
    const ERRORS_UNITED_ONLY = 2;

    
    public static function hasUnitedLog ($mode) 
    {
        if ($mode !== self::PARTITIONED_LOGS) {
            return true;
        }
        
        return false;
    }
    
    public static function hasPartitionedLog ($mode) 
    {
        if ($mode !== self::ALL_LOGS_UNITED) {
            return true;
        }
        
        return false;
    }
}