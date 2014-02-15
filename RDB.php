<?php
class RDB {
	public static $rdbDatabase;
	public static $conn;
	public static $params;

	public static function connect($host, $port, $database, $password) {
		self::$rdbDatabase = $database;
		self::$conn = r\connect($host, $port, $database, $password);
	}
	public static function initialize() {
		if (!isset(static::$table))
			static::$table = substr(get_called_class(), strrpos(get_called_class(), '\\')+1);
		self::$params[static::$table] = array();
		foreach (get_class_vars(get_called_class()) as $key => $value)
			if (!in_array($key, array('conn', 'params', 'table', 'rdbDatabase')))
				self::$params[static::$table][$key] = $value;
	}
	public static function get($id) {
		$arr = \r\table(static::$table)->get($id)->run(self::$conn)->toNative();
		$obj = new Static();
		foreach ($arr as $key => $value) {
			$obj->{$key} = $value;
		}
		return $obj;
	}
	public function save() {
		$paramsTemp = array();
		foreach (self::$params[static::$table] as $key => $value) {
			$paramsTemp[$key] = $this->$key;
		}

		if (isset($this->id)) {
			return \r\table(static::$table)->get($this->id)->update($paramsTemp)->run(self::$conn);
		} else {
			unset($paramsTemp['id']);
			return \r\table(static::$table)->insert($paramsTemp)->run(self::$conn);
		}
	}
	public function delete() {
		if (!isset($this->id))
			return;
		return \r\table(static::$table)->get($this->id)->delete()->run(self::$conn);
	}
	public static function createTable() {
		\r\db(self::$rdbDatabase)->tableCreate(static::$table)->run(self::$conn);
	}
	public static function table() {
		return \r\table(static::$table);
	}
}
?>