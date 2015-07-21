<?php

/**
 * Исправление неверного расчета контрольной суммы CRC32 в PHP
 * @param $v
 * @return int
 */
function crc32_fix($v){
	$v = crc32($v);
	return ($v < 0) ? ($v + 4294967296) : $v;
};

/**
 * Очистка массива от пустых элементов
 * @param $a
 * @return array
 */
function array_clean($a) {
    $c = array();
    foreach ($a as $k=>$v){
        if(!empty($v)) $c[] = $v;
    };
    unset($a);
    return $c;
};

/**
 * Очистка массива переданного как указатель
 * @param $a
 */
function array_clean_ref(&$a) {
    $a = array_clean($a);
};

/**
 * Разделение строки на отдельные слова
 * @param $str
 *
 * @return array
 */
function split_string_to_words($str) {
    $str = preg_split('~[^\p{L}\p{N}\']+~u', $str);
    array_clean_ref($str);
    return $str;
};

/**
 * Построение URL по заданным параметрам
 * Если параметр в $new равен NULL он удаляется из URL
 * @param      $new
 * @param bool $withPrefix
 *
 * @return string
 */
function buildURL($new, $withPrefix = false){ 
	$new = array_merge($_GET, $new);

	$url = array();
	foreach($new as $k => $v) {
        if(!$v) continue;
	    $url[] = $k.'='.$v;    
	};
    unset($new);

	$url = ((sizeof($url) > 0) ? '?' : '').join('&', $url);

	if(!$withPrefix) return $url;
	
	$prefix = explode('/', $_SERVER['REQUEST_URI']);
	
	if(sizeof($prefix) > 1 && isset($prefix[0]{1})){
		unset($prefix[sizeof($prefix)-1]);
		$prefix = join('/', $prefix).'/'; 
	} else $prefix = '';

	return $prefix.$url;
};

/**
 * Преобразование строки в каноническую ссылку
 * @param $url
 * @return string
 */
function toCanonical($url){
	$url = strip_tags($url);
	$url = mb_substr($url, 0, 100);
	$url = str_replace('№', '-', $url);
    $url = mb_ereg_replace('\W+', '-',  $url );
   	return mb_ereg_replace('\-+', '-', $url);
	# return mb_strtolower( mb_ereg_replace('\_+', '-', $url), 'UTF-8' );
};

/**
 * Очистка строки
 * @param $str
 * @return string
 */
function strclean(&$str){
	$str = strip_tags($str);
	/// $str = str_replace('№', '_', $str);
    $str = mb_ereg_replace('\W+', ' ',  $str );
	return $str;
};

/**
 * Адекватное отображение JSON в кодировке UTF-8
 * @param $value
 * @return mixed
 */
function json_encode_utf8($value){
	return ( preg_replace_callback (
    	'/\\\u([0-9a-fA-F]{4})/',
    	create_function('$match', 'return mb_convert_encoding("&#".intval($match[1], 16).";", "UTF-8", "HTML-ENTITIES");'),
    	json_encode($value)
    ) ); 
};

/**
 * Это поисковый робот?
 * @return int
 */
function isCrowler() {
    /// Названия User-Agent'ов поисковых пауков
	return preg_match('/(bingbot|msnbot|googlebot|slurp|teoma|scooter|ia_archiver|lycos|yandex|stackrambler|mail\.ru|aport|webalta)/i', $_SERVER['HTTP_USER_AGENT']);
};

/**
 * Это мобильный браузер?
 * @return int
 */
function isMobile() {
	/// Android не указывает в агенте mobile
	return preg_match('/(mobile|android|phone)/i', $_SERVER['HTTP_USER_AGENT']);
};

/**
 * Оптимизация
 * @param $__content
 * @return mixed
 */
function text_optimize(&$__content) {
	$__content = str_replace('\r\n', '', $__content);
	$__content = str_replace('\n', '', $__content);
	$__content = str_replace('\r', '', $__content);
	$__content = str_replace('\t', '', $__content);

	return $__content;
};
/**
 * Оптимизация
 * @param $__content
 * @return mixed|string
 */
function optimize(&$__content) {
	# $__content = preg_replace('/\/\*.*\*\//', '', $__content);
    # $__content = mb_ereg_replace('([^:]\/\/[^\n\r]*(\n|\r\n))', '', $__content);
    # $__content = mb_ereg_replace('(\/\*.*\*\/)', '', $__content);

    $__content = mb_ereg_replace('(\s[\s]+)|(\t+)|(\n[\n]*)|(\r[\n]*)|(<!---.*?-->)', '', $__content);
    $__content = preg_replace('/(\xEF\xBB\xBF)+/', '', $__content);

    $__content = str_replace('<![CDATA[', "<![CDATA[\n", $__content);
    $__content = str_replace('// ]]>', "// ]]>\n", $__content);

	return $__content;
};

/**
 * Удаление дерева каталогов
 * @param $dir
 * @return bool
 */
function deleteTree($dir) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) { (is_dir("$dir/$file")) ? deleteTree("$dir/$file") : unlink("$dir/$file"); };
    return rmdir($dir);
};

/**
 * Упаковываем контент для передачи браузеру :)
 * @param $output
 * @return string
 */
function aeon_pack($output){
	/// Поддерживается ли deflate
	if( strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') ) {
		header('Content-Encoding: deflate');	/// Устанавливаем заголовок
		$output = gzdeflate($output, 6);		/// Упаковываем :)
	/// deflate не поддерживается, а gzip?
	} elseif ( strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') ) {					
		header('Content-Encoding: gzip');		/// Устанавливаем заголовок
		$output = gzencode($output, 6);			/// Упаковываем :)
	};
	return ($output); /// Возвращаем результат
};

/// Copyright © 2014-2015 Чернов, Александр. Contacts: <aeonrush@live.ru>, <black_web@outlook.com>
/// License: http://www.apache.org/licenses/LICENSE-2.0