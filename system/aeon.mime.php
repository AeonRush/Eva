<?php

namespace Eva {
    /**
     * Class Mime
     *
     * @package Eva
     */
    final class Mime {
        private static $mimes = NULL;

        /**
         * Возвращает MIME в виде строки
         *
         * @param $ext
         *
         * @return mixed
         */
        public static function get($ext) {
            if(self::$mimes == NULL) self::$mimes = include(__SYSTEM__ . '/mimes.php');
            return self::$mimes[$ext];
        }

        /**
         * Устанавливает заголовок Content-type
         *
         * @param $ext
         */
        public static function setContentType($ext) {
            header('Content-Type: '.self::get($ext));
        }
    };
}

/// Copyright © 2014-2015 Чернов, Александр. Contacts: <aeonrush@live.ru>, <black_web@outlook.com>
/// License: http://www.apache.org/licenses/LICENSE-2.0