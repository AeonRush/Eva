<?php

namespace AeonRush {
    /**
     * Class ReCaptcha
     * @package AeonRush
     */
    final class ReCaptcha {
        /**
         * Проверка капчи
         * @return mixed
         */
        public static function check(){
            $recaptcha = new \ReCaptcha\ReCaptcha(\app::getParam('captcha:secret'));
            $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
            return $resp->isSuccess();
        }
    };
}

/// Copyright © 2014-2015 Чернов, Александр. Contacts: <aeonrush@live.ru>, <black_web@outlook.com>
/// License: http://www.apache.org/licenses/LICENSE-2.0