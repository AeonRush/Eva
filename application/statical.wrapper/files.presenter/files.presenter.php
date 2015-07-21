<?php
namespace StaticalWrapper\Presenter {
    /**
     * Class Files
     *
     * @package Statical\Presenter
     */
    final class Files extends \Eva\Presenter {

        /**
         * Возвращает ресурсы для Application
         */
        public function app(){
            ob_end_clean();
            $path = explode('/', $_GET['path']);
            $path[1] = $path[1].'.presenter/view';
            ob_end_clean();
            $this->file(__APP__.'/'.join('/', $path));
        }
        public function short(){
            $this->file(__APP__.'/'.dirname($_GET['path']).'.presenter/view/'.basename($_GET['path']));
        }
        /**
         * Возвращает ресурсы для Fragment
         */
        public function fragment(){
            $this->file(__APP__.'/fragment/'.preg_replace('/\//', '/view/', $_GET['path'], 1));
        }

        /**
         * Возвращает любой файл из __APP__
         */
        public function all(){
            $this->file(__APP__.'/'.dirname($_GET['path']).'/'.basename($_GET['path']));
        }
    };
}

/// Copyright © 2014-2015 Чернов, Александр. Contacts: <aeonrush@live.ru>, <black_web@outlook.com>
/// License: http://www.apache.org/licenses/LICENSE-2.0