<?php

/**
 * (c) 2016. SMSGH
 */

namespace UssdFramework;

/**
 * Entry point to USSD framework.
 * 
 * @author Aaron Baffour-Awuah
 */
class Ussd  {
    
    /**
     * @var int The limit on the number of internal redirects that may
     *          occur before an error is thrown.
     */
    const MAX_REDIRECT_COUNT = 5;
    
    /**
     * @var string The root url of the online USSD Simulator for setting up
     *             the Access-Control-Allow-Origin header needed by
     *             the cross-origin nature of the USSD simulator to work.
     */
    const DEFAULT_ACCESS_CONTROL_ALLOW_ORIGIN = 'http://apps.smsgh.com';
    
    /**
     * @var int The sliding expiration time for entries in the session store. Set
     *          to 10 seconds more than the USSD API session timeout.
     */
    const SESSION_TIMEOUT_MILLIS = 70000;
    
    /**
     * @var string Character encoding of USSD requests and responses.
     */
    const DEFAULT_ENCODING = 'utf-8';
    
    /**
     * @var Stores\SessionStore
     */
    private $_store;
    
    // Controller- and action-related fields.
    /**
     * @var array indexed array of strings.
     */
    private $_controllerNamespaces;
    /**
     * @var string
     */
    private $_initiationController;
    /**
     * @var string
     */
    private $_initiationAction;
    
    // Log- and error- related fields.
    /**
     * @var string
     */
    private $_errorMessage;
    /**
     * @var UssdRequestListener
     */
    private $_requestListener;
    
    /**
     *  @var string Root url of USSD simulator, just in case simulator is 
     *              moved to a different url, so developers don't have to wait 
     *              for an update to the default value.
     */
    private $_accessControlAllowOrigin;
    
    /**
     * @var int Enables auto dialling 
     */
    private $_maxAutoDialDepth;

    /**
     * Creates a new Ussd instance.
     */
    function __construct() {
        $this->_maxAutoDialDepth = 0;
    }
    
    /**
     * Gets the session store used by the Ussd instance.
     * 
     * @return Stores\SessionStore instance's session store. 
     */
    function getStore() {
        return $this->_store;
    }

    /**
     * Sets the session store used by the Ussd instance.
     * 
     * @param Stores\SessionStore $store new session store for the instance.
     * @return Ussd this instance to allow chaining of property mutators.
     */
    function store($store) {
        $this->_store = $store;
        return $this;
    }

    /**
     * Gets the namespaces in which the ussd controller to handle
     * the current request is located. This enables the setting of 
     * controller names with unqualified names.
     * 
     * @return array indexed array of namespaces for qualifying controller name.
     * 
     * @see #setControllerNamespaces(array) 
     */
    function getControllerNamespaces() {
        return $this->_controllerNamespaces;
    }

    /**
     * Sets the namespaces used to qualify the ussd controller to handle the
     * current request. By default nothing (null) is set.
     * <p>
     * The controller package can be partial. So a controller whose full name is 
     * \com\UssdFramework\demo\controllers\MainController can have its
     * namespace specified as
     * <ol>
     *  <li>\com
     *  <li>\com\smsgh
     *  <li>\com\UssdFramework
     *  <li>\com.UssdFramework\demo
     *  <li>\com\UssdFramework\demo\controllers
     * </ol>
     * 
     * @param array $controllerNamespaces indexed array of namespaces for
     *                                    qualifying controller name.
     * @return Ussd this instance to allow chaining of property mutators.
     */
    function controllerNamespaces($controllerNamespaces) {
        $this->_controllerNamespaces = $controllerNamespaces;
        return $this;
    }

    /**
     * Gets the name of the controller which handles the very first request
     * in a ussd session.
     * 
     * @return string name of controller used to handle initiation requests
     * 
     * @see #setInitiationController(string)
     */
    function getInitiationController() {
        return $this->_initiationController;
    }

