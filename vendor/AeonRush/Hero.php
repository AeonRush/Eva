<?php

namespace AeonRush {
    /**
     * Class Hero
     *
     * @package AeonRush
     */
    final class Hero {
        /**
         * Простой, ОЧЕНЬ простой, способ сокрытия email'а
         * @param $e
         * @param null $view
         * @param string $separator
         * @return array|mixed|string
         */
        public static function email($e, &$view = NULL, $separator = '!'){
            if($view == NULL) return $e;
            if($separator != NULL) {
                $separator = substr($separator, 0, 1);
                $separator = in_array($separator, array('.', '@')) ? '!' : $separator;
                $e = str_replace('@', '[at]', str_replace('.', '[dot]', $e));
                $separator = addslashes($separator);
            };
            $e = self::hashEmail($e);

            /// Если separator NULL значит возвращаем массив в JSON
            if($separator == NULL) {
                return json_encode($e);
            };
            /// Если $view это \Eva\View значит добавляем скрипт
            if($view instanceof \Eva\View) {
                $view->add('javascript:inline', self::getJavaScriptForEmail($separator));
            };
            /// Возвращаем строку
            return join($separator, $e);
        }

        /**
         * Обработка адреса электронной почты
         *
         * @param $e
         *
         * @return array
         */
        private function hashEmail($e) {
            $x = ceil(strlen($e)/rand(2, strlen($e) / 3));
            $y = floor(strlen($e) / $x);
            $hash = array(0);
            $email = array();
            for($i=1;$i<$x;++$i) {
                $z = rand($hash[$i-1] + 2, $hash[$i-1] + $y + 2);
                if($z >= strlen($e)-1) break;
                $hash[$i] = $z;
            };
            $hash = array_unique($hash);
            for($i = 0; $i < sizeof($hash)-1; ++$i) {
                $email[] = substr($e, $hash[$i], $hash[$i+1]-$hash[$i]);
            };
            $email[] = substr($e, $hash[sizeof($hash)-1]);
            return $email;
        }

        /**
         * Получение скрипта для обработка
         *
         * @param $separator
         *
         * @return string
         */
        private static function getJavaScriptForEmail($separator) {
            return '
                    (function(){
                        var a = ["e","ta-","a","d"].reverse().join(""),
                            b = ["[","a","t]"].join(""),
                            c = ["o","t","il","a","m"].reverse().join(""),
                            d = ["'.$separator.'","a","t'.$separator.'"].join(""),
                            i = ["[", "d", "o", "t", "]"].join("");
                        Array.prototype.forEach.call(document.querySelectorAll("[" + a + "]"), function(e) {
                            e.innerHTML = e.getAttribute(a)
                                                .split(d).join(["il", "a", "m"].reverse().join(""))
                                                .split("'.$separator.'").join("")
                                                .split(i).join(".")
                                                .replace(b, String.fromCharCode(Math.pow(2, Math.pow(36, .5))));
                            if(e["href"] != undefined)
                                e.href = c + String.fromCharCode(Math.pow(2, Math.pow(36, .5)) - Math.pow(36, .5)) + e.innerText;
                        });
                    })();
                ';
        }
    };
}

/// Copyright © 2014-2015 Чернов, Александр. Contacts: <aeonrush@live.ru>, <black_web@outlook.com>
/// License: http://www.apache.org/licenses/LICENSE-2.0