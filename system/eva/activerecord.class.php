<?php

namespace Eva;
/**
 * Class ActiveRecord
 * TODO : Закончить код
 *
 * Пример использования
 *
 *      \app::model('example')->where('id = :id')->id(1, PDO::PARAM_INT)->limit(1)->select()
 *      \app::model('example')->where('category = :category')->category(1, PDO::PARAM_INT)->page(1, 10)->orderby('date desc')->select('id, title, price')
 *
 *      \app::model('example')->where('id = :id')->id(1, PDO::PARAM_INT)->update(array('title' => 'new_title'))
 *
 *      \app::model('example')->id(1, PDO::PARAM_INT)->title('new_title', PDO::PARAM_STR)->insert()
 *
 *      \app::model('example')->where('id = :id')->id(1, PDO::PARAM_INT)->delete()
 *
 * @package Eva
 */
abstract class ActiveRecord extends Eva {
    protected $db;
    public function __construct(){
        $temp = array_clean( explode('\\', get_called_class()) );
        $this->instance = strtolower($temp[1]);
        unset($temp);
        $this->db = \app::$db;
    }

    /**
     * Магическая функция
     * @param $f
     * @param $a
     * @return $this|void
     */
    public function __call($f, $a) {
        $this->$f = array($a[0], $a[1]);
        return $this;
    }

    /**
     * Изменение названия таблицы в запросах
     * ->instance('new_table_name')
     * @param $i
     * @return $this
     */
    public function instance($i) {
        $this->instance = $i;
        return $this;
    }
    /**
     * Очистка добавленных параметров
     * @return $this
     */
    public function cleanup(){
        $this->__data__ = array();
        return $this;
    }

    /**
     * Задаёт Limit и Offset для запросов
     * ->limit(10, 0) => взять 10, пропустить 0
     * @param $l
     * @param null $o
     * @return $this
     */
    public function limit($l, $o = NULL) {
        $this->limit = array($l, PDO::PARAM_INT);
        if($o != NULL) $this->offset($o);
        return $this;
    }

    /**
     * Работа с Limit и Offset как со страницами :)
     * ->page(1, 10) => показать первую страницу с 10 элементами на странице
     * @param $p
     * @param $l
     * @return $this
     */
    public function page($p, $l) {
        $this->limit = array($l, PDO::PARAM_INT);
        if($p < 1) $p = 1;
        $this->offset($p-1);
        return $this;
    }
    /**
     * Задает Offset
     * ->offset(10) => пропустить 10
     * @param $o
     * @return $this
     */
    public function offset($o) {
        $this->offset = array($o, PDO::PARAM_INT);
        return $this;
    }

    /**
     * Выборка из БД endpoint
     * ->select('id, title')
     * @param string $what
     * @return null
     */
    public function select($what = '*'){
        return $this->exec('SELECT SQL_CALC_FOUND_ROWS '.$what.' FROM '.$this->instance);
    }

    /**
     * Обновление данных в  endpoint
     * ->update(array('id' => 1, 'title' => 'new_title'))
     * @param $a Наимменование полея для обновления
     * @return null
     */
    public function update($a) {
        $sth = 'UPDATE '.$this->instance;
        /**
         * Из списка параметров для обновления ($a) формируем запрос типа SET k = :k, k2 = :k2
         */
        $temp = array();
        foreach($a as $k => $v) {
            $temp []= $v.' = :'.$v; 
        };

        return $this->exec($sth.' SET '.join(',', $temp));
    }

    /**
     * Добавление новой записи в БД endpoint
     * ->insert()
     * @return null
     */
    public function insert() {
        $sth = 'INSERT INTO '.$this->instance;
        unset($this->instance);

        /**
         * Из списка параметров формируем запрос типа
         * INSERT INTO $instance ($keys) VALUES($value)
         */
        $values = array();
        $keys = array();
        foreach($this->__data__ as $k => $v) {
            $keys[]= $k;
            $values[]= ':'.$k;
        };
        return $this->exec($sth.' ('.join(',', $keys).') VALUES('.join(',', $values).')');
    }

    /**
     * Удаление записи из БД endpoint
     * ->delete()
     * @return null
     */
    public function delete() {
        return $this->exec('DELETE FROM '.$this->instance);
    }

    /**
     * Подготовка и выполнения запроса
     * На этой стадии добавляется Where, Limit, Group, Order
     * @param $statement
     * @return array|null
     * @throws \Exception
     */
    protected function exec($statement){
        $return = array();
        /**
         * Блок подготовки WHERE
         */
        unset($this->instance);
        $this->where = $this->where[0];
        foreach($this->__data__ as $k => $v) {
            if(!is_array($v[0])) continue;
            if($v[1] == PDO::PARAM_INT) {
                foreach($v[0] as $k2 => $v2)
                    if(!is_numeric($v2)) return NULL;
            };
            $this->where = str_replace(':'.$k, join(',', $v[0]), $this->where);
            unset($this->__data__[$k]);
        };
        if(isset($this->where{2}))
            $statement .= ' WHERE '.$this->where;
        unset($this->where);

        /**
         * Блок подготовки Group By и Order By
         */
        if(isset($this->groupby[0])) $statement .= ' GROUB BY '.$this->groupby[0];
        if(isset($this->orderby[0])) $statement .= ' ORDER BY '.$this->orderby[0];
        unset($this->orderby, $this->groupby);

        /**
         * Блок подготовки Limit
         */
        if($this->limit) {
            /**
             * Если есть Offset и Limit то Limit считается "страницей", т.е.
             * Limit = Limit * Offset
             */
            $return['limit'] = $this->limit[0];
            if($this->offset) {
                $return['page'] = $this->offset[0] + 1;
                $this->__data__['offset'][0] = $this->limit[0] * $this->offset[0];
                $return['offset'] = $this->offset[0];
                $statement .= ' LIMIT :offset, :limit';
            } else {
                $statement .= ' LIMIT :limit';
            };
        };

        /**
         * Магия PDO
         */
        $sth = \app::$db->prepare($statement);
        foreach($this->__data__ as $k => $v) {
            $sth->bindParam(':'.$k, $v[0], $v[1] ? $v[1] : PDO::PARAM_STR);
        };

        $sth->execute();
        $this->cleanup();
        /**
         * Если ошибок нет, выводим результат
         */
        if($sth->errorCode() == '00000') {
            if($return['limit'] == 1) {
                unset($return);
                return $sth->fetch(PDO::FETCH_ASSOC);
            };
            $return['result'] = $sth->fetchAll(PDO::FETCH_ASSOC);
            $return['total'] = \app::$db->query('SELECT FOUND_ROWS()')->fetch(PDO::FETCH_COLUMN);
            return $return;
        };
        throw new \Exception('PDO::Error');
    }

};

/// Copyright © 2014-2015 Чернов, Александр. Contacts: <aeonrush@live.ru>, <black_web@outlook.com>
/// License: http://www.apache.org/licenses/LICENSE-2.0