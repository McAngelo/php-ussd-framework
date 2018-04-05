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
class UserController extends \UssdFramework\UssdController {

    private $_baseUrl;
    private $_requestBody;
    private $_dataBag;
    private $_header;

    public function __construct()
    {
        # change this to the approprate url
        $this->_baseUrl = "https://millennium-marathon.herokuapp.com/api/ussd/";
        #$this->_baseUrl = "http://localhost:8080/api/ussd/";
        $this->_requestBody = $this->getRequest();

        $this->_header = "Millennium Marathon";
    }

    #############   New User Experience    #############

    # display the menu
    public function e_menu(){

        # Get the user's cached data
        
        $dataBag = $this->getDataBag();   
        $data = $dataBag->get('userData');

        $userData = json_decode(json_encode($data), true);

        $userName = $userData['last_name'] . ' ' . $userData['first_name'];

        if($userData['payment_status'] == "pending"){
            return $this->redirect("make_payment");
        }

        //$menuHeader = "$this->_header\nHi $userName, nice to meet you again.";
        $menuHeader = "Welcome to $this->_header \n Hi $userName";

        $message = "$this->_header \n $enuHeader\nYour registration is being processed. Kindly check back here on March 30th 2018 for further details\n Thank you.";
        return $this->render($message);

        /*$menu = new \UssdFramework\UssdMenu();
        $menu->header($menuHeader)
                ->createAndAddItem('Buy Power tool', 'products', 'Buy')
                ->createAndAddItem('Make Payment', 'underconstruction')
                ->createAndAddItem('View Payments', 'transaction_summary')
                ->createAndAddItem('Exit', 'close', 'Main');
        return $this->renderMenu($menu);*/
    }

    # Close user's USSD session
    public function make_payment()
    {
        # closing message

        $dataBag = $this->getDataBag();   
        $data = $dataBag->get('userData');

        $userData = json_decode(json_encode($data), true);

        $userName = $userData['last_name'] . ' ' . $userData['first_name'];

        $message = "Hello $userName, kindly complete your registration process\n";
        $message .= "Registration fee: GHs 53.00\nConfirm details:";

        $menuHeader = "$this->_header\n\n$message";

        $menu = new \UssdFramework\UssdMenu();
        $menu->header($menuHeader)
                ->createAndAddItem('Yes', 'process_payment')
                ->addItem(new \UssdFramework\UssdMenuItem('0', 'No', 'close', 'Main'));
        return $this->renderMenu($menu);
    }

    # Close user's USSD session
    public function process_payment()
    {
        # closing message
         $message = "$this->_header \n\nKindly complete the prompt from your provider";
        return $this->render($message);
    }

    public function transaction_summary(){
        
        $dataBag = $this->getDataBag();   
        $data = $dataBag->get('userData');

        $dataObject = json_decode(json_encode($data), true);
        //return $this->render();
        $userName = $dataObject['user_first_name'];

        $menuHeader = "$this->_header\nThese are your payment history";

        $menu = new \UssdFramework\UssdMenu();
        $menu->header($menuHeader)
                ->createAndAddItem('14/03/2018 - GHs 200', 'e_menu')
                ->createAndAddItem('13/03/2018 - GHs 130', 'e_menu')
                ->createAndAddItem('12/03/2018 - GHs 320', 'e_menu')
                ->addItem(new \UssdFramework\UssdMenuItem('0', 'Back', 'e_menu'));

        return $this->renderMenu($menu);
    }
}
