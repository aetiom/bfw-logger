<?php
/**
 * Install script for the module
 * @author Alexandre Moittié <contact@alexandre-moittie.com>
 * @package bfw-logger
 * @version 2.0
 */

echo "\n".'     > Create log directory into /app/logs/';

// If dir doesn't exists AND mkdir fails, we throws an exeption
if (!file_exists(APP_DIR.'/logs/')) {
    
    // Create our directory with 755 acls
    if(mkdir (APP_DIR.'/logs/', 0755, true)) {
        echo "...\033[1;32m Done";
    }

    else {
        // Display error in case of symlink fail
        echo "...\033[1;31m Fail !";
    }
}

else {
    // Dispay warning if the destination file (link) already exists
    echo "...\033[1;33m Already exist.";
}

echo "\033[0m\n";