<?php

/**
 * Class Router
 * Класс обработки путей URL
 */
final class Router {
    private static $self;
    private $rules = array();
    private $httpMethod = 'get';
	  
    public static function getInstance(){
		if(self::$self == NULL) self::$self = new self();
        return self::$self;
    }
    public function __construct(){
        if(!empty($_POST) && !empty($_FILES)) $this->httpMethod = 'post&files';
        elseif(!empty($_POST)) $this->httpMethod = 'post';
        elseif(!empty($_FILES)) $this->httpMethod = 'files';

		$_SERVER['REQUEST_URI2'] = iconv('cp1251', 'utf-8', substr($_SERVER['REQUEST_URI'], ($_SERVER['REQUEST_URI'][1] == '?') ? 2 : 1));

        # $method = function_exists('apc_exists') ? 'loadCachedResources' : 'loadStaticResources';
        # TODO Сделать возможность работать с кешем APC
        $method = 'loadStaticResources';

        if(!!\Helper\Auth::getInstance()->isAuth()) {

            $this->$method(__APP__.'/*/*/routes.secure.php');

            $role = \Helper\Auth::getInstance()->getRole();
            if(isset($role{1}) )
                $this->$method(__APP__.'/*/*/routes.'.$role.'.php');

        } else {
            $this->$method(__APP__.'/*/*/routes.unsecureonly.php');
        };

        $this->$method(__APP__."/*/*/routes.php");
        foreach($this->rules as $section => $rules) {
            $config_temp = \app::getSectionConfig($section);
            $urlSection = $config_temp['eva:urlSection'];
            foreach ($rules as $template => $params) {

                $url = explode(':', $template);

                $url[1] = !$url[1] ? 'get' : $url[1];
                if ($url[1] !== $this->httpMethod) continue;

                $matches = array();
                $url = str_replace('^', '^([a-z]{2}\-[a-z]{2}[/]{1})?', str_replace('$section/', $urlSection, $url[0]));

                if (preg_match('/' . str_replace('/', '\/', $url) . '/', urldecode($_SERVER['REQUEST_URI2']), $matches) == true) {

                    /**
                     * Настройки языка для Section. Например если сайт на русском, а административная панель на ТОЛЬКО английском.
                     * Если файла нет, то настройки по умолчанию.
                     */
                    \app::configSectionInject($section);
                    \Eva\Local::getInstance()->localeCheck();

                    $e = explode('?', $params);
                    $i = sizeof($matches);
                    for ($j = 1; $j < $i; ++$j) {
                        $e[1] = str_replace('$' . $j, $matches[$j + 1], $e[1]);
                    };

                    $e[1] = strtr(addslashes(urldecode($e[1])), array('=' => '":"', '&' => '","'));

                    if (isset($e[1]{5})) $_GET = array_merge($_GET, json_decode(sprintf('{"%s"}', str_replace("\\'", "'", $e[1])), true));

                    list($class, $method) = explode('/', $e[0]);
                    \app::$app = array('section' => $section, 'class' => $class, 'method' => $method);
                    $class = '\\' . str_replace('.', '', $section) . '\Presenter\\' . $class;

                    $e = new $class();
                    if (method_exists($e, $method)) {
                        $e->$method();
                    } else {
                        unset($class, $section, $method, $e, $url, $matches);
                        msg404();
                    }
                    unset($class, $section, $method, $e, $url, $matches);
                    return;
                };
            };
        };
		msg404();
	}

    /**
     * Использование APC в качестве кэша для роутера :)
     * @param $path
     */
    private function loadCachedResources($path) {
        if(apc_exists('routes-'.crc32_fix($path))) {
            $this->rules = array_merge($this->rules, json_decode(apc_fetch('routes-'.crc32_fix($path)) ,true));
            return;
        }
        $this->loadStaticResources($path);
    }
    /**
     * Загрузка параметров роутера из файлов в ФС
     * @param $path
     */
    private function loadStaticResources($path) {
        $files = glob($path);
        foreach($files as $k => $v) {
            $section = substr(dirname(dirname($v)), strlen(__APP__) + 1);
            if(empty($this->rules[$section])) $this->rules[$section] = array();
            $this->rules[$section] = array_merge($this->rules[$section], include($v));
        };
        unset($files);
    }
};

/// Copyright © 2014-2015 Чернов, Александр. Contacts: <aeonrush@live.ru>, <black_web@outlook.com>
/// License: http://www.apache.org/licenses/LICENSE-2.0