    /**
     * Sets the name of the controller used to handle ussd initiation 
     * requests. The name must point to a public concrete subclass
     * of {@link UssdController} with a public no-arg constructor.
     * <p>
     * Leveraging the controllerNamespaces property, some or all
     * of the components of a controller's namespace can be left out. Also,
     * if the name of the controller ends with the "Controller" suffix, this
     * suffix can be left out.
     * <p>
     * So a controller whose full name is 
     * \com\UssdFramework\demo\controllers\MainController can be specified as
     * <ol>
     *  <li>\com\UssdFramework\demo\controllers\MainController
     *  <li>\UssdFramework\demo\controllers\Main
     *  <li>\ussd\demo\controllers\MainController
     *  <li>\demo\controllers\Main
     *  <li>\controllers\MainController
     *  <li>\MainController
     *  <li>\Main
     * </ol>
     * In all but the first name however, the controllerNamespaces property must
     * be set or else the controller will not be found.
     * 
     * @param string $initiationController name of controller.
     * @return Ussd this instance to allow chaining of property mutators.
     */
    function initiationController($initiationController) {
        $this->_initiationController = $initiationController;
        return $this;
    }

    /**
     * Gets the name of the action that will handle initiation requests.
     * 
     * @return string name of action handling initiation requests.
     * 
     * @see #initiationAction(java.lang.String)
     */
    function getInitiationAction() {
        return $this->_initiationAction;
    }

    /**
     * Sets the name of the action that will handle initiation requests.
     * This action must be a public no-arg method of the controller class
     * that returns a {@link UssdResponse} instance.
     * 
     * @param string $initiationAction the name of the action handling
     *                                 ussd initiation requests.
     * @return Ussd this instance to allow chaining of property mutators.
     */
    function initiationAction($initiationAction) {
        $this->_initiationAction = $initiationAction;
        return $this;
    }

    /**
     * Gets the error message returned by the ussd request processing
     * pipeline if an exception is raised at any point. The default is null,
     * which triggers the default behaviour of sending exception.__toString()
     * to the phone.
     * 
     * @return string error message to be used in place of default behaviour, or
     *                null which indicates default behaviour.
     * 
     * @see #setErrorMessage(string) 
     */
    function getErrorMessage() {
        return $this->_errorMessage;
    }

    /**
     * Hook for overriding the default exception handling behaviour of
     * setting the response message to the result of calling toString()
     * on the exception which was raised.
     * <p>
     * It is expected that this property is left as null during development
     * so exception messages are seen immediately, but set to something
     * more meaningful in a production environment so users don't see
     * cryptic error messages.
     * 
     * @param string $errorMessage error message to send as ussd response if an
     *                             exception occurs, or null to stick to default 
     *                             behaviour.
     * @return Ussd this instance to allow chaining of property mutators.
     */
    function errorMessage($errorMessage) {
        $this->_errorMessage = $errorMessage;
        return $this;
    }

    /**
     * Gets the maximum allowable depth for automatically handling 
     * auto dial requests. See {@link Ussd#maxAutoDialDepth(int)} for
     * an explanation to this very useful feature. Default value is 0, which
     * means auto dial requests are not handled specially.
     * 
     * @return int maximum allowable depth.
     * 
     */
    function getMaxAutoDialDepth() {
        return $this->_maxAutoDialDepth;
    }

    /**
     * Sets the maximum allowable depth for treating auto dial requests 
     * specially. Auto dial requests are initiation requests containing
     * extra messages after the ussd code, e.g. *714*2*4# when the ussd
     * code is *714*2#. 
     * <p>
     * In this example, the extra message is "4", and it is possible that the
     * phone user wants be shown the screen that would have appeared if
     * he/she had texted a "4" in response to texting *714*2#.
     * <p>
     * Auto dial support enables ussd application developers to handle this
     * scenario with little effort on their side, by setting this
     * property to a positive value. By default this value is 0, thus
     * disabling auto dialing until it is explicitly requested.
     * <p>
     * Auto dialing however, can be ended during request processing by
     * setting the autoDialOn property of the {@link UssdResponse} to false
     * During validation for example, autoDialOn
     * must be turned off upon invalid input, or else the phone user will 
     * have his/her requests wrongly interpreted.
     * <p>
     * For example, if the ussd code is *110# and the phone user texts
     * *110*9*2# (so that "9" and "2" are the extra messages), and "9"
     * is invalid, the auto dialing session must be broken (and the "2"
     * discarded) for the user to retry his/her input at the second screen.
     * Fortunately, the {@link UssdController} caters for the breaking
     * of auto dialing sessions when processing {@link UssdMenu} items and
     * {@link UssdForm} options. Any other validation however (including
     * {@link UssdForm} with no options) must set to false the
     * autoDialOn property on the UssdResponse upon invalid input.
     * 
     * @param int $maxAutoDialDepth positive value to indicate how far auto dialing
     *                              is handled, or 0 to disable it.
     * @return Ussd this instance to allow chaining of property mutators.
     * 
     * @see UssdResponse#setAutoDialOn(bool)
     */
    function maxAutoDialDepth($maxAutoDialDepth) {
        $this->_maxAutoDialDepth = $maxAutoDialDepth;
        return $this;
    }

