<?php

/*
 *  (c) 2018. Michael Johnson
 */

namespace UssdApp;

use Api\ApiConnector;
/**
 * Description of NewUserController
 *
 * @author Michael kwame Johnson
 */
class SecondController extends \UssdFramework\UssdController {

    private $_baseUrl;
    private $_dataBag;
    private $_header;

    public function __construct()
    {
        # change this to the approprate url
        $this->_baseUrl = "http://localhost:8080/api/ussd/";

        $this->_header = "PHP UssdFramework";
    }

    #############   New User Experience    #############

    # display the menu
    public function start(){

        $message = "\nWelcome to another class where you can perform further object oriented development\n";

        $menuHeader = "$this->_header : $message";

        $menu = new \UssdFramework\UssdMenu();
        $menu->header($menuHeader)
                ->createAndAddItem('Thank you', 'close')
                ->addItem(new \UssdFramework\UssdMenuItem('0', 'Gerrout', 'start', 'Main'));
        return $this->renderMenu($menu);       
    }

     # Close user's USSD session
    public function close()
    {
        # closing message
        $message = "$this->_header \n\nThank you for using our USSD service at the second class";
        return $this->render($message);
    }
}
