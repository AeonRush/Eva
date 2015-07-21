<?php

/**
 * Class app
 * Базовый класс
 */
final class app {
    public static $db;
    public static $app;
	private static $self;
    private static $cache;
    private static $router;
    private static $config;
    private static $extensions;
    private $events;

    /**
     * Singleton
     * @return app
     */
    public static function getInstance(){
		if(self::$self != NULL) return self::$self;
        self::$self = new self();

        /// Получение параметров конфигурации из файла
        self::$config = include(__NGN__ . '/config.inc');

        /// Подключение простого модуля кэширования с помощью APC
        # include(__SYSTEM__.'/aeon.cache.php');
        # self::$cache = new SimpleCache();

        /// Если есть APC храним в нём список helper'ов
        /*
        if(__APC__){
            if(apc_exists('helpers-list')) $ext = json_decode(apc_fetch('helpers-list'), true);
            else {
                $ext = glob(__HELPERS__.'/*.php');
                apc_add('helpers-list', json_encode($ext), 2);
            }
        } else {
            $ext = glob(__HELPERS__.'/*.php');
        };
        */
        /// Модуль авторизации
        # self::$extensions['auth'] = \Auth::getInstance();
/*
        /// Подключаем Helper'ы
        foreach($ext as $v) {
            include($v);
            $ext_name = substr(basename($v), 0, -4);
            $ext_class_name = '\Helper\\'.$ext_name;
            self::$extensions[$ext_name] = $ext_class_name::getInstance(self::$self);
        };
        unset($ext);
*/
        include(__SYSTEM__.'/aeon.local.php');
        \Eva\Local::getInstance(self::$self);

        /// Сайт работает с БД?
        if(self::getParam('db:enabled')) {
            try {
                /// Пытаемся подключится
                self::$db = new PDO('mysql:host='.self::getParam('db:server').'; dbname='.self::getParam('db:name'), self::getParam('db:user'), self::getParam('db:password'),  array( PDO::ATTR_PERSISTENT => true ));
                self::$db->query('SET character_set_client="utf8", character_set_results="utf8", collation_connection="cp1251_general_ci"');
            }
            catch(Exception $e){
                /// Падаем и ложим весь сайт
                error_log('PDO is not supported on this OS! Please, contact your administrator!', 0);
                msg503();
            };
        };
        /// Роутер
        self::$router = \Router::getInstance();

        /// Возвращаем экземпляр класса движка
        return self::$self;
	}

    public static function getSectionConfig($s) {
        return file_exists(__APP__.'/'.$s.'/config.inc') ? array_merge(self::$config, include(__APP__.'/'.$s.'/config.inc')) : self::$config;
    }
    public static function configSectionInject($s) {
        if(is_array($s)) {
            self::$config = array_merge(self::$config, $s);
        } else {
            if(file_exists(__APP__.'/'.$s.'/config.inc'))
                self::$config = array_merge(self::$config, include(__APP__.'/'.$s.'/config.inc'));
        }
    }

    /**
     * @param $n
     * @param $a
     */
    public function addEvent($n, $a) {
        $this->events[$n][] = $a;
    }

    /**
     * Simple redirect function
     * @param $url
     */
    public static function redirect($url) {
        header('Location: '.urldecode($url));
        exit;
    }

    /**
     * Get param value from config by key
     * @param $key
     * @return mixed
     */
    public static function getParam($key){
        return self::$config[$key];
    }

    /**
     * Create a new model
     * @param $m
     * @param null $a
     * @return mixed
     */
    public static function model($m, $a = NULL) {
        $m = '\Model\\'.$m;
        return new $m($a);
    }

    /**
     * Sanitize array
     * @param $a
     */
    public static function sanitize(&$a) {
        foreach($a as $k => $v) {
            if(is_array($v)) {
                self::sanitize($a[$k]);
                continue;
            }
            $a[$k] = htmlspecialchars($v);   
        }
    }
    public static function postprocess($html){
        foreach(self::$self->events[\EvenType::PostProcess] as $p) {
            $p[0]->$p[1]($html);
        };
        return $html;
    }

    /**
     * Provide access to extensions
     * Required PHP 5.3+
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments) {
        return self::$extensions[$name];
    }
};

/**
 * Engine events
 * Class Events
 */
final class EvenType {
    const PostProcess = 1;
};

/// Copyright © 2014-2015 Чернов, Александр. Contacts: <aeonrush@live.ru>, <black_web@outlook.com>
/// License: http://www.apache.org/licenses/LICENSE-2.0