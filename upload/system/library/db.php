<?php
class DB {
	private $db;

	public function __construct($registry = null) {
		$class = 'DB\\' . DB_DRIVER;

		if (class_exists($class)) {
			$this->db = new $class(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
		} else {
			exit('Error: Could not load database driver ' . DB_DRIVER . '!');
		}
	}

	public function query($sql) {
		return $this->db->query($sql);
	}

	public function escape($value) {
		return $this->db->escape($value);
	}

	public function countAffected() {
		return $this->db->countAffected();
	}

	public function getLastId() {
		return $this->db->getLastId();
	}
}
