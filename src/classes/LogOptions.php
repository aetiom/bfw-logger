<?php

/**
 * Classes permettant de gérer la journalisation
 * @author Alexandre Moittié <contact@alexandre-moittie.com>
 * @package bfw-advanced-log
 * @version 1.0
 */

namespace BFWLog;

/**
 * Describes File Log Options
 */
class LogOptions 
{
    public $rotate_interval         = 90;
    public $flush_interval          = 395;
    public $use_compression         = false;
}
