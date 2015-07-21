<?php

namespace Eva {
    /**
     * Class View
     *
     * @package Eva
     */
    final class View extends Eva {
        public $layout;
        public $presenter;

        public function __construct(&$presenter) {
            $this->presenter = $presenter;
            $l = \app::getParam('layout');
            $this->layout = \app::$app['section'] . '/'.(isset($l{0}) ? $l : 'default');
            unset($l);
        }

        /**
         * Добавление ресурсов
         *  css:src, css:inline
         *  javascript:src, javascript:inline
         *
         * @param $k
         * @param $v
         */
        public function add($k, $v) {
            $k = explode(':', $k);
            if (!in_array($k[1], array('inline', 'src'))) {
                $v = '/' . $k[1] . '/' . $v;
                $k[1] = 'src';
            };
            $this[$k[0]][$k[1]][] = $v;
            $this[$k[0]][$k[1]] = array_unique($this[$k[0]][$k[1]]);
        }

        /**
         * Обработка данных
         * Если $data - строка - обработка шаблона
         * Если $data - массив/объект - возвращение JSON
         *
         * @param $data
         */
        public function render($data) {
            if (!is_string($data)) return $this->ajax($data);
            return $this->layout($data);
        }

        /**
         * Работа со статическими файлами
         *
         * @param     $path
         * @param int $buffer
         */
        public function file($path, $buffer = 1024) {
            /// Если файла нет или расширение файла в "черном списке" -> ощибка 404
            if (
                (substr(basename($path), 0, 3) == '.ht')
                || !file_exists($path)
                || in_array(pathinfo($path, PATHINFO_EXTENSION), array('data', 'dat', 'bak', 'backup', 'sql', 'inc', 'key', 'config', 'phtml', 'php'))
            ) msg404();

            $path = preg_replace('/\?(.*)/', '', $path);

            header('Content-Description: ' . __EVA__);
            Mime::setContentType(pathinfo($path, PATHINFO_EXTENSION));
            header('Content-Length: ' . filesize($path));

            HttpCache::getInstance()->set(filesize($path) . filemtime($path) . $_SERVER['REQUEST_URI'], filemtime($path));

            /// Очищаем старый буфер
            ob_end_clean();

            if ($fd = fopen($path, 'rb')) {
                while (!feof($fd))
                    echo fread($fd, $buffer);
                fclose($fd);
            };

            exit;
        }

        /**
         * Обработка шаблона из файла + обработка layout
         *
         * @param $view
         */
        private function layout($view) {
            ob_start();
            include($this->presenter->path . '/view/' . $view . '.view.phtml');
            $this->content = ob_get_contents();
            ob_end_clean();

            ob_start();
            include(__APP__.'/'.str_replace('/', '/layout/', $this->layout).'/layout.phtml');
            $this->content = ob_get_contents();
            ob_end_clean();

            $c = aeon_pack(optimize(\app::postprocess($this->content)));
            unset($this);

            header('Content-length: '.strlen($c));
            /// Устанавливаем заголовок с языком содержимого
            header('Content-language: '.\Eva\Local::getInstance()->getCurrentLanguage(), true /* O_o */);

            switch(\app::getParam('eva:staticCache')) {
                case -1 : HttpCache::getInstance()->noCache(); break;
                case 0 : { /* Do noting. Let's HTTP server decide */ }; break;
                default : {
                    $t = 0;
                    foreach(get_included_files() as $f)
                        $t = max($t, filemtime($f));

                    HttpCache::getInstance()->set(crc32_fix($c).$_SERVER['REQUEST_URI'].$t, $t);
                };
            };

            exit($c);
        }

        /**
         * Возвращаем JSON
         *
         * @param $data
         */
        private function ajax(&$data) {
            $data = json_encode($data);
            header('Content-type: application/json');
            header('Content-length: '.strlen($data));
            echo $data;
            return;
        }
    };
}
/// Copyright © 2014-2015 Чернов, Александр. Contacts: <aeonrush@live.ru>, <black_web@outlook.com>
/// License: http://www.apache.org/licenses/LICENSE-2.0