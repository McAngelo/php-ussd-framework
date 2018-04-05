<?php

/*
 *  (c) 2016. SMSGH
 */

namespace UssdFramework;

/**
 * Abstraction of logging functionality.
 *
 * @author Aaron
 */
class Loggers {
    
    /**
     *
     * @var callable
     */
    private static $_debugLogger;
    
    /**
     *
     * @var callable
     */
    private static $_errorLogger;
    
    static function getDebugLogger() {
        return Loggers::$_debugLogger;
    }
    
    static function setDebugLogger($debugLogger) {
        Loggers::$_debugLogger = $debugLogger;
    }
    
    static function getErrorLogger() {
        return Loggers::$_errorLogger;
    }
    
    static function setErrorLogger($errorLogger) {
        Loggers::$_errorLogger = $errorLogger;
    }
}
