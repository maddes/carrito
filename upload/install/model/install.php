<?php

class ModelInstall extends Model {

	public function database($data)
	{
		$db = new DB();

		$file = DIR_APPLICATION.'opencart.sql';

		if (! file_exists($file)) {
			exit('Could not load sql file: '.$file);
		}

		$lines = file($file);

		if ($lines) {
			$sql = '';

			foreach($lines as $line) {
				if ($line && (substr($line, 0, 2) != '--') && (substr($line, 0, 1) != '#')) {
					$sql .= $line;

					if (preg_match('/;\s*$/', $line)) {
						$sql = str_replace('DROP TABLE IF EXISTS `oc_', 'DROP TABLE IF EXISTS `'.DB_PREFIX, $sql);
						$sql = str_replace('CREATE TABLE `oc_', 'CREATE TABLE `'.DB_PREFIX, $sql);
						$sql = str_replace('INSERT INTO `oc_', 'INSERT INTO `'.DB_PREFIX, $sql);

						$db->query($sql);

						$sql = '';
					}
				}
			}

			$db->query('SET CHARACTER SET utf8');

			$db->query("SET @@session.sql_mode = 'MYSQL40'");

			$db->query('DELETE FROM `'.DB_PREFIX."user` WHERE user_id = '1'");

			$db->query('INSERT INTO `'.DB_PREFIX."user` SET user_id = '1', user_group_id = '1', username = '".$db->escape($data['username'])."', salt = '".$db->escape($salt = random_str(9))."', password = '".$db->escape(sha1($salt.sha1($salt.sha1($data['password']))))."', firstname = 'John', lastname = 'Doe', email = '".$db->escape($data['email'])."', status = '1', date_added = NOW()");

			$db->query('DELETE FROM `'.DB_PREFIX."setting` WHERE `key` = 'config_email'");
			$db->query('INSERT INTO `'.DB_PREFIX."setting` SET `code` = 'config', `key` = 'config_email', value = '".$db->escape($data['email'])."'");

			$db->query('DELETE FROM `'.DB_PREFIX."setting` WHERE `key` = 'config_url'");
			$db->query('INSERT INTO `'.DB_PREFIX."setting` SET `code` = 'config', `key` = 'config_url', value = '".$db->escape(HTTP_OPENCART)."'");

			$db->query('DELETE FROM `'.DB_PREFIX."setting` WHERE `key` = 'config_encryption'");
			$db->query('INSERT INTO `'.DB_PREFIX."setting` SET `code` = 'config', `key` = 'config_encryption', value = '".$db->escape(random_str(1024))."'");

			$db->query('UPDATE `'.DB_PREFIX."product` SET `viewed` = '0'");

			$db->query('INSERT INTO `'.DB_PREFIX."api` SET name = 'Default', `key` = '".$db->escape(random_str(256))."', status = 1, date_added = NOW(), date_modified = NOW()");

			$api_id = $db->getLastId();

			$db->query('DELETE FROM `'.DB_PREFIX."setting` WHERE `key` = 'config_api_id'");
			$db->query('INSERT INTO `'.DB_PREFIX."setting` SET `code` = 'config', `key` = 'config_api_id', value = '".(int) $api_id."'");
		}
	}
}
