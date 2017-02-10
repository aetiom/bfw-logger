<?php

namespace BfwLogger;

/**
 * Class that manage log file
 * @author Alexandre Moittié <contact@alexandre-moittie.com>
 * @package bfw-advanced-log
 * @version 2.0
 */
class LogHandler {
    
    /**
     * @var string $rootDir
     * Path to root directory 
     */
    protected $rootDir;
    
    /**
     * @var string $logFile 
     * Full path to log file
     */
    protected $logFile;
    
    /**
     * @var string $logName
     * Local path to log file (file name + extension)
     */
    protected $logName;
        
    /**
     * @var string $cTimeFile 
     * Path to the json ctime file
     */
    protected $cTimeFile;
    
    /**
     * @var int $log_cTime
     * Creation Time for the main log file in Unix Timestamp Integer
     */
    protected $log_cTime;
    
    /**
     * @var \BfwLogger\ChannelOptions $options 
     * Channel options
     */
    protected $options;
    
    
    /**
     * Constructor
     * 
     * @param string             $logFile : full path to the log file
     * @param \BfwLogger\LogOptions $options : channle options for this handler
     */
    public function __construct($logFile, LogOptions $options)
    {
        $this->logFile = $logFile;
        $this->options = $options;

        // Get parent directory path for further interactions
        $this->rootDir = dirname($this->logFile);
        
        // Construct logName and cTimeFile from logFile parent directory
        $this->logName = substr($this->logFile, strlen($this->rootDir)+1);
        $this->cTimeFile = $this->rootDir.'/.ctime.json';
        

        // Get ctime file, decode its json and put it into log ctime var
        if (file_exists($this->cTimeFile)) {
            $contents = file_get_contents($this->cTimeFile);
            $decoded_contents = json_decode($contents, true);
            
            if (isset($decoded_contents[$this->logName])) {
                $this->log_cTime = $decoded_contents[$this->logName];
            }
        }
    }
    
    
    /**
     * Add record to log file
     * 
     * @param string   $logRecord : record to add to the log file
     * @throws Exception : if we can't write log file
     */
    public function addRecord($logRecord) 
    {
        // if we can't write log file throw an exception
        if(!file_put_contents($this->logFile, rtrim($logRecord)."\n", FILE_APPEND)) {
            throw new Exception('Impossible d\'écrire dans le fichier : '.$this->logFile);
        }
        
        // else if log ctime is not set, reset it
        elseif ($this->log_cTime === null || $this->log_cTime === 0 || $this->log_cTime === '') {
            $this->reset_cTime();
        }
    }
    
    /**
     * Get all records from log file
     * 
     * @return mixed : array of records, or false if errors
     */
    public function getRecords() 
    {
        if(file_exists($this->logFile)) { 
            return file($this->logFile); 
        }
        
        return false;
    }
    
    /**
     * Manage archiving, by :
     *      - checking if rotation is required and performing it 
     *      - compressing archived files if required and allowed
     *      - flushing too old log archives (see config for more information)
     */
    public function archive() {

        // Check if rotation is activated on this log handler and 
        // if file exists before doing a file rotation
        if ($this->options->rotate_interval > 0 && file_exists($this->logFile)) {

            // Get the rotation interval time
            $rotInterval = $this->options->rotate_interval * 86400;
            
            // Check if it is time to perfom a rotation
            if ($this->log_cTime + $rotInterval < time()) {
                $archiveFile = $this->rotate();
                
                if ($archiveFile !== false) {
                    if ($this->options->use_compression === true) {
                        $this->compress($archiveFile);
                    }

                    $this->reset_cTime();
                }
            }
        }
        
        // Get all archived files and their local path
        $files = $this->searchArchives();
        
        // Check if flush is activated on this log handler and
        // if files is not null or false
        if ($this->options->flush_interval > 0 && $files !== false && $files !== null) {
            
            // Get the flushing interval time
            $flushInterval = $this->options->flush_interval * 86400;
            
            foreach ($files as $file) {
                // Check if it is time to perform a flush (add rootDir on local file path)
                if (filectime($this->rootDir.'/'.$file) + $flushInterval < time()) {
                    unlink($this->rootDir.'/'.$file);
                }
            }
        }
    }
    
