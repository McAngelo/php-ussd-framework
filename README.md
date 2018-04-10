# USSD Framework in PHP [![Travis Build Status](https://travis-ci.org/McAngelo/php-ussd-framework.svg?branch=master)]

This is a simple Framework for building Ussd applications in PHP against the [Hubtel USSD API](https://developers.hubtel.com/documentations/ussd).

This project is a ported from the [original C# version](https://github.com/hubtel/ussd-framework)

## Purpose

There are many ways to integrate with the [Hubtel USSD API](https://developers.hubtel.com/documentations/ussd) across the many programming languages.

This project seeks to create a light weight 1 Mb framework that will make it easy for any one to bootstrap a ussd application in minutes.

**Discliamer**: this is not a Hubtel sponsered project. It is a hubby project to fill in the gap.


Take your time to understand how Hubtel USSD API works. https://developers.hubtel.com/documentations/ussd

## Main specs

- Designed with PHP's object oriented architecture
- Simple application configuration settings
- Session storage flexibility i.e. either Redis store or any RDMS
- Simple standards for development
- Use of [HTTPFul](http://phphttpclient.com/) for making API request
- Simple custom logging engine


## Install

To explain better.

**1 Clone/Download this project** 

Clone the repository unto your machine/server, then navigate into the project.

**2 Create Database with Table named 'UssdSessions'**
First , we create the table UssdSessions where all session requests and responses are registered

```sql
CREATE TABLE ussd_sessions (
  ussd_session_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(36) NOT NULL,
  sequence INT NOT NULL,
  client_state TEXT NOT NULL,
  date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Then we define the applications settings, in **/Config/settings.php**

```php
// ussd application settings
return [
    // USSD Settings
    'appilcationPath' => '\UssdApp', # the folder that contains all your ussd applications logic
    'initiationController' => 'Main', # the main controller/class of your ussd logic
    'initiationAction' => 'start', # the main action/function/method of your ussd logic 
    'storageType' => 'database', # uncomment if you are using database to log your sessions
    #'storageType' => 'redis', # uncomment if you are using redis to log your sessions
    'redis' => [
        'dev' => [
            'redis' => '',
            'config' => ''
        ],
        'prod' => [
            'redis' => '',
            'config' => ''
        ]
    ],
    'database' => [
        // dev database settings
        'dev' => [
            'dsn' => "mysql:host=127.0.0.1;dbname=local_db;charset=utf8",# change only the dbname to your actual dbname
            'username' => 'username',
            'password' => '******',
        ],

        // prod database settings
        'prod' => [
            'dsn' => 'mysql:host=www.example.com;dbname=production_db;charset=utf8',
            'username' => 'username',
            'password' => 'password',
        ]
    ],
    'accessControlAllowOrigin' => [
        'path' => "https://example.com/ussd"
    ],        
    
    // logging settings
    'logger' => [
        'name' => 'ussd-framework-logger', # your logger's identifier
        'path' => __DIR__ . '/../Logs/general.log', # path to your logger file
    ]
];
```

**3 Run the application**

You need [composer](https://getcomposer.org/) install on your machine to be able to run this project. Find out how to setup here https://getcomposer.org/doc/00-intro.md.

Execute either of the following commands

```bash
composer start

or

composer.phar start
``` 

**4 Finish**

If you did all things well you should have your demo application running ;)

## Demo

The demo application can be found in the **/UssdApp/** folder.

```php
*
 *  (c) 2018. MJ-Consult
 */

namespace UssdApp;

/**
 * Description of MainController
 *
 * @author Michael kwame Johnson
 */
class MainController extends \UssdFramework\UssdController {

    private $_header;
    private $_date;

    public function __construct()
    {
        $this->_header = "PHP UssdFramework";
        $this->_date = date('Y-m-d H:i:s', time());
    }
    
    # Main startup method/action for the USSD initiates the USSD session
    public function start()
    {
        $menu = new \UssdFramework\UssdMenu();
        $menu->header($this->_header)
                ->createAndAddItem('Form Inputs', 'form_input')
                ->createAndAddItem('Redirect', 'same_class_redirect')
                ->createAndAddItem('Another Class', 'start', 'Second')
                ->createAndAddItem('Render', 'render_message')
                ->createAndAddItem('List Items', 'list_items')
                ->createAndAddItem('Api Calls', 'api_call')
                ->createAndAddItem('Exit', 'close', 'Main');
        return $this->renderMenu($menu);
    }

    # Responds with a USSD form
    public function form_input()
    {
        $formHeader = "$this->_header\n";

        # initialize the registation form
        $form = new  \UssdFramework\UssdForm('process_form_input');

        # set the name input field
        $nameInput = new \UssdFramework\UssdInput('name', 'Enter your name');
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

    # Processes the USSD form
    public function process_form_input()
    {
        $formData = $this->getFormData();

        $name = $formData['name'];
        $gender = $formData['gender'];    

        $title = ($gender == 'male' ? 'Mr.' : "Ms."); 

        $message = "\nHello $title $name\n";

        $menuHeader = "$this->_header : $message";

        $menu = new \UssdFramework\UssdMenu();
        $menu->header($menuHeader)
                ->createAndAddItem('Thank you', 'close')
                ->addItem(new \UssdFramework\UssdMenuItem('0', 'Gerrout', 'start'));
        return $this->renderMenu($menu);
    }

    # Displays a welcome message and releases the USSD session
    public function you_welcome()
    {
        $message = "$this->_header \n\nYou are welcome and have a nice day";
        return $this->render($message);
    }

    # List menu items
    public function list_items()
    {
        $menu = new \UssdFramework\UssdMenu();
        $menu->header($menuHeader)
                ->createAndAddItem('Sunday', 'e_menu')
                ->createAndAddItem('Monday', 'e_menu')
                ->createAndAddItem('Tuesday', 'e_menu')
                ->createAndAddItem('Wednesday', 'e_menu')
                ->createAndAddItem('Thurday', 'e_menu')
                ->createAndAddItem('Friday', 'e_menu')
                ->createAndAddItem('Saturday', 'e_menu')
                ->addItem(new \UssdFramework\UssdMenuItem('0', 'Back', 'e_menu'));

        return $this->renderMenu($menu);
    }

    # redirects to the start menu
    public function e_menu(){

        return $this->redirect('start');
    }

     # Redirects to an action/function/method
    public function same_class_redirect()
    {
        return $this->redirect("redirect_message");
    }

    # Redirect and stop
    public function redirect_message()
    {
        $message = "$this->_header \n\nYou were redirected here";
        return $this->render($message);
    }

    # Displays a message and releases
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
```

You can run or edit it there. Good luck

**PS**: Looking forward to your feed back, suggestions for improvement, pull requests and critics.

## Credits

I will like to credit **[Aaron Baffour-Awuah](https://github.com/aaronicsubstances)** for starting this project.

