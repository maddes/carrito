<?php

namespace Carrito\Framework\Library;

use Carrito\Framework\Library\DB\Mysqli;

class Db
{
    private $db;

    public function __construct($app = null)
    {
        $class = ucfirst(DB_DRIVER);

        if (class_exists($class)) {
            $this->db = new Mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
        } else {
            exit('Error: Could not load database driver '.$class.'!');
        }
    }

    public function query($sql)
    {
        return $this->db->query($sql);
    }

    public function escape($value)
    {
        return $this->db->escape($value);
    }

    public function countAffected()
    {
        return $this->db->countAffected();
    }

    public function getLastId()
    {
        return $this->db->getLastId();
    }
}