    /**
     * Gets any request listener set to listen for pre-process and 
     * post-process events.
     * 
     * @return UssdRequestListener custom request listener
     */
    function getRequestListener() {
        return $this->_requestListener;
    }

    /**
     * Hook for listening to pre-process and post-process events.
     * 
     * @param UssdRequestListener $requestListener custom request listener
     * @return Ussd this instance to allow chaining of property mutators.
     */
    function requestListener($requestListener) {
        $this->_requestListener = $requestListener;
        return $this;
    }

    /**
     * Gets the root url set for the online USSD simulator.
     * 
     * @return string USSD simulator root url or null to use the default
     *                {@link Ussd#DEFAULT_ACCESS_CONTROL_ALLOW_ORIGIN}.
     */
    function getAccessControlAllowOrigin() {
        return $this->_accessControlAllowOrigin;
    }

    /**
     * Sets the root url for the online USSD simulator (or any browser
     * script requiring CORS support for that matter). Default is null,
     * meaning that {@link Ussd#DEFAULT_ACCESS_CONTROL_ALLOW_ORIGIN} will
     * be used.
     * <p>
     * This property is not intended to be set by developers, except 
     * when the USSD simulator is moved and 
     * {@link Ussd#DEFAULT_ACCESS_CONTROL_ALLOW_ORIGIN} still points to it.
     * Or if another browser script requires it in advanced usage scenarios.
     * 
     * @param string $accessControlAllowOrigin value for 
     *                                         Access-Control-Allow-Origin
     *                                         header or null to use 
     *                                         {@link Ussd#DEFAULT_ACCESS_CONTROL_ALLOW_ORIGIN}
     * 
     * @return Ussd this instance to allow chaining of property mutators.
     */
    function accessControlAllowOrigin($accessControlAllowOrigin) {
        $this->_accessControlAllowOrigin = $accessControlAllowOrigin;
        return $this;
    }
    
    /**
     * Alternative point of call for processing USSD requests.
     * <p>
     * Although the SMSGH USSD API works with the POST verb, the
     * USSD simulator works with the OPTIONS verb as well, and thus
     * this method is intended to be the only method to call
     * in order to handle both verbs automatically.
     * 
     * @return bool true if and only if request was handled (HTTP verb was POST
     *              or OPTIONS) 
     */
    function service() {
        if ($this->doOptions()) {
            return true;
        }
        
        return $this->doPost();
    }
    
    /**
     * Implements CORS requirement of USSD simulator.
     * 
     * @return bool true if and only if HTTP verb is OPTIONS.
     */
    function doOptions() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        if (strcasecmp($requestMethod, 'OPTIONS')) {
            return false;
        }
        
