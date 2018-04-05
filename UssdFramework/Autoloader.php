<?php

/*
 * This file is part of the Predis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
