<?php
class RDB {
	public static $conn;
	public static $params;

	public static function initialize() {
		if (!isset(static::$table))
			static::$table = substr(get_called_class(), strrpos(get_called_class(), '\\')+1);
		static::$params = array();
		foreach (get_class_vars(get_called_class()) as $key => $value)
			if (!in_array($key, array('conn', 'params', 'table')))
				static::$params[$key] = $value;
	}
	public static function get($id) {
		$arr = \r\table(static::$table)->get($id)->run(static::$conn)->toNative();
		$obj = new Static();
		foreach ($arr as $key => $value) {
			$obj->{$key} = $value;
		}
		return $obj;
	}
	public function save() {
		$paramsTemp = array();
		foreach (static::$params as $key => $value) {
			$paramsTemp[$key] = $this->$key;
		}

		if (isset($this->id)) {
			return \r\table(static::$table)->get($this->id)->update($paramsTemp)->run(static::$conn);
		} else {
			unset($paramsTemp['id']);
			return \r\table(static::$table)->insert($paramsTemp)->run(static::$conn);
		}
	}
	public function delete() {
		if (!isset($this->id))
			return;
		return \r\table(static::$table)->get($this->id)->delete()->run(static::$conn);
	}
	public static function table() {
		return \r\table(static::$table);
	}
}
?>