<?php

/// And the boot begun
error_reporting(E_ALL);
session_start();
mb_internal_encoding('UTF-8');

define('__EVA__', 'AEON Web Engine');
define('__VERSION__', 'v2');
define('__ROOT__', dirname(__DIR__));
define('__NGN__', __ROOT__.'/system');
define('__SYSTEM__', __NGN__);
define('__HELPERS__', __ROOT__.'/helpers');
define('__APP__', __ROOT__.'/application');
define('__HOST__', ('//'.$_SERVER['HTTP_HOST']));
define('__APC__', !!function_exists('apc_exists'));
define('__PROTOCOL__', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http');

ob_start();
    include(__SYSTEM__.'/aeon.php');
    include(__SYSTEM__.'/aeon.headers.php');
    include(__SYSTEM__.'/aeon.tracer.php');
    include(__SYSTEM__.'/aeon.router.php');
    include(__SYSTEM__.'/aeon.loader.php');
    include(__SYSTEM__.'/aeon.engine.php');
ob_end_clean();
\app::getInstance();

/// Copyright © 2014-2015 Чернов, Александр. Contacts: <aeonrush@live.ru>, <black_web@outlook.com>
/// License: http://www.apache.org/licenses/LICENSE-2.0