<?php

/*
 *  (c) 2016. SMSGH
 */

namespace UssdFramework;

/**
 * Class internal to framework, which is responsible for
 * creating a controller instance and calling upon one of its action
 * methods to process a ussd request and return a ussd response.
 * 
 * @author Aaron Baffour-Awuah
 */
class UssdContext {
    /**
     * @var SessionStore
     */
    private $_store;
    
    /**
     * @var UssdRequest
     */
    private $_request;
    
    /**
     * @var array indexed array of controller namespaces.
     */
    private $_controllerNamespaces;
    
    /**
     * @var UssdDataBag
     */
    private $_dataBag;
    
    function __construct($store, $request, $controllerNamespaces) {
        if ( ! $store) {
            throw new \InvalidArgumentException('"store" argument '
                    . 'cannot be null');
        }
        if ( ! $request) {
            throw new \InvalidArgumentException('"request" argument '
                    . 'cannot be null');
        }
        
        $store->open($request);
        
        $this->_store = $store;
        $this->_request = $request;
        $this->_controllerNamespaces = $controllerNamespaces;
        
        $this->_dataBag = new UssdDataBag($store, $this->getDataBagKey());
    }

    /**
     * Gets the request that is currently being processed.
     * 
     * @return UssdRequest current request.
     */
    function getRequest() {
        return $this->_request;
    }
    
    /**
     * Gets the key used to keep track of the route - controller/action pair -
     * used to handle requests.
     * 
     * @return string key under which next route is kept. 
     */
    function getNextRouteKey() {
        return $this->_request->getSessionId() . '.NextRoute';
    }
    
    /**
     * Gets the key for the data associated with the session of 
     * this context's request.
     * 
     * @return string key for session's data bag.
     */
    function getDataBagKey() {
        return $this->_request->getSessionId() . '.DataBag';
    }

    /**
     * Inserts into session store the next route - controller/action pair.
     * 
     * @param string nextRoute the route to store.
     */
    function sessionSetNextRoute($nextRoute) {
        $this->_store->setValue($this->getNextRouteKey(), $nextRoute);        
    }

    /**
     * Removes next route and data associated with  session of this 
     * context's request.
     */
    function sessionClose() {
        $this->_store->deleteValue($this->getNextRouteKey());
        $this->_store->deleteHash($this->getDataBagKey());
    }

    /**
     * Determines whether or not some session data exists for the session of
     * a context's request.
     * 
     * @return bool true or false if session of this context's request exists 
     * or not respectively.
     */
    function sessionExists() {
        return $this->_store->valueExists($this->getNextRouteKey());
    }

    /**
     * Uses the data used to create a context to create a controller and
     * execute a specified action.
     * 
     * @return UssdResponse response from action executed.
     */
    function sessionExecuteAction() {
        // Get route which has the controller and action to execute.
        $route = $this->_store->getValue($this->getNextRouteKey());
        if ($route === null) {
            throw new FrameworkException('No route was found.');
        }
        
        // Split route up to get the controller and action.
        $periodIndex = strrpos($route, '.');
        if ($periodIndex === false) {
            throw new FrameworkException('Invalid route format. '
                    . 'Must be "SomeController.action".' .
                'Current route is: ' . $route);
        }
        $controllerName = substr($route, 0, $periodIndex);
        $actionName = substr($route, $periodIndex+1);
        
        // First trying loading class using only given controller's name.
        $controllerClass = null;
        try {
            $controllerClass = new \ReflectionClass($controllerName);
        }
        catch (\ReflectionException $ex) { }
        
        // If class was not found, then it may be because it has not
        // been qualified with its package. Use given controller packages
        // to attempt class loading again.
        if ( ! $controllerClass) {
            
            // Use this string builder to store all the classes
            // we tried loading. If we eventually don't find the
            // controller class, we'll let user know what classes
            // were attempted.
            $attemptedClasses = ' ';
            $attemptedClasses .= $controllerName;
            if ($this->_controllerNamespaces) {
                foreach ($this->_controllerNamespaces as $controllerNamespace) {
                    $fullControllerName = $controllerNamespace . '\\' .
                            $controllerName;
                    try {
                        $controllerClass = new \ReflectionClass(
                                $fullControllerName);
                        break;
                    }
                    catch (\ReflectionException $ex) {}
                    $attemptedClasses .= ', ';
                    $attemptedClasses .= $fullControllerName;
                    
                    // Have support for omission of "Controller" suffix from
                    // controller class names.
                    if ( ! preg_match("/Controller$/", $fullControllerName)) {
                        $fullControllerName .= 'Controller';
                        try {
                            $controllerClass = new \ReflectionClass(
                                    $fullControllerName);
                            break;
                        }
                        catch (\ReflectionException $ex) {}
                        $attemptedClasses .= ', ';
                        $attemptedClasses .= $fullControllerName;
                    }
                }
            }
            
            // If controller class wasn't found, throw exception with
            // details of classes we tried loading.
            if ( ! $controllerClass) {                    
                throw new FrameworkException(sprintf(
                        'Class \"%s\" could not be found. Tried to load '
                                . 'the following classes: %s',
                        $controllerName, $attemptedClasses));
            }
        }
        
        // Check that controller class subclasses UssdController.
        if ( ! $controllerClass->isSubclassOf('UssdFramework\UssdController')) {
            trigger_error(sprintf('Class \"%s\" does not '
                    . 'subclass "UssdFramework\UssdController"', 
                    $controllerClass));
        }
        
        // Get action method.
        $action = null;
        try {
            $action = $controllerClass->getMethod($actionName);
        }
        catch (ReflectionException $ex) {
            throw new Framework(sprintf(
                    'Class \"%s\" does not have a public no-arg action '
                            . 'named \"%s\".',
                    $controllerClass->getName(), $actionName));
        }
        
        // Create controller instance. Possible problems include
        // non-public class, non-public constructor, absence of
        // no-arg constructor or error in constructor.
        $controller = null;        
        try {
            $controller = $controllerClass->newInstance();
        }
        catch (\ReflectionException $ex) {
            throw new FrameworkException(sprintf('Failed to create '
                    . 'instance of class \"%s\". Is class a public class having a '
                    . 'public no-arg constructor?', 
                    $controllerClass->getName()));
        }
        
        // Initialize newly created controller.
        $controller->setRequest($this->_request);
        $controller->setDataBag($this->_dataBag);
        $controller->init();
        
        // Now invoke action on controller.
        $response = null;
        try {
            $response = $action->invoke($controller);
        }
        catch (\ReflectionException $ex) {
            throw new FrameworkException('', 0, $ex);
        }
        
        // Check that return value of action is not null, and is
        // a UssdResponse instance.
        if ( ! ($response instanceof UssdResponse)) {
            throw new FrameworkException(sprintf('Action \"%s.%s()\" '
                    . 'did not return an instance of class \"%s\", '
                    . 'but rather returned: %s',
                    $controllerClass.getName(),
                    $actionName, 'UssdFramework\UssdResponse',
                    print_r($response, true)));
        }
        return $response;
    }
    
    /**
     * Gives opportunity to SessionStore implementation to release any
     * resources it might be holding on to.
     * 
     * @return string any data to be saved in response client state.
     */
    function close() {
        return $this->_store->close();
    }
}
