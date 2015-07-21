<?php

/**
 * Class EvaLoader
 * Класс автоматической загрузки классов и интерфейсов
 */
final class EvaLoader {
	public static $loader;

    /**
     * Singleton
     * @return AEONLoader
     */
    public static function getInstance() {
		if (self::$loader == NULL) self::$loader = new self();
		return self::$loader;
	}
	public function __construct() {
        spl_autoload_register(array($this, 'preprocess'));
	}

    /**
     * Detecting what kind of class we initialize
     * Example
     * (\FrontEnd)?\Model\simple
     * |__________|_____|______|
     *   Section   Type   Name
     *
     * @param $class
     *
     * @throws Exception
     */
    public function preprocess($class){
        if(class_exists($class)) return;
        $raw = strtr($class, '\\', '/');
        $class = array_clean(  explode('/', strtolower($raw)) );
        if(sizeof($class) == 3 && $class[1] == 'presenter') {
            if (!method_exists($this, $class[1])) throw new Exception('Fail');
            return $this->$class[1]($class[2]);
        };
        if(method_exists($this, $class[0].'s'))  {
            $class[0] .= 's';
            return $this->$class[0]($class[1]);
        };
        unset($class);
        return $this->maybe($raw);
    }

    /**
     * Presenters loading
     * /application/$class.presenter/$class.presenter.php
     * class $class extends \Eva\Presenter
     * @param $class
     */
    public function presenter($class) {
        include (__APP__.'/'.\app::$app['section'].'/'.$class.'.presenter/'.$class.'.presenter.php');
    }

    public function maybe($class) {
        $class = array_clean(  explode('/', $class) );
        include (__ROOT__.'/vendor/'.join('/', $class).'.php');
    }

    /**
     * Fragments loading
     * /application/$class.fragment/$class.fragment.php
     * class $class extends \Eva\Fragment
     * @param $class
     */
    public function fragments($class) {
        include (__APP__.'/fragment/'.$class.'/'.$class.'.fragment.php');
    }

    /**
     * Fragments loading
     * /application/$class.fragment/$class.fragment.php
     * class $class extends \Eva\Fragment
     * @param $class
     */
    public function helpers($class) {
        include (__HELPERS__.'/'.$class.'.php');
    }

    /**
     * Models load
     * /model/
     * class $class
     * @param $class
     */
    public function models($class) {
        include (__ROOT__.'/model/'.$class.'.model.php');
    }

    /**
     * Standard classes load
     * /system/$class.class.php
     * @param $class
     */
    public function evas($class) {
        include (__SYSTEM__.'/eva/'.$class.'.class.php');
    }
}; EvaLoader::getInstance();

/// Copyright © 2014-2015 Чернов, Александр. Contacts: <aeonrush@live.ru>, <black_web@outlook.com>
/// License: http://www.apache.org/licenses/LICENSE-2.0