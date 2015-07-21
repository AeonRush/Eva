<?php
    
namespace AeonRush {
    /**
     * Class Is
     * Simple testing class
     *
     * @package AeonRush
     */
    final class is {
        /// Email pattern
        public static $email = '/^[a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[@][a-zA-Z0-9_-]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/';
        /// Username pattern
        public static $username = '/^[^_\-0-9][A-z0-9_]{4,32}$/';
        /// Password pattern
        public static $password = '/^[a-zA-Z0-9-_+-=*()]{6,32}$/';
        /// Array of blacklisted usernames
        public static $blacklisted = array(
            'dev', 'development', 'support', 'feedback', 'ads', 'ion', 'aeonrush', 'root', 'admin', 'administrator', 'seo', 'webmaster', 'mail', 'smtp', 'imap',
        );

        /**
         * Static email test
         *
         * @param $e
         * @return int
         */
        public static function email($e) {
            return preg_match(self::$email, $e);
        }

        /**
         * Static username test
         *
         * @param $l
         * @return int
         */
        public static function username($l) {
            return preg_match(self::$username, $l) && !in_array($l, self::$blacklisted);
        }

        /**
         * Static password test
         *
         * @param $p
         * @return int
         */
        public static function password($p) {
            return preg_match(self::$password, $p);
        }
    };
}

/// Copyright © 2014-2015 Чернов, Александр. Contacts: <aeonrush@live.ru>, <black_web@outlook.com>
/// License: http://www.apache.org/licenses/LICENSE-2.0