        // These CORS headers are necessary for Ussd Simulator at
        // http://apps.smsgh.com/UssdSimulator/ to work with
        // a Ussd app under test.
        header('Access-Control-Allow-Origin: ' .
                ($this->_accessControlAllowOrigin ? 
                $this->_accessControlAllowOrigin :
                self::DEFAULT_ACCESS_CONTROL_ALLOW_ORIGIN));
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Max-Age: 1');
        if (array_key_exists('HTTP_ACCESS_CONTROL_REQUEST_HEADERS', $_SERVER)) {
            $accessControlRequestHeaders = 
                $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'];
            header('Access-Control-Allow-Headers: ' .
                    $accessControlRequestHeaders);
        }
        return true;
    }
    
    /**
     * Main point of call for processing USSD requests.
     * <p>
     * The SMSGH USSD API works with the POST verb only, 
     * and thus this method is intended to be the only method to
     * call from
     * {@link javax.servlet.http.HttpServlet#doPost(
     * javax.servlet.http.HttpServletRequest, 
     * javax.servlet.http.HttpServletResponse)}
     * in order to handle the POST verb.
     * 
     * @return bool true if and only if HTTP verb is POST.
     */
    function doPost() {   
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        if (strcasecmp($requestMethod, 'POST')) {
            return false;
        }
        
        $ussdRequest = $this->fetchRequest();
        
        $ussdResponse = $this->processRequest($ussdRequest);
        
        $this->sendResponse($ussdResponse);
        
        return true;
    }
    
    /**
     * Hook for subclasses to override how {@link UssdRequest} instances are
     * parsed from the HTTP request.
     * 
     * @param request HTTP response
     * @return UssdRequest parsed {@link UssdRequest} instance.
     */
    function fetchRequest() {
        // Set this CORS header early so that if an error occurs and notices are
        // echoed to output stream, they can reach simulator js code in
        // browser.
        header('Access-Control-Allow-Origin: ' .
                ($this->_accessControlAllowOrigin ? 
                $this->_accessControlAllowOrigin :
                        self::DEFAULT_ACCESS_CONTROL_ALLOW_ORIGIN));
        
        $ussdRequestJson = file_get_contents('php://input');
        $ussdRequest = UssdRequest::fromJson($ussdRequestJson);
        return $ussdRequest;
    }
    
    /**
     * Hook for subclasses to override how {@link UssdResponse} instances
     * are sent in the HTTP response.
     * 
     * @param ussdResponse result of ussd request processing.
     * @param response HTTP response
     * @throws ServletException
     * @throws IOException 
     */
    function sendResponse($ussdResponse) {        
        $ussdResponseJson = UssdResponse::toJson($ussdResponse);
        
        // This CORS header is necessary for Ussd Simulator at
        // http://apps.smsgh.com/UssdSimulator/ to work with
        // a Ussd app under test.
        header('Access-Control-Allow-Origin: ' .
                ($this->_accessControlAllowOrigin ? 
                $this->_accessControlAllowOrigin :
                        self::DEFAULT_ACCESS_CONTROL_ALLOW_ORIGIN));
        
        header('Content-Type: application/json;charset=' .
                self::DEFAULT_ENCODING);
        
        echo $ussdResponseJson;
    }

    /**
     * @param UssdRequest request
     * @return UssdResponse
     */
    function processRequest($request) {
        // Set this CORS header early so that if an error occurs and notices are
        // echoed to output stream, they can reach simulator js code in
        // browser.
        header('Access-Control-Allow-Origin: ' .
                ($this->_accessControlAllowOrigin ? 
                $this->_accessControlAllowOrigin :
                        self::DEFAULT_ACCESS_CONTROL_ALLOW_ORIGIN));
        
        if ( ! $request) {
            throw new \InvalidArgumentException('"request" argument '
                    . 'cannot be null');
        }
        
        if ( ! $this->_store) {
            throw new FrameworkException('"store" '
                    . 'property cannot be null.');
        }
        
        if ($this->_requestListener) {
            $this->_requestListener->requestEntering($request);
        }
        
        $context = null;
        try {        
            $context = new UssdContext($this->_store, $request, 
                    $this->_controllerNamespaces);
            if ( ! strcasecmp($request->getType(),
                    UssdRequest::REQUEST_TYPE_INITIATION)) {
                if ( ! $this->_initiationController) {
                    throw new FrameworkException(
                            '"initiationController" property cannot '
                            . 'be null.');
                }
                if ( ! $this->_initiationAction) {
                    throw new FrameworkException(
                            '"initiationAction" property cannot '
                            . 'be null.');  
                }
                $route = $this->_initiationController . '.' . 
                        $this->_initiationAction;
                $response = $this->processInitiationRequest($context, $route);
            }
            else {
                $response = $this->processContinuationRequest($context);
            }
            if ($response->isRelease()) {
                $context->sessionClose();
            }
            $clientState = $context->close();
            $response->setClientState($clientState);
            $this->logResponse($response);
        }
        catch (\Exception $t) {
            $response = UssdResponse::render($this->_errorMessage ?
                    $this->_errorMessage : $t->getMessage());
            $response->setException($t);
            $this->logResponse($response);
            if ($context) {
                $context->sessionClose();
                $context->close();
            }
        }
        if ($this->_requestListener) {
            $this->_requestListener->responseLeaving($request, $response);
        }
        return $response;
    }

    /**
     * Processes initiation requests and implements auto dial
     * mechanism.
     * 
     * @param UssdContext $context
     * @param string $route
     * @return UssdResponse
     */
    private function processInitiationRequest($context, $route) {
        $context->sessionSetNextRoute($route);
        $ussdResponse = $this->processContinuationRequest($context);
        
        if ($this->_maxAutoDialDepth > 0 && $ussdResponse->isAutoDialOn() &&
                !$ussdResponse->isRelease()) {            
            $ussdRequest = $context->getRequest();
            $initiationMessage = $ussdRequest->getMessage();
            $serviceCode = $ussdRequest->getServiceCode();
            
            // To make searching for dial string and split more
            // straightforward, replace # with *.
            $initiationMessage = str_replace('#', '*', $initiationMessage);
            $serviceCode = str_replace('#', '*', $serviceCode);
            
            $extraIndex = strpos($initiationMessage, $serviceCode);
            if ($extraIndex === false) {
                throw new FrameworkException(sprintf(
                        'Service code %s not found in initiation '
                                . 'message %s', $ussdRequest->getServiceCode(),
                                $ussdRequest->getMessage()));
            }
            
            $extra = substr($initiationMessage,
                    $extraIndex + strlen($serviceCode));
            $codes = explode('*', $extra);
            
            // codes may have empty strings if ** was in initiation message.
            // So remove them first.
            $newCodes = array();
            foreach ($codes as $code) {
                if ($code) {
                    array_push($newCodes, $code);
                }
            }
            $codes = $newCodes;
            
            $i = 0;
            while ($i < $this->_maxAutoDialDepth && $i < count($codes)) {
                $nextMessage = $codes[$i];
                $ussdRequest->setType(UssdRequest::REQUEST_TYPE_RESPONSE);
                $ussdRequest->setClientState($ussdResponse->getClientState());
                $ussdRequest->setMessage($nextMessage);
                $ussdRequest->setAutoDialOriginated(true);
                $ussdRequest->setAutoDialIndex($i);
                $ussdResponse = $this->processContinuationRequest($context);
                if ($ussdResponse->isRelease() || !$ussdResponse->isAutoDialOn()) {
                    break;
                }
                $i++;
            }
        }
        return $ussdResponse;
    }

    /**
     * @param UssdContext $context
     * @return UssdResponse
     */
    private function processContinuationRequest($context) {  
        $this->logRequest($context->getRequest());
        $response = null;
        $redirectCount = 0;
        while ($redirectCount < self::MAX_REDIRECT_COUNT) {
            $exists = $context->sessionExists();
            if ( ! $exists) {
                throw new SessionNotFoundException('Session does not exist.');
            }
            $response = $context->sessionExecuteAction();
            
            if ($response->isRelease()) {
                $context->sessionClose();
                break;
            }
            
            $context->sessionSetNextRoute($response->getNextRoute());
            if ( ! $response->isRedirect()) {
                break;
            }
            
            // Only log redirect responses at this stage.
            // Final responses will be logged later, when
            // client state is available.
            $this->logResponse($response);
            
            $response = null;
            $redirectCount++;
        }
        if ($response === null) {
            throw new FrameworkException(sprintf(
                    'Failed to get final ussd response after %d redirect%s.',
                    $redirectCount, $redirectCount == 1 ? '' : 's'));
        }
        return $response;
    }
    
    private function logRequest($request) {
        $debugFxn = Loggers::getDebugLogger();
        if ($debugFxn) {
            call_user_func($debugFxn, 'New ussd request:', 
                $request);
        }
    }
    
    private function logResponse($response) {
        // Log exception if any occurred.
        if ($response->getException()) {
            $errorFxn = Loggers::getErrorLogger();
            if ($errorFxn) {
                call_user_func($errorFxn,
                    'An error occured during ussd request processing.',
                        $response->getException());
            }
        }
        $debugFxn = Loggers::getDebugLogger();
        if ($debugFxn) {
            call_user_func($debugFxn, 'Sending ussd response:',
                $response);
        }
    }
}
