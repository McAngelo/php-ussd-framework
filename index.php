<?php

/* 
 *  (c) 2018. MJ-Consult
 */

// This is the unix autoloader
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    
    spl_autoload_register(
        function($class) {
            $filePath = __DIR__ . '/' . $class . '.php';
            if (file_exists($filePath)) {
                require $filePath;
                return true;
            }
            return false;
        },
        true,
        false
    );
} else {// This is the linux autoloader

    spl_autoload_register(
        function($class) {
            $filePath = __DIR__ . '/'. str_replace('\\', '/', $class) . '.php';
            if (file_exists($filePath)) {
                require $filePath;
                return true;
            }
            return false;
        },
        true,
        false
    );
}

// Instantiate the app settings
$settings = require __DIR__ . '/Config/settings.php';


//die(var_dump($settings));


function customDebugLogger() {
    $settings = require __DIR__ . '/Config/settings.php';
    $log = new \Logs\BaseLog($settings['logger']['path'], $settings['logger']['name']);
    $log->debug(func_get_args());
}

function customErrorLogger() {
    $settings = require __DIR__ . '/Config/settings.php';
    $log = new \Logs\BaseLog($settings['logger']['path'], $settings['logger']['name']);
    $log->error(func_get_args());    
}

\UssdFramework\Loggers::setDebugLogger('customDebugLogger');
\UssdFramework\Loggers::setErrorLogger('customErrorLogger');

$ussd = new \UssdFramework\Ussd;

if($settings['storageType'] == 'database'){
    $ussd->store(new UssdFramework\Stores\DatabaseSessionStore($settings['database']['dev']['dsn'], $settings['database']['dev']['username'], $settings['database']['dev']['password']));    
}

if($settings['storageType'] == 'redis'){
    $ussd->store(new UssdFramework\Stores\RedisStore($redis, $config));    
}


$ussd->controllerNamespaces(array($settings['appilcationPath']))
     ->initiationController($settings['initiationController'])
     ->initiationAction($settings['initiationAction'])
     ->maxAutoDialDepth(10000)
     ->accessControlAllowOrigin($settings['accessControlAllowOrigin']);
$ussd->service();

