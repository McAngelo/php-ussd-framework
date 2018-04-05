<?php

/**
 * Description of core
 *
 * @author roby
 */

namespace Logs;

class BaseLog {
    const DEBUG = 1;
    const INFO = 2;
    const WARN = 3;
    const ERROR = 4;
    
    /**
     *
     * @var int
     */
    private $_logLevel;
    
    /**
     *
     * @var string
     */
    private $_name;
    
    /**
     *
     * @var string
     */
    private $_fileAppender;

    public function __construct($fileAppender, $name, $logLevel = self::DEBUG) {
        $this->_fileAppender = $fileAppender;
        $this->_name = $name;
        $this->_logLevel = $logLevel;
    }
    
    public function debug($data) {
        $this->log(self::DEBUG, $data);
    }
    
    public function info($data) {
        $this->log(self::INFO, $data);
    }
    
    public function warn($data) {
        $this->log(self::WARN, $data);
    }
    
    public function error($data) {
        $this->log(self::ERROR, $data);
    }

    public function log($logLevel, $data) {
        if ( ! $this->isLoggable($logLevel)) {
            return;
        }
        
        $fileHandle = fopen($this->_fileAppender, 'a');
        if ( ! $fileHandle) {
            error_log("Couldn't open log file for writing: {$this->_fileAppnder}");
            return;
        }
        
        $param = '';
        $param .= gmdate('Y-m-d H:i:s e') . ' ';
        $param .= '['. $this->_name . '] ';
        $param .= self::getLogLevelName($logLevel) . ' ';
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $origin = $_SERVER['REMOTE_ADDR'];
        if (is_array($data)) {
            foreach ($data as $item) {
                $param .= "$item ";
            }
        }
        else {
            $param .= "$data ";
        }
        $param .= "\r\n";
        fwrite($fileHandle, $param);
        fclose($fileHandle);
    }

    public function isLoggable($logLevel) {
        return $this->_logLevel <= $logLevel;
    }

    public static function getLogLevelName($logLevel) {
        switch ($logLevel) {
            case self::DEBUG:
                return 'DEBUG';
            case self::INFO:
                return 'INFO';
            case self::WARN:
                return 'WARN';
            case self::ERROR:
                return 'ERROR';
            default:
                return 'UNKNOWN';
        }
    }

    /*public static function getInstance()
    {
        if ( is_null(self::$instance) ) {
            self::$instance = new self();
            return self::$instance;
        }
        else {
            return self::$instance;
        }
    }*/
}
