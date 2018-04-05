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
CREATE TABLE UssdSessions (
  UssdSessionId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  SessionId VARCHAR(36) NOT NULL,
  Sequence INT NOT NULL,
  ClientState TEXT NOT NULL,
  DateCreated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Then we define the applications settings, in **/Config/settings.php**

```php
// api rate limiter settings
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
            'dsn' => "mysql:host=127.0.0.1;dbname=bosch_db;charset=utf8",
            'username' => 'root',
            'password' => 'mich120',
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

composer.phar start
``` 

**4 Finish**

If you did all things well you should have your demo application running ;)

## Demo

The demo application can be found in the **/UssdApp/** folder.

You can run or edit it there. Good luck

**PS**: Looking forward to your feed back, suggestions for improvement, pull requests and critics.

## Credits

I will like to credit Aaron Baffour-Awuah for starting this project.