<?php

namespace Eva {
    /**
     * Class Local
     * Класс по работе с языками
     *
     * @package Helper
     */
    final class Local {
        private static $self;
        private $appLanguages = NULL;

        public static function getInstance(&$app = NULL) {
            if (self::$self == NULL) {
                self::$self = new self();
                if($app != NULL) $app->addEvent(\EvenType::PostProcess, array(self::$self, 'postprocess'));
            };
            return self::$self;
        }

        /**
         * Конструктор класса
         * Загружаем JSON с данными
         */
        public function __construct() {

            /// Если в начале URL "!" делаем redirect на эту же URL, но без "!" и c urldecode
            if(substr($_SERVER['REQUEST_URI'], 1, 1) == '!') {
                header('Location: '.__HOST__.'/'.urldecode(substr($_SERVER['REQUEST_URI'], 2)), 301);
                exit;
            };

            $resources = array(
                'countries' => __SYSTEM__.'/local/countries.json',
                'languages' => __SYSTEM__.'/local/languages.json',
                'cultures' => __SYSTEM__.'/local/cultures.json',
            );
            if (__APC__)
                foreach ($resources as $name => $path) $this->loadResource($name, $path);
            else
                foreach ($resources as $name => $path) $this->loadStaticResources($name, $path);

            # $this->localeCheck();
        }

        /**
         * Постобработка HTML страницы. Заменяет ~/ на текущий язык
         *
         * @param $html
         */
        public function postprocess(&$html) {
            $evaLanguages = empty($this->appLanguages) ? \app::getParam('eva:languages') : $this->appLanguages;
            $html = str_replace('~/', '/'.(sizeof($evaLanguages) == 1 ? '' : strtolower($this->getCurrentLanguage()).'/').\app::getParam('eva:urlSection'), $html);
        }

        /**
         * Загрузка ресурсов если есть APC
         *
         * @param $name
         * @param $path
         */
        private function loadResource($name, $path) {
            if(!apc_exists($name)) {
                /// Получаем из файла
                $json = file_get_contents($path);
                /// Записываем в APC
                apc_add($name, $json, 2);
                /// Заносим в переменную
                $this->$name = $this->parseJSON($json);
                unset($json);
                return;
            };
            /// Получем из APC и заносим в переменную
            $this->$name = $this->parseJSON(apc_fetch($name));
        }

        /**
         * Загрузка из файлов если APC нет
         *
         * @param $name
         * @param $path
         */
        private function loadStaticResources($name, $path) {
            $this->$name = $this->parseJSON(file_get_contents($path));
        }

        /**
         * Парсер файловых данных с предобработкой на случай использования BOM в UTF8
         *
         * @param $json
         *
         * @return mixed
         */
        private function parseJSON($json) {
            return json_decode(preg_replace('/(\xEF\xBB\xBF)+/', '', $json), true);
        }

        /**
         * Пишем правильно сколько дней :)
         * ru-RU :
         *      1 день, 2 дня, 11 дней
         * Other :
         *      0 days, 1 day, 11 days
         *
         * @param $day
         *
         * @return string
         */
        public function days($day) {
            if($this->getCurrentLanguage() == 'ru-RU') {
                if($day % 10 == 1) $t = 'день';
                else $t = 'дня';
                if(in_array($day % 10, array(0, 5, 6, 7 ,8 ,9)) || ($day % 100 > 10 && $day % 100 < 20)) $t = 'дней';
                return $t;
            }
            return $day == 1 ? 'day' : 'days';
        }

        /**
         * Проверка текущего языка
         * Если Язык не определен или язык не поддерживается делаем redirect к ближайшему подходящему
         * @param null $evaLanguages
         */
        public function localeCheck($evaLanguages = NULL) {
            $language = NULL;
            $langAsParam = !!$evaLanguages;
            if($langAsParam) {
                $this->appLanguages = $evaLanguages;
            }
            $evaLanguages = $langAsParam == false ? \app::getParam('eva:languages') : $evaLanguages;

            preg_match('/^\/([a-z]{2}\-[a-z]{2})[\/]{1}(.*)/', $_SERVER['REQUEST_URI'], $language);

            if (sizeof($evaLanguages) == 1) {
                if (empty($language)) return;
                \app::redirect(substr($_SERVER['REQUEST_URI'], 6));
            };

            $default = array_keys($this->getUserLanguages());

            if (empty($language)) {
                \app::redirect('/' . strtolower($default[0]) . $_SERVER['REQUEST_URI']);
            };

            $language = (substr($language[1], 0, 2) . '-' . strtoupper(substr($language[1], 3, 5)));
            if (!in_array($language, array_keys($this->getSupportedLanguages()))) {
                \app::redirect('/' . strtolower($default[0]) . substr($_SERVER['REQUEST_URI'], 6));
            }
        }

        /**
         * Функция возвращает текущий язык
         *
         * @return string
         */
        public function getCurrentLanguage() {
            $evaLanguages = empty($this->appLanguages) ? \app::getParam('eva:languages') : $this->appLanguages;
            if (sizeof($evaLanguages) == 1) {
                $t = $evaLanguages;
                return $t[0];
            };
            $language = NULL;
            preg_match('/([a-z]{2}\-[a-z]{2})(.*)/', $_SERVER['REQUEST_URI'], $language);
            return (substr($language[1], 0, 2) . '-' . strtoupper(substr($language[1], 3, 2)));
        }

        /**
         * Функция возвращает все языки
         *
         * @return mixed
         */
        public function getAllLanguages() {
            return $this->languages;
        }

        /**
         * Функция возвращает список всех поддерживаемых языков
         *
         * @return array
         */
        public function getSupportedLanguages() {
            $evaLanguages = $this->appLanguages == NULL ? \app::getParam('eva:languages') : $this->appLanguages;
            $return = array();
            foreach ($evaLanguages as $k => $v) {
                $return[$v] = $this->languages[$v];
            };
            return $return;
        }

        /**
         * Функция возвращает пользовательские языки
         *
         * @return array
         */
        public function getUserLanguages() {
            $lang = array();
            $evaLanguages = $this->appLanguages == NULL ? \app::getParam('eva:languages') : $this->appLanguages;

            /// Получение языка для страны
            $country = function_exists('geoip_country_code3_by_name') ? geoip_country_code3_by_name($_SERVER['REMOTE_ADDR']) : NULL;
            $country = $this->cultures[$country];
            if (!empty($country)) {
                $lang[] = $country['language-culture-name'];
                unset($country);
            }

            /// Получени языка по заголовку HTTP Accept-Language
            $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $lang[] = $langs[0];
            unset($langs[0], $langs[1]);

            foreach ($langs as $k => $v) {
                $v = explode(';', $v);
                $lang[] = $v[0];
            };
            unset($langs);
            array_unique($lang);

            /// Заполнение массива данными о названии языка, культуре и т.д.
            $langs = array();
            foreach ($lang as $k2 => $v2) {
                foreach ($evaLanguages as $k => $v) {
                    $v1 = substr($v, 0, 2);
                    if ($v1 == $v2 || $v == $v2) {
                        $langs[$v] = $this->languages[$v];
                        break;
                    }
                }
            }
            unset($lang);
            return $langs;
        }
    };
}

/// Copyright © 2014-2015 Чернов, Александр. Contacts: <aeonrush@live.ru>, <black_web@outlook.com>
/// License: http://www.apache.org/licenses/LICENSE-2.0