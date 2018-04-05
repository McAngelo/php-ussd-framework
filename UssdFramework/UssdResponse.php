<?php

/*
 *  (c) 2016. SMSGH
 */

namespace UssdFramework;

/**
 * Represents ussd responses to be sent to SMSGH.
 * 
 * @author Aaron Baffour-Awuah
 */
class UssdResponse {
    
    /**
     * @var string
     */
    const RESPONSE_TYPE_RESPONSE = "Response";
    
    /**
     * @var string
     */
    const RESPONSE_TYPE_RELEASE = "Release";
    
    /**
     * @var string
     */
    private $_type;
    
    /**
     * @var string
     * @access private
     */
    private $_message;
    
    /**
     * @var string
     */
    private $_clientState;
    
    /**
     * @var \Exception
     */
    private $_exception;
    
    /**
     * @var string
     */
    private $_nextRoute;
    
    /**
     * @var bool
     */
    private $_redirect;
    
    /**
     * @var bool
     */
    private $_autoDialOn;

    /**
     * Creates new UssdResponse instance.
     */
    function __construct() {
        $this->_redirect = false;
        $this->_autoDialOn = true;
    }

    /**
     * Gets the type of the ussd response. SMSGH uses this to determine whether
     * or not session has ended.
     * 
     * @return string type of ussd response
     */
    function getType() {
        return $this->_type;
    }

    /**
     * Sets the type of the ussd response.
     * 
     * @param string $type type of ussd response. SMSGH expects it to be one of
     *               the RESPONSE_TYPE_* constants of this class.
     */
    function setType($type) {
        if ( ! is_string($type)) {
            throw new \InvalidArgumentException('"type" argument is not ' .
                    'a string: ' . var_export($type, true));
        }
        $this->_type = $type;
    }

    /**
     * Gets ussd response message.
     * 
     * @return string ussd response message.
     */
    function getMessage() {
        return $this->_message;
    }

    /**
     * Sets ussd response message.
     * 
     * @param string $message ussd response message.
     */
    function setMessage($message) {
        if ( ! is_string($message)) {
            throw new \InvalidArgumentException('"message" argument is not ' .
                    'a string: ' . var_export($message, true));
        }
        $this->_message = $message;
    }

    /**
     * Used internally by framework. See SMSGH USSD documentation 
     * for details.
     * 
     * @return string
     */
    function getClientState() {
        return $this->_clientState;
    }

    /**
     * Used internally by framework. See SMSGH USSD documentation 
     * for details. Please do not edit!!!
     * 
     * @param string $clientState
     */
    function setClientState($clientState) {
        if ($clientState !== null && ! is_string($clientState)) {
            throw new \InvalidArgumentException('"clientState" argument is not ' .
                    'a string: ' . var_export($clientState, true));
        }
        $this->_clientState = $clientState;
    }

    /**
     * Gets any exception which occured during the processing of a 
     * ussd request.
     * 
     * @return \Exception ussd request processing error or null if no such 
     *                    error occured.
     */
    function getException() {
        return $this->_exception;
    }

    /**
     * Sets any exception which occured during the processing of a ussd
     * request.
     * 
     * @param \Exception $exception a ussd request processing error
     */
    function setException($exception) {
        if ($exception !== null && ! ($exception instanceof \Exception)) {
            throw new \InvalidArgumentException('"exception" argument is not ' .
                    'an Exception: ' . var_export($exception, true));
        }
        $this->_exception = $exception;
    }

    /**
     * @return string
     */
    function getNextRoute() {
        return $this->_nextRoute;
    }

    /**
     * @param string $nextRoute
     */
    function setNextRoute($nextRoute) {
        if ($nextRoute !== null && ! is_string($nextRoute)) {
            throw new \InvalidArgumentException('"nextRoute" argument is not ' .
                    'a string: ' . var_export($nextRoute, true));
        }
        $this->_nextRoute = $nextRoute;
    }

    /**
     * @return bool
     */
    function isRedirect() {
        return $this->_redirect;
    }

    /**
     * @param bool $redirect
     */
    function setRedirect($redirect) {
        if ( ! is_bool($redirect)) {
            throw new \InvalidArgumentException('"redirect" argument is not ' .
                    'a boolean: ' . var_export($redirect, true));
        }
        $this->_redirect = $redirect;
    }
    
    /**
     * @return bool
     */
    function isRelease() {
        return $this->_nextRoute === null;
    }

    /**
     * Gets whether any ongoing auto dial processing should be continued or
     * broken.
     * 
     * @return bool true (by default) to continue any ongoing auto dial processing; 
     * false to break it.
     */
    function isAutoDialOn() {
        return $this->_autoDialOn;
    }

    /**
     * Sets whether or not auto dial processing should be continued.
     * For example, framework uses this property to end auto dial when 
     * input validation fails for menus and form options.
     * 
     * @param bool $autoDialOn true to continue auto dial processing, false
     *             to end it.
     */
    function setAutoDialOn($autoDialOn) {
        if ( ! is_bool($autoDialOn)) {
            throw new \InvalidArgumentException('"autoDialOn" argument is not ' .
                    'a boolean: ' . var_export($autoDialOn, true));
        }
        $this->_autoDialOn = $autoDialOn;
    }

    /**
     * @return string
     */
    function __toString() {
        return "UssdResponse{" . "type=" . $this->_type . 
                ", message=" . $this->_message . 
                ", clientState=" . $this->_clientState . 
                ", exception=" . 
                ($this->_exception ? $this->_exception->getMessage() : null) . 
                ", nextRoute=" . $this->_nextRoute . 
                ", redirect=" . $this->_redirect . 
                ", autoDialOn=" . $this->_autoDialOn . '}';
    }
    
    /**
     * @param string $message
     * @param string $nextRoute
     * 
     * @return UssdResponse
     */
    static function render($message, $nextRoute = null) {
        $type = $nextRoute === null
                ? self::RESPONSE_TYPE_RELEASE
                : self::RESPONSE_TYPE_RESPONSE;
        $response = new self;
        $response->setType($type);
        $response->setMessage($message);
        $response->setNextRoute($nextRoute);
        return $response;
    }
    
    /**
     * @param string $nextRoute
     * 
     * @return UssdResponse
     */
    static function redirect($nextRoute) {
        $response = new UssdResponse();
        $response->setNextRoute($nextRoute);
        $response->setRedirect(true);
        return $response;
    }
    
    /**
     * @param UssdResponse $instance
     * 
     * @return string
     */
    static function toJson($instance) {
        $arr = array('Type' => $instance->getType(),
            'Message' => $instance->getMessage(),
            'ClientState' => $instance->getClientState());
        $json = json_encode($arr);
        return $json;
    }
}
