<?php

/*
 *  (c) 2016. SMSGH
 */

namespace UssdFramework;

/**
 * Used to gather data from the ussd app user, for subsequent processing.
 * 
 * The data are gathered using one or more {@link UssdInput} screens. The
 * ussd app user is guided through each screen, and then after the last one
 * is filled, an action is invoked to fetch that data using the
 * {@link UssdController#getFormData()} method, after which it may now begin
 * processing.
 * 
 * @author Aaron Baffour-Awuah
 */

class UssdForm 
{
    /**
     * @var array indexed array of UssdInput objects.
     */
    private $_inputs;
    
    /**
     * @var int
     */
    private $_processingPosition;
    
    /**
     * @var string
     */
    private $_controller;
    
    /**
     * @var string
     */
    private $_action;
    
    /**
     * @var array associative array of string keys and string values.
     */
    private $_data;
    
    /**
     * Creates a new UssdForm instance and initialize its inputs and data
     * properties to empty arrays.
     * 
     * @param string $action
     * @param string $controller Optional. Defaults to null.
     */
    function __construct($action, $controller = null) {
        $this->action($action);
        $this->controller($controller);
        
        $this->_processingPosition = 0;
        $this->_inputs = array();
        $this->_data = array();
    }

    /**
     * @return array array of UssdInput objects.
     */
    function getInputs() {
        return $this->_inputs;
    }

    /**
     * @param array $inputs
     */
    function inputs($inputs) {
        if ( ! is_array($inputs)) {
            throw new \InvalidArgumentException(
                    '"inputs" argument is not an array: ' .
                    print_r($inputs, true));
        }
        
        $this->_inputs = $inputs;
        return $this;
    }
    
    /**
     * @param UssdInput input
     */
    function addInput($input) {
        if ( ! is_object($input)) {
            throw new \InvalidArgumentException(
                    '"input" argument is not a object: ' .
                    print_r($input, true));
        }
        array_push($this->_inputs, $input);
        return $this;
    }

    /**
     * @return int
     */
    function getProcessingPosition() {
        return $this->_processingPosition;
    }

    /**
     * @param int processingPosition
     */
    function processingPosition($processingPosition) {
        if ( ! is_int($processingPosition)) {
            throw new \InvalidArgumentException(
                    '"processingPosition" argument is not an integer: ' .
                    print_r($processingPosition, true));
        }
        
        $this->_processingPosition = $processingPosition;
        return $this;
    }

    /**
     * @return UssdController
     */
    function getController() {
        return $this->_controller;
    }

    /**
     * @param string controller
     */
    function controller($controller) {
        if ($controller !== null && ! is_string($controller)) {
            throw new \InvalidArgumentException(
                    '"controller" argument is not a string: ' .
                    print_r($controller, true));
        }
        $this->_controller = $controller;
        return $this;
    }

    /**
     * @return string 
     */
    function getAction() {
        return $this->_action;
    }

    /**
     * @param string action
     */
    function action($action) {
        if ( ! is_string($action)) {
            throw new \InvalidArgumentException(
                    '"action" argument is not a string: ' .
                    print_r($action, true));
        }
        $this->_action = $action;
        return $this;
    }

    /**
     * @return array associative array
     */
    function getData() {
        return $this->_data;
    }

    /**
     * @param array $data associative array of strings to strings
     */
    function data($data) {
        if ( ! is_array($data)) {
            throw new \InvalidArgumentException(
                    '"data" argument is not an array: ' .
                    print_r($data, true));
        }
        
        $this->_data = $data;
        return $this;
    }
    
    /**
     * @return string
     */
    function render() {
        if ($this->_processingPosition < 0 || $this->_processingPosition >=
                count($this->_inputs)) {
            throw new FrameworkException(sprintf('Invalid processing '
                    . 'position (%d) for inputs of size %d',
                    $this->_processingPosition, count($this->_inputs)));
        }
        $currentInput = $this->_inputs[$this->_processingPosition];
        if ( ! is_object($currentInput)) {
            throw new FrameworkException('Form input at index ' .
                    $this->_processingPosition . ' is not an object: ' .
                    print_r($currentInput, true));
        }
        return $currentInput->render();
    }

    /**
     * @return string
     */
    function __toString() {
        return "UssdForm{" . "inputs=" . print_r($this->_inputs. true) .
                ", processingPosition=" . 
                $this->_processingPosition .
                ", controller=" . $this->_controller .
                ", action=" . $this->_action . 
                ", data=" . print_r($this->_data, true) . '}';
    }
    
    /**
     * 
     * @param UssdForm $instance
     * @return string
     */
    static function serialize($instance) {
        $arr = array(
            $instance->getAction(),
            $instance->getController(),
            $instance->getProcessingPosition(),
            json_encode($instance->getData())
        );
        if ($instance->getInputs()) {
            foreach ($instance->getInputs() as $input) {
                array_push($arr, UssdInput::serialize($input));
            }
        }
        $serialized = UssdUtils::marshallIndexedArray($arr);
        return $serialized;
    }
    
    /**
     * 
     * @param string $serialized
     * @return UssdForm
     */
    static function deserialize($serialized) {
        $arr = UssdUtils::unmarshallIndexedArray($serialized);
        $action = $arr[0];
        $controller = $arr[1];
        $processingPosition = intval($arr[2]);
        $data = json_decode($arr[3], true);
        $inputs = array();
        for ($i = 4; $i < count($arr); $i++) {
            array_push($inputs, UssdInput::deserialize($arr[$i]));
        }
        $instance = new UssdForm($action, $controller);
        $instance->processingPosition($processingPosition);
        $instance->data($data)->inputs($inputs);
        return $instance;
    }
}

