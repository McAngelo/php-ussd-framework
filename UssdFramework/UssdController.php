<?php

/*
 *  (c) 2016. SMSGH
 */
namespace UssdFramework;

/**
 * Base class for controller classes which handle USSD requests. All controllers
 * must subclass this class.
 * <p>
 * Provides convenience methods for displaying menus and forms in
 * ussd apps.
 * 
 * @author Aaron Baffour-Awuah
 */
class UssdController {
    
    /**
     * @var string
     */
    const MENU_PROCESSOR_DATA_KEY = 'UssdFramework\UssdController\MenuProcessorData';
    
    /**
     * @var string
     */
    const FORM_PROCESSOR_DATA_KEY = 'UssdFramework\UssdController\FormProcessorData';
    
    /**
     * @var string
     */
    const FORM_DATA_KEY = 'UssdFramework\UssdController\FormData';

    /**
     * @var UssdRequest
     */
    private $_request;
    
    /**
     * @var UssdDataBag
     */
    private $_dataBag;
    
    /**
     * @var array associative array of form data
     */
    private $_formData;

    /**
     * Does nothing aside instance creation.
     */
    function __construct() {
    }
    
    /**
     * Called as the final step in initialising a controller. Subclasses 
     * must call this version or else important initialisation  
     * will be skipped.
     */
    function init() {
        // Retrieve any form data existing from previous ussd screens,
        // for use by current route
        $repr = $this->_dataBag->get(self::FORM_DATA_KEY);
        $this->_formData = json_decode($repr, true);
    }

    /**
     * Gets the request to be handled.
     * 
     * @return UssdRequest request to be handled.
     */
    function getRequest() {
        return $this->_request;
    }

    /**
     * Sets the request to be handled. Called by the framework
     * during controller initialisation.
     * 
     * @param UssdRequest request the request to be handled.
     */
    function setRequest($request) {
        $this->_request = $request;
    }

    /**
     * Gets a {@link UssdDataBag} instance which can be used by
     * controller to persist data across requests.
     * 
     * @return UssdDataBag {@link UssdDataBag} for persisting requests across requests.
     */
    function getDataBag() {
        return $this->_dataBag;
    }

    /**
     * Sets a {@link UssdDataBag} instance for controllers to use to
     * persist data across requests. Called by the framework during 
     * controller initialisation.
     * 
     * @param UssdDataBag dataBag {@link UssdDataBag} instance for persisting requests
     * across requests.
     */
    function setDataBag($dataBag) {
        $this->_dataBag = $dataBag;
    }

    /**
     * Gets the data collected from ussd app user in previous form
     * screens
     * <p>
     * When a {@link UssdForm} is rendered, it results in the user being
     * asked for a number of inputs, the number being equal to the 
     * number of {@link UssdInput} instances in the {@link UssdForm}.
     * All inputs are saved into a map and made available after the form
     * display is complete.
     * 
     * @return array form data from previous form input screens as an associative
     * array.
     */
    function &getFormData() {
        return $this->_formData;
    }
    
    private function route($action, $controller = null) {
        if ( ! $action) {
            throw new \InvalidArgumentException('"action" argument '
                    . 'cannot be null');
        }
        if ( ! $controller) {
            $controller = get_class($this);
        }
        return $controller . '.' . $action;
    }
    
    /**
     * Asks framework to continue processing by calling an action on a
     * different controller.
     * 
     * @param action action to call to continue processing.
     * @param controller controller in which action resides.
     * @return UssdResponse the response which informs framework to call the
     * "action" argument in the "controller" class and continue
     * the ussd request processing (<b>not</b> the result of calling
     * "action").
     */
    function redirect($action, $controller = null) {
        return UssdResponse::redirect($this->route($action, $controller));
    }
    
    /**
     * Constructs a ussd response which continues session by invoking
     * an action on the given controller. Action is actually
     * invoked when the next ussd request comes in from the telcos
     * (telecommunications networks).
     * 
     * @param message the ussd response message
     * @param action the action to call in response to the next ussd
     * request. If null, then session will be terminated.
     * @param controller the controller whose action will be called. If
     * null, then the subclass calling this method will be used.
     * @param autoDialOn true (by default) to continue any ongoing auto 
     * dial processing; false to end it.
     * @return UssdResponse ussd response to continue session (unless action is null).
     */
    function render($message, $action = null, $controller = null, 
            $autoDialOn = true) {
        if ( ! $message) {
            $message = '';
        }
        $route = null;
        if ($action) {
            $route = $this->route($action, $controller);
        }
        $ussdResponse = UssdResponse::render($message, $route);
        $ussdResponse->setAutoDialOn($autoDialOn);
        return $ussdResponse;
    }
    
    /**
     * Constructs a ussd response out of a menu.
     * @param ussdMenu the menu.
     * @param autoDialOn true (by default) to continue any ongoing auto 
     * dial processing; false to end it.
     * @return UssdResponse ussd response from menu.
     */
    function renderMenu($ussdMenu, $autoDialOn = true) {
        if ( ! $ussdMenu) {
            throw new \InvalidArgumentException('"ussdMenu" argument cannot '
                    . 'be null');
        }
        $repr = UssdUtils::marshallUssdMenu($ussdMenu);
        $this->_dataBag->set(self::MENU_PROCESSOR_DATA_KEY, $repr);
        $message = $ussdMenu->render();
        return $this->render($message, 'menuProcessor', null, $autoDialOn);
    }
    
    /**
     * Constructs a ussd response out of a form.
     * @param form the form
     * @param autoDialOn true (by default) to continue any ongoing auto 
     * dial processing; false to end it.
     * @return UssdResponse ussd response from form.
     */
    function renderForm($form, $autoDialOn = true) {
        if ( ! $form) {
            throw new \InvalidArgumentException('"form" argument cannot '
                    . 'be null');
        }
        $repr = UssdUtils::marshallUssdForm($form);
        $this->_dataBag->set(self::FORM_PROCESSOR_DATA_KEY, $repr);
        $message = $form->render();
        return $this->render($message, 'formProcessor', null, $autoDialOn);
    }
    
    /**
     * Internal to framework, for processing menus. Handles invalid menu choices.
     * 
     * @return UssdResponse appropriate response depending on selected menu
     * choice. Redisplays menu if selected menu choice is invalid.
     */
    function menuProcessor() {
        $menu = $this->getMenu();
        $chosenItem = null;
        $choice = $this->_request->getTrimmedMessage();
        foreach ($menu->getItems() as $item) {
            if ( ! $item) {
                throw new FrameworkException('Encountered null '
                        . 'ussd menu item.');
            }
            if ( ! strcasecmp($item->getIndex(), $choice)) {
                $chosenItem = $item;
                break;
            }
        }
        if ( ! $chosenItem) {
            return $this->handleInvalidMenuChoice($menu, $choice);
        }
        $this->_dataBag->delete(self::MENU_PROCESSOR_DATA_KEY);
        return $this->redirect($chosenItem->getAction(), 
                $chosenItem->getController());
    }
    
    /**
     * Hook for subclasses to override how invalid menu choices are
     * handled. By default invalid menu choices cause a redisplay of
     * the menu, and any auto dial session is ended.
     * 
     * @param UssdMenu menu the menu in which the invalid input was received.
     * @param string invalidMenuChoice the invalid choice the app user texted.
     * 
     * @return UssdResponse the response to send to app user in response to his/her
     * invalid input.
     */
    protected function handleInvalidMenuChoice($menu, $invalidMenuChoice) {
        // Redisplay menu, but turn off auto dial mechanism.
        return $this->renderMenu($menu, false);
    }
    
    /**
     * Internal to framework, for processing forms. Handles invalid form options.
     * 
     * @return UssdResponse appropriate response depending on stage of form processing.
     * If form processing is done, returns response that calls the action
     * to process the form data. Redisplays any stage in which an invalid
     * input option is received.
     */
    function formProcessor() {
        $form = $this->getForm();
        $inputs = $form->getInputs();
        $input = $inputs[$form->getProcessingPosition()];
        if ( ! $input) {
            throw new FrameworkException('Encountered null ussd form input.');
        }
        $key = $input->getName();
        $value = null;
        if ( ! $input->hasOptions())
        {
            $value = $this->_request->getTrimmedMessage();
        }
        else
        {
            $option = null;
            try {
                $choice = $this->_request->getTrimmedMessage();
                $options = $input->getOptions();
                $option = $options[$choice - 1];
            }
            catch (\Exception $ex) {
                return $this->handleInvalidFormInputOption($form,
                        $this->_request->getTrimmedMessage());
            }
            if ( ! $option) {
                throw new FrameworkException('Encountered null ussd input '
                        . 'option');
            }
            $value = $option->getValue();
        }
        $formData = $form->getData();
        $formData[$key] = $value;
        $form->data($formData);
        if ($form->getProcessingPosition() == (count($inputs) - 1))
        {
            $this->_dataBag->delete(self::FORM_PROCESSOR_DATA_KEY);
            $formDataRepr = json_encode($form->getData());
            $this->_dataBag->set(self::FORM_DATA_KEY, $formDataRepr);
            return $this->redirect($form->getAction(),
                    $form->getController());
        }
        $form->processingPosition($form->getProcessingPosition()+1);
        $formRepr = UssdUtils::marshallUssdForm($form);
        $this->_dataBag->set(self::FORM_PROCESSOR_DATA_KEY, $formRepr);
        $message = $form->render();
        return $this->render($message, 'formProcessor');
    }
    
    /**
     * Hook for subclasses to override how invalid form options are
     * handled. By default, invalid form options cause the form to
     * be redisplayed, and any auto dial session running is ended.
     * 
     * @param UssdForm form the form in which an invalid option was received.
     * @param string invalidOption the invalid option texted by app user.
     * @return UssdResponse the response to send to app user in response to 
     *                      his/her invalid input.
     */
    protected function handleInvalidFormInputOption($form, $invalidOption) {
        // Redisplay form at current input, but turn off auto dial mechanism.
        return $this->renderForm($form, false);
    }
    
    /**
     * @return UssdMenu
     */
    private function getMenu() {
        $repr = $this->_dataBag->get(
                self::MENU_PROCESSOR_DATA_KEY);
        $menu = UssdUtils::unmarshallUssdMenu($repr);
        if ( ! $menu) {
            throw new FrameworkException('UssdMenu object could not be found.');
        }
        return $menu;
    }
    
    /**
     * @return UssdForm
     */
    private function getForm() {
        $repr = $this->_dataBag->get(
                self::FORM_PROCESSOR_DATA_KEY);
        $form = UssdUtils::unmarshallUssdForm($repr);            
        if ( ! $form) {
            throw new FrameworkException('UssdForm object could not be found.');
        }
        return $form;
    }
}
