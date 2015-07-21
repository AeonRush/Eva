<?php

/**
 * Class SimpleCache
 * Очень простое кэширование
 * TODO Сделать и memcached
 */
final class SimpleCache {
    /**
     * Буфер для хранения
     * @var array
     */
    private $data = [];
    private $prefix = 'apc_';

    /**
     * Конструктор
     */
    public function __construct() {
        if(!$this->isAPCEnabled()) {
            $this->data = [];
            $this->prefix = 'mem_';
            error_log('APC is not supported on this OS! Please, contact your administrator!', 0);
        };
    }

    /**
     * Функция проверки доступности APC
     * @return bool
     */
    public function isAPCEnabled() {
        return !!function_exists('apc_add');
    }

    /**
     * Добавление элемента в кэш
     * @param $name
     * @param $value
     * @param int $ttl
     * @return mixed
     */
    public function add($name, $value, $ttl = 60) {
        return $this->{$this->prefix.'add'}($name, $value, $ttl);
    }

    /**
     * Добавление элемента в кэш APC
     * @param $name
     * @param $value
     * @param $ttl
     * @return bool
     */
    private function apc_add(&$name, &$value, &$ttl) {
        return apc_add($name, $value, $ttl);
    }

    /**
     * Добавление элемента в кэш MEM
     * @param $name
     * @param $value
     * @param $ttl
     * @return bool
     */
    private function mem_add(&$name, &$value, &$ttl){
        $this->data[crc32_fix($name)] = $value;
        return true;
    }

    /**
     * Получение элемента из кэша
     * @param $name
     * @return mixed
     */
    public function fetch($name) {
        return $this->{$this->prefix.'fetch'}($name);
    }

    /**
     * Получение элемента из кэша APC
     * @param $name
     * @return mixed
     */
    private function apc_fetch(&$name) {
        return apc_fetch($name);
    }

    /**
     * Получение элемента из кэша MEM
     * @param $name
     * @return mixed
     */
    private function mem_fetch(&$name) {
        return $this->data[crc32_fix($name)];
    }

    /**
     * Удаление элемента из кэша
     * @param $name
     * @return mixed
     */
    public function remove($name) {
        return $this->{$this->prefix.'remove'}($name);
    }

    /**
     * Удаление элемента из кэша APC
     * @param $name
     * @return bool|string[]
     */
    private function apc_remove(&$name) {
        return apc_delete($name);
    }

    /**
     * Удаление элемента из кэша MEM
     * @param $name
     * @return bool
     */
    private function mem_remove(&$name) {
        unset($this->data[crc32_fix($name)]);
        return true;
    }

    /**
     * Проверка наличия элемента в кэше
     * @param $name
     * @return mixed
     */
    public function exists($name) {
        return $this->{$this->prefix.'exists'}($name);
    }

    /**
     * Проверка наличия элемента в кэше APC
     * @param $name
     * @return bool|string[]
     */
    private function apc_exists(&$name) {
        return apc_exists($name);
    }

    /**
     * Проверка наличия элемента в кэше MEM
     * @param $name
     * @return bool
     */
    private function mem_exists(&$name) {
        return isset($this->data[crc32_fix($name)]);
    }
};

/// Copyright © 2014-2015 Чернов, Александр. Contacts: <aeonrush@live.ru>, <black_web@outlook.com>
/// License: http://www.apache.org/licenses/LICENSE-2.0