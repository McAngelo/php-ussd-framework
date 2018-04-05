<?php

/*
 *  (c) 2016. SMSGH
 */

namespace UssdFramework;

/**
 * Used to display menus in ussd apps.
 * <p>
 * A menu presents the ussd app user with choices, usually numbered choices.
 * e.g.
 * <p>
 * Select fruit:
 * <ol>
 *  <li>Apple
 *  <li>Banana
 *  <li>Pawpaw
 * </ol>
 * To act on the user's choice, one supplies {@link UssdMenuItem} instances
 * which contain the controller and action to call for a given choice.
 * 
 * @author Aaron Baffour-Awuah
 */
class UssdMenu {
    
    /**
     * @var string
     */
    private $_header;
    
    /**
     * @var string
     */
    private $_footer;
    
    /**
     * @var string
     */
    private $_message;
    
    /**
     * @var array indexed array of UssdMenuItem objects.
     */
    private $_items;

    /**
     * Creates a new instance with an empty list of
     * {@link UssdMenuItem} instances.
     */
    function __construct() {
        $this->_items = array();
    }

    /**
     * Gets the menu's header.
     * @return string menu's header or null if menu will be displayed without a
     *                header.
     */
    function getHeader() {
        return $this->_header;
    }
    
    /**
     * Sets the menu's header.
     * @param string $header menu's header. Can be null.
     * @return UssdMenu this instance to enable chaining of property mutator 
     *                  methods.
     */
    function header($header) {
        if ($header !== null && ! is_string($header)) {
            throw new \InvalidArgumentException(
                    '"header" argument is not a string: ' .
                    print_r($header, true));
        }
        
        $this->_header = $header;
        return $this;
    }

    /**
     * Gets the menu's footer.
     * @return string menu's footer or null if menu will be displayed without
     *                a footer.
     */
    function getFooter() {
        return $this->_footer;
    }

    /**
     * Sets the menu's footer.
     * @param string $footer menu's footer. Can be null.
     * @return UssdMenu this instance to enable chaining of property mutator 
     *                  methods.
     */
    function footer($footer) {
        if ($footer !== null && ! is_string($footer)) {
            throw new \InvalidArgumentException(
                    '"footer" argument is not a string: ' .
                    print_r($footer, true));
        }
        
        $this->_footer = $footer;
        return $this;
    }

    /**
     * Gets the message which will be used to render the entire menu.
     * @return string entire menu's representation or null to use the default
     *                way of rendering menus.
     */
    function getMessage() {
        return $this->_message;
    }
    
    /**
     * Hook for clients to override how a menus is displayed.
     * @param string $message the message which will be used to render the entire 
     * menu and skip the default of using headers, menu items and footers. Can
     * be null to indicate that the default way be used.
     * @return UssdMenu this instance to enable chaining of property mutators.
     */
    function message($message) {
        if ($message !== null && ! is_string($message)) {
            throw new \InvalidArgumentException(
                    '"message" argument is not a string: ' .
                    print_r($message, true));
        }
        
        $this->_message = $message;
        return $this;
    }

    /**
     * Gets the list of menu items.
     * @return array indexed array of menu items.
     */
    function getItems() {
        return $this->_items;
    }

    /**
     * Sets the list of menu items.
     * @param array $items indexed array of UssdMenuItem instances.
     * @return UssdMenu this to enable chaining of mutator methods.
     */
    function items($items) {
        if ( ! is_array($items)) {
            throw new \InvalidArgumentException(
                    '"items" argument is not an array: ' .
                    print_r($items, true));
        }
        
        $this->_items = $items;
        return $this;
    }
    
    /**
     * Adds a new menu item to the existing menu item list.
     * @param UssdMenuItem $item new menu item.
     * @return UssdMenu this instance to enable chaining of mutator methods.
     */
    function addItem($item) {
        if ( ! is_object($item)) {
            throw new \InvalidArgumentException(
                    '"item" argument is not an object: ' .
                    print_r($item, true));
        }
        array_push($this->_items, $item);
        return $this;
    }
    
    /**
     * Creates and adds a new menu item, giving it an index of 1 more than
     * the size of the current menu item list.
     * @param string $displayName the text of the menu item.
     * @param string $action the action to call when the menu is selected. 
     *                       The action will be called from the controller 
     *                       which renders the menu instance via 
     *                       {@link #render()}
     * @param string controller the controller the action argument belongs to. 
     *                          If null, then the action will be called from 
     *                          the controller which renders the menu instance 
     *                          via {@link #render()}
     * @return UssdMenu this instance to enable chaining of mutator methods.
     */
    function createAndAddItem($displayName, $action,
            $controller = null) {
        $index = '' . (count($this->_items) + 1);
        $item = new UssdMenuItem($index, $displayName, $action, $controller);
        array_push($this->_items, $item);
        return $this;
    }
    
    /**
     * Generates the ussd response message to be sent for the
     * menu instance. 
     * <p>
     * If the message property is not null, it is returned
     * immediately. Otherwise, the header, footer and menu item list are
     * combined to generate the message.
     * @return string ussd response message.
     */
    function render() {
        if ($this->_message !== null) {
            return $this->_message;
        }
        
        $messageBuilder = '';
        if ($this->_header !== null) {
            $messageBuilder .= $this->_header . "\n";
        }
        for ($i = 0; $i < count($this->_items); $i++) {
            $item = $this->_items[$i];
            if ( !is_object($item)) {
                throw new FrameworkException("items[$i] is not "
                        + 'an object: ' . print_r($item, true));
            }
            $messageBuilder .= $item->getIndex();
            $messageBuilder .= '. ';
            $messageBuilder .= $item->getDisplay() . "\n";
        }
        if ($this->_footer !== null) {
            $messageBuilder .= $this->_footer;
        }
        return $messageBuilder;
    }
    
    /**
     * 
     * @return string
     */
    function __toString() {
        return 'UssdMenu{' . 'header=' . $this->_header . 
                ', footer=' . $this->_footer .
                ', message=' . $this->_message . 
                ', items=' . print_r($this->_items, true) . '}';
    }
    
    /**
     * 
     * @param UssdMenu $instance
     * @return string
     */
    static function serialize($instance) {
        $arr = array(
            $instance->getHeader(),
            $instance->getFooter(),
            $instance->getMessage()
        );
        if ($instance->getItems()) {
            foreach ($instance->getItems() as $item) {
                array_push($arr, UssdMenuItem::serialize($item));
            }
        }
        $serialized = UssdUtils::marshallIndexedArray($arr);
        return $serialized;
    }
    
    /**
     * 
     * @param string $serialized
     * @return UssdMenu
     */
    static function deserialize($serialized) {
        $arr = UssdUtils::unmarshallIndexedArray($serialized);
        $header = $arr[0];
        $footer = $arr[1];
        $message = $arr[2];
        $items = array();
        for ($i = 3; $i < count($arr); $i++) {
            array_push($items, UssdMenuItem::deserialize($arr[$i]));
        }
        $instance = new UssdMenu();
        $instance->header($header)->footer($footer);
        $instance->message($message)->items($items);
        return $instance;
    }
}
