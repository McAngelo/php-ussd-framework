<?php

/*
 *  (c) 2016. SMSGH
 */

namespace UssdFramework;

/**
 *
 * Exception thrown when a session is not found for a ussd continuation
 * message.
 * 
 * @author Aaron Baffour-Awuah
 */
class SessionNotFoundException extends FrameworkException {

    /**
     * @param string $message
     * @param int $code
     * @param Exception $previous
     */
    function __construct($message = '', $code = 0,
            $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
