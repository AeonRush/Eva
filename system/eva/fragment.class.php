<?php

namespace Eva {
    /**
     * Class Fragment
     *
     * @package Eva
     */
    class Fragment extends Eva {
        public function __construct($method, &$parent_view, $args = NULL){

            if(\Helper\Auth::getInstance()->isAuth() && method_exists($this, 'secure_'.$method)) {
                $method = 'secure_'.$method;
            };

            if(!method_exists($this, $method)) throw new \ErrorException('Method "'.$method.'" not present in class '.get_called_class());
            $this->view = $parent_view;

            return $this->$method($args);
        }

        /**
         * Просто shortcut для $this->view->add чтобы было так же как и в Presenter
         *
         * @param $a
         * @param $b
         *
         * @return mixed
         */
        protected function add($a, $b) {
            return $this->view->add($a, $b);

        }
        protected function render($view) {
            include (__APP__.'/'.strtolower(str_replace('\\', '/', get_called_class())).'/view/'.$view.'.view.phtml');
        }
    };
}

namespace Fragment {
    class PDO extends \PDO {};
}

/// Copyright © 2014-2015 Чернов, Александр. Contacts: <aeonrush@live.ru>, <black_web@outlook.com>
/// License: http://www.apache.org/licenses/LICENSE-2.0