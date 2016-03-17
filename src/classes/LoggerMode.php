<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BFWLog;

/**
 * Describes Logger Mode
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