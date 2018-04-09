<?php

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
    
    'accessControlAllowOrigin' =>  "https://example.com/ussd",        
    
    // logging settings
    'logger' => [
        'name' => 'ussd-framework-logger',
        'path' => __DIR__ . '/../Logs/general.log',
    ]
];