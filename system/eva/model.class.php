<?php

namespace Eva {
    /**
     * Class Model
     * Provide access to \app::$db
     * AND THAT'S ALL :)
     * @package Eva
     */
    abstract class Model {
        protected $db;
        public function __construct(){
            $this->db = \app::$db;
        }
    };
}

namespace Model {
    class PDO extends \PDO {};
}

/// Copyright © 2014-2015 Чернов, Александр. Contacts: <aeonrush@live.ru>, <black_web@outlook.com>
/// License: http://www.apache.org/licenses/LICENSE-2.0