<?php

/*
 *  (c) 2016. SMSGH
 */

namespace UssdFramework;

/**
 * Represents the choices in ussd menus and the action to take upon their
 * selection.
 * 
 * @see UssdMenu
 * 
 * @author Aaron Baffour-Awuah
 */
class UssdMenuItem {
    
    /**
     * @var string
     */
    private $_index;
    
    /**
     * @var string
     */
    private $_display;
    
    /**
     * @var string
     */
    private $_controller;
    
    /**
     * @var string
     */
    private $_action;

    /**
     * Creates a new ussd menu item.
     * 
     * @param string $index the choice that the user texts to select the 
     *                      menu item. This is usually the index of the 
     *                      menu item in the menu item list.
     * @param string $display the text of the ussd menu item. This text will be 
     *                        prepended by the index string 
     *                        when {@link UssdMenu#render()} is called.
     * @param string $action the action to take when the menu item is selected.
     * @param string $controller the controller in which the action 
     *                           argument will be called. If null, then 
     *                           the action will be called on the controller
     *                           which renders the menu.
     */
    function __construct($index, $display, 
            $action, $controller=null) {
        $this->index($index)->display($display)->controller($controller);
        $this->action($action);
    }

    /**
     * Gets the message that should be texted to select the menu item.
     * @return choice string by which menu item will be selected.
     */
    function getIndex() {
        return $this->_index;
    }

    /**
     * Sets the message that should be texted to select the menu item.
     * 
     * @param string $index the choice string or null to get the current value
     *                      of the index property.
     */
    function index($index) {
        if ( ! is_string($index)) {
            throw new \InvalidArgumentException(
                    '"index" argument is not a string: ' .
                    print_r($index, true));
        }
        $this->_index = $index;
        return $this;
    }

    /**
     * Gets the text of the menu item (excluding choices/indices 
     * like 1., *.)
     * 
     * @return string the text of the menu item or null to get the
     *                current value of the display property.
     */
    function getDisplay() {
        return $this->_display;
    }

    /**
     * Sets the text of the menu item (excluding choices/indices 
     * like 1., *.)
     * 
     * @param string $display the text of the menu item or null to get the
     *                        current value of the display property.
     */
    function display($display) {
        if ( ! is_string($display)) {
            throw new \InvalidArgumentException(
                    '"display" argument is not a string: ' .
                    print_r($display, true));
        }
        $this->_display = $display;
        return $this;
    }

    /**
     * Gets the controller whose action will be invoked when 
     * the menu item is selected.
     * 
     * @return string name of controller or null to get the
     *                current value of the controller property.
     */
    function getController() {
        return $this->_controller;
    }

    /**
     * Sets the controller whose action will be invoked when 
     * the menu item is selected.
     * 
     * @param string $controller name of controller or null to get the
     *                           current property of the controller property.
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
     * Gets the action which is invoked when the menu item is selected.
     * 
     * @return string name of action which gets called when menu item is 
     *                selected; or null to get the current value of the
     *                current value of the action property.
     */
    function getAction() {
        return $this->_action;
    }
    
    /**
     * Sets the action which is invoked when the menu item is selected.
     * 
     * @param string $action name of action which gets called when menu item is 
     *                       selected; or null to get the current value of the
     *                       current value of the action property.
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
     * 
     * @return string
     */
    function __toString() {
        return 'UssdMenuItem{' . 'index=' . $this->_index . 
                ', display=' . $this->_display . 
                ', controller=' . $this->_controller . 
                ', action=' . $this->_action . '}';
    }

    /**
     * 
     * @param UssdMenuItem $instance
     * @return string
     */
    public static function serialize($instance) {
        $serialized = UssdUtils::marshallIndexedArray(array(
            $instance->getIndex(), $instance->getDisplay(),
            $instance->getController(), $instance->getAction()
        ));
        return $serialized;
    }

    /**
     * 
     * @param string $serialized
     * @return UssdMenuItem
     */
    public static function deserialize($serialized) {
        list($index, $display, $controller, $action) =
            UssdUtils::unmarshallIndexedArray($serialized);
        $instance = new UssdMenuItem($index, $display, $action, $controller);
        return $instance;
    }
}
