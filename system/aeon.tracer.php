<?php

set_error_handler('error_handler');
function error_handler($code, $message, $file, $line) {
    switch ($code) {
        case E_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
            throw new Exception($message, 0, $code, $file, $line);
        break;
    };
    return true;
};

set_exception_handler('exception_handler');
function exception_handler($e) {
    # TODO Написать отправку сообщения
    ob_end_clean();
    print_r($e);
    msg500();
};

/// Copyright © 2014-2015 Чернов, Александр. Contacts: <aeonrush@live.ru>, <black_web@outlook.com>
/// License: http://www.apache.org/licenses/LICENSE-2.0