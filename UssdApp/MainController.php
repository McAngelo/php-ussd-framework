<?php

/*
 *  (c) 2018. MJ-Consult
 */

namespace UssdApp;

use Api\ApiConnector;

/**
 * Description of MainController
 *
 * @author Michael kwame Johnson
 */
class MainController extends \UssdFramework\UssdController {

    private $_baseUrl;
    private $_header;

    public function __construct()
    {
        $this->_baseUrl = "http://localhost:8080/api/ussd/";

        $this->_header = "PHP UssdFramework";
    }
    
    # Main startup method/action for the USSD
    public function start()
    {
        $menu = new \UssdFramework\UssdMenu();
        $menu->header($this->_header)
                ->createAndAddItem('Form Inputs', 'form_input')
                ->createAndAddItem('Redirect', 'same_class_redirect')
                ->createAndAddItem('Render', 'render_message')
                ->createAndAddItem('List Items', 'list_items')
                ->createAndAddItem('Api Calls', 'api_call')
                ->createAndAddItem('Exit', 'close', 'Main');
        return $this->renderMenu($menu);
    }

    # Check whether the user's phone number exists
    public function form_input()
    {
        $formHeader = "$this->_header\n";

        # initialize the registation form
        $form = new  \UssdFramework\UssdForm('process_form_input');

        # set the name input field
        $nameInput = new \UssdFramework\UssdInput('name', 'Enter number');
        $nameInput->header($formHeader);
        $form->addInput($nameInput);

        # select your gender
        $genderInput = new \UssdFramework\UssdInput('gender', "Select gender");
        $genderInput->header($formHeader)
                    ->addOption(new \UssdFramework\Option('Male', 'male'))
                    ->addOption(new \UssdFramework\Option('Female', 'female'));
        $form->addInput($genderInput);

        return $this->renderForm($form);
    }

    public function process_form_input()
    {

    }

    public function list_items()
    {
        $menu = new \UssdFramework\UssdMenu();
        $menu->header($menuHeader)
                ->createAndAddItem('14/03/2018 - GHs 200', 'e_menu')
                ->createAndAddItem('13/03/2018 - GHs 130', 'e_menu')
                ->createAndAddItem('12/03/2018 - GHs 320', 'e_menu')
                ->addItem(new \UssdFramework\UssdMenuItem('0', 'Back', 'e_menu'));

        return $this->renderMenu($menu);
    }

    # display the menu
    public function e_menu(){

        return $this->redirect('start');
    }

     # Check whether the user's phone number exists
    public function same_class_redirect()
    {
        return $this->redirect("redirect_message");
    }

    public function redirect_message()
    {
        $message = "$this->_header \n\nYou were redirected here";
        return $this->render($message);
    }

    # Close user's USSD session
    public function render_message()
    {
        # closing message
        $message = "$this->_header \n\nYou have reached a USSD render message";
        return $this->render($message);
    }

    # Close user's USSD session
    public function close()
    {
        # closing message
        $message = "$this->_header \n\nThank you for using our USSD service";
        return $this->render($message);
    }
}