    /**
     * Rotate log file
     * 
     * @return mixed : archive file path if rotation has been done, false otherwise
     * @throws Exception : if we can't overwrite log file or if we can't copy it
     */
    public function rotate()
    {
        // Get Sql timestamp
        $date = new \BFW\Date();
        $ts = strtr($date->getSql(false), array(':' => '', ' ' => '_'));
        
        // Extract raw file name and path plus extension
        $dotPos = strpos($this->logFile, '.');
        $rawFile = substr($this->logFile, 0, $dotPos);
        $extFile = substr($this->logFile, $dotPos + 1);
        
        $archiveFile = $rawFile.'_'.$ts.'.'.$extFile;
        
        if (copy($this->logFile, $archiveFile)) {
            // If we can't overwrite the file, we throw an exception
            if(file_put_contents($this->logFile, '') === false) {
                throw new Exception('Impossible d\'écraser le fichier : '.$this->logFile);
            }
            
            else {
                return $archiveFile;
            }
        }
        
        // If we can't copy the file, we throw an exception
        else {
            throw new Exception('Impossible de créer le fichier : '.$archiveFile.
                ', copie du fichier : '.$this->logFile);
        }
        
        return false;
    }
    
    /**
     * Compress file using Zlib (gzip)
     * 
     * @param string $file  : file path
     * @param int    $level : compression level from 0 to 9
     * @return mixed : return file path or false if errors
     * @throws Exception : if we get errors while opening or creating files
     * 
     * Source : http://php.net/manual/en/function.gzwrite.php#34955
     */
    private function compress($file, $level = 9) 
    {
        // Construct full path to gzip file and file access mode
        $gzFile = $file.'.gz';
        $mode = 'wb'.$level;
        
        // Open log and gzip file
        $fp_gz=gzopen($gzFile,$mode);
        $fp_log=fopen($file,'rb');
        
        if ($fp_gz === false) {
            throw new Exception('Zlib error while creating '.$gzFile);
        }
        
        elseif ($fp_log === false) {
            throw new Exception('error while opening log file '.$file);
        }
        
        else {
            while(!feof($fp_log)) {
                // Write gzip file in 512kb chunks to avoid memory overload
                bzwrite($fp_gz, fread($fp_log, 512 * 1024));
            }
            
            // Close all files, and remove log file and return gzip file path if closing is ok
            if (fclose($fp_log) !== false && gzclose($fp_gz) !== false) {
                unlink($file);
                return $gzFile;
            }
        }
        
        return false;
    }
   
	
    /**
     * Search log achives in the parent directory and ONLY RETURN LOCAL PATH !
     * 
     * @return mixed : array of files found or false if there is no file
     */
    private function searchArchives() 
    {
        $files = scandir($this->rootDir);
        
        $dotPos = strrpos($this->logName, '.');
        $ext = substr($this->logName, $dotPos);

        if ($files !== false) {
            // Remove all directories, all files that not contains log extention 
            // and all files that contain logName.logExtention
            foreach ($files as $key => $file) {
                if (is_dir($file) || strpos($file, $ext) === false 
                    || strpos($file, $this->logName) !== false) {
                    unset($files[$key]);
                }
            }
        }

        if ($files === null) {
            return false;
        }
        
        return $files;
    }
    
    /**
     * Reset cTime for the main log file
     * 
     * @throws Exception if we can't overwrite the ctime file
     */
    private function reset_cTime() {
        $this->log_cTime = time();
        $decoded_contents = array();
        
        // Get ctime file, decode its json and put it into log ctime var
        if (file_exists($this->cTimeFile)) {
            $contents = file_get_contents($this->cTimeFile);
            $decoded_contents = json_decode($contents, true);
        }
        
        // Set ctime in an array and encode this array into a json 
        $decoded_contents[$this->logName] = $this->log_cTime;
        $contents = json_encode($decoded_contents);
        
        // Save json contents into the ctime file
        if (file_put_contents($this->cTimeFile, $contents) === false) {
            throw new Exception('Cannot save ctime file '.$this->cTimeFile);
        }
    }
}