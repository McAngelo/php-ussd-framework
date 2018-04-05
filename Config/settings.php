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
            'dsn' => 'mysql:host=eu-cdbr-west-02.cleardb.net;dbname=heroku_58617ca4d053a22;charset=utf8',
            'username' => 'b519ac75ff9d6b',
            'password' => '18415738',
        ]
    ],
    'accessControlAllowOrigin' => [
        'path' => "https://bosch-promo.herokuapp.com/ussd"
    ],        
    
    // logging settings
    'logger' => [
        'name' => 'millennium-marathon',
        'path' => __DIR__ . '/../Logs/general.log',
    ]
];

//*315#