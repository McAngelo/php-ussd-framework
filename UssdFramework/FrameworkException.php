<?php

/*
 *  (c) 2016. SMSGH
 */

namespace UssdFramework;

/**
 * Base class of exceptions thrown from framework code.
 * 
 * @author Aaron Baffour-Awuah
 */
class FrameworkException extends \Exception {

    /**
     * @param string $message
     * @param int $code
     * @param Exception $previous
     */
    function __construct($message = '', $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
