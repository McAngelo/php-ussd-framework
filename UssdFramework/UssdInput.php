<?php

/*
 *  (c) 2016. SMSGH
 */

namespace UssdFramework;

/**
 *
 * @author Aaron Baffour-Awuah
 */
class UssdInput {
    
    /**
     * @var string
     */
    private $_name;
    
    /**
     * @var string
     */    
    private $_displayName;
    
    /**
     * @var string
     */
    private $_header;
    
    /**
     * @var string
     */
    private $_message;
    
    /**
     * @var array indexed array of Option instances.
     */
    private $_options;
    
    function __construct($name, $inputName = null) {
        $this->_displayName = $inputName;
        $this->_name = $name;
        $this->name($this->_name);
        $this->_options = array();
    }
    
    /**
     * @return string
     */
    function render() {
        if ($this->_message !== null) {
            return $this->_message;
        }
        
        $messageBuilder = '';
        if ($this->_header !== null) {
            $messageBuilder .= $this->_header . "\n";
        }
        
        $displayName = $this->_displayName;
        if ($displayName === null) {
            $displayName = $this->_name;
        }
        if ($this->_options) {
            $messageBuilder .= $displayName . ":\n";
            //$messageBuilder .= 'Choose ' . $displayName . ":\n";
            for ($i = 0; $i < count($this->_options); $i++) {
                $option = $this->_options[$i];
                if ( ! is_object($option)) {
                    throw new FrameworkException('Form input option at index ' .
                            $i . ' is not an object: ' .
                            print_r($option, true));
                }
                $messageBuilder .= ($i+1) . '. ' . $option->getDisplay();
                $messageBuilder .= "\n";
            }
        }
        else {
            $messageBuilder .= $displayName . ":\n";
            //$messageBuilder .= 'Enter ' . $displayName . ":\n";
        }
        return $messageBuilder;
    }

    /**
     * @return string
     */
    function getName() {
        return $this->_name;
    }

    function name($name) {
        if ( ! is_string($name)) {
            throw new \InvalidArgumentException('"name" argument is '
                    . 'not a string: ' . print_r($name, true));
        }
        
        $this->_name = $name;
        return $this;
    }

    /**
     * @return string
     */
    function getDisplayName() {
        return $this->_displayName;
    }

    function displayName($displayName) {
        if ($displayName !== null && ! is_string($displayName)) {
            throw new \InvalidArgumentException('"displayName" argument is '
                    . 'not a string: ' . print_r($displayName, true));
        }
        
        $this->_displayName = $displayName;
        return $this;
    }

    function getHeader() {
        return $this->_header;
    }

    function header($header) {
        if ($header !== null && ! is_string($header)) {
            throw new \InvalidArgumentException('"header" argument is '
                    . 'not a string: ' . print_r($header, true));
        }
        
        $this->_header = $header;
        return $this;
    }

    function getMessage() {
        return $this->_message;
    }

    function message($message) {
        if ($message !== null && ! is_string($message)) {
            throw new \InvalidArgumentException('"message" argument is '
                    . 'not a string: ' . print_r($message, true));
        }
        
        $this->_message = $message;
        return $this;
    }

    function getOptions() {
        return $this->_options;
    }

    function options($options) {
        if ( ! is_array($options)) {
            throw new \InvalidArgumentException('"options" argument is '
                    . 'not an array: ' . print_r($options, true));
        }
        
        $this->_options = $options;
        return $this;
    }
    
    /**
     * @param Option $$option 
     * @return UssdInput
     */
    function addOption($option) {
        if ( ! is_object($option)) {
            throw new \InvalidArgumentException(
                    '"option" argument is not an object: ' .
                    print_r($option, true));
        }
        array_push($this->_options, $option);
        return $this;
    }
    
    /**
     * @return bool
     */
    function hasOptions() {
        return count($this->_options) > 0;
    }
    
    /**
     * @return string
     */
    function __toString() {
        return 'UssdInput{' . 'name=' . $this->_name . ', displayName=' . 
                $this->_displayName . ', header=' . $this->_header . ', message=' . 
                $this->_message . ', options=' . 
                print_r($this->_options, true) . '}';
    }

    /**
     * 
     * @param UssdInput $instance
     * @return string
     */
    public static function serialize($instance) {
        $arr = array(
            $instance->getName(),
            $instance->getDisplayName(),
            $instance->getMessage(),
            $instance->getHeader(),
        );
        if ($instance->getOptions()) {
            foreach ($instance->getOptions() as $option) {
                array_push($arr, Option::serialize($option));
            }
        }
        $serialized = UssdUtils::marshallIndexedArray($arr);
        return $serialized;
    }

    /**
     * 
     * @param string $serialized
     * @return UssdInput
     */
    public static function deserialize($serialized) {
        $arr = UssdUtils::unmarshallIndexedArray($serialized);
        $name = $arr[0];
        $displayName = $arr[1];
        $message = $arr[2];
        $header = $arr[3];
        $options = array();
        for ($i = 4; $i < count($arr); $i++) {
            array_push($options, Option::deserialize($arr[$i]));
        }
        $instance = new UssdInput($name);
        $instance->displayName($displayName);
        $instance->message($message);
        $instance->header($header)->options($options);
        return $instance;
    }
}
    
class Option {
    
    /**
     * @var string
     */
    private $_display;
    
    /**
     * @var string
     */
    private $_value;

    function __construct($display, $value = null) {
        $this->display($display);
        if ($value !== null && ! is_string($value)) {
            throw new \InvalidArgumentException(
                    '"value" argument is not a string: ' .
                    print_r($value, true));
        }
        if ($value === null) {
            $value = $display;            
        }
        $this->value($value);
    }
    
    function getDisplay() {
        return $this->_display;
    }
    
    function display($display) {
        if (! is_string($display)) {
            throw new \InvalidArgumentException(
                    '"display" argument is not a string: ' .
                    print_r($display, true));
        }
        $this->_display = $display;
        return $this;
    }
            
    function getValue() {
        return $this->_value;
    }
    
    function value($value) {
        if (! is_string($value)) {
            throw new \InvalidArgumentException(
                    '"value" argument is not a string: ' .
                    print_r($value, true));
        }
        $this->_value = $value;
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    function __toString() {
        return 'Option{' . 'display=' . $this->_display . ', value=' . 
                $this->_value . '}';
    }

    /**
     * 
     * @param Option $instance
     * @return string
     */
    public static function serialize($instance) {
        $serialized = UssdUtils::marshallIndexedArray(array(
            $instance->getDisplay(), $instance->getValue()
        ));
        return $serialized;
    }

    /**
     * 
     * @param string $serialized
     * @return Option
     */
    public static function deserialize($serialized) {
        list($display, $value) = UssdUtils::unmarshallIndexedArray($serialized);
        $instance = new Option($display, $value);
        return $instance;
    }
}
