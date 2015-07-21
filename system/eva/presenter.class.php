<?php

namespace Eva {
    /**
     * Class Presenter
     * @package Eva
     */
    abstract class Presenter extends Eva {
        protected $view;
        public function __construct(){
            $this->view = new View($this);
            $path = explode('\\', get_called_class());
            $path = \app::$app['section'].'/'.$path[2].'.'.$path[1];
            $this->path = __APP__.'/'.strtolower($path);
        }

        /**
         * Просто shortcut для $this->view->render
         *
         * @param $data
         */
        public function render($data){
            $this->view->render($data);
        }

        /**
         * Просто shortcut для $this->view->file
         *
         * @param $path
         */
        public function file($path) {
            $this->view->file($path);
        }
    };
}

namespace Presenter {
    class PDO extends \PDO {};
}
/// Copyright © 2014-2015 Чернов, Александр. Contacts: <aeonrush@live.ru>, <black_web@outlook.com>
/// License: http://www.apache.org/licenses/LICENSE-2.0