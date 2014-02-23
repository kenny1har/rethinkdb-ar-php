<?php
class RDB {
	public static $rdbDatabase;
	public static $conn;
	public static $params;
	public $rdbSaved;

	public static function connect($host, $port, $database, $password) {
		self::$rdbDatabase = $database;
		self::$conn = r\connect($host, $port, $database, $password);
	}
	public static function initialize() {
		if (!isset(static::$table))
			static::$table = substr(get_called_class(), strrpos(get_called_class(), '\\')+1);
		self::$params[static::$table] = array();
		foreach (get_class_vars(get_called_class()) as $key => $value)
			if (!in_array($key, array('conn', 'params', 'table', 'rdbDatabase', 'rdbSaved')))
				self::$params[static::$table][$key] = $value;
		try {
			static::createTable();
			static::createIndex();
		} catch (Exception $e) {

		}
	}
	public static function mapArray($arr) {
		$obj = new Static();
		foreach ($arr as $key => $value) {
			$obj->{$key} = $value;
		}
		$obj->rdbSaved = true;
		return $obj;
	}
	public static function mapObject($object) {
		$arr = $object->toNative();
		$obj = new Static();
		foreach ($arr as $key => $value) {
			$obj->{$key} = $value;
		}
		$obj->rdbSaved = true;
		return $obj;
	}
	public static function get($id) {
		if ($id == null)
			return new Static();
		$arr = \r\table(static::$table)->get($id)->run(self::$conn)->toNative();
		if ($arr != null)
			return self::mapArray($arr);
		else
			return new Static();
	}
	public function save() {
		$paramsTemp = array();
		foreach (self::$params[static::$table] as $key => $value) {
			$paramsTemp[$key] = $this->$key;
		}

		if ($this->rdbSaved) {
			return $this->update($paramsTemp);
		} else {
			if (!isset($paramsTemp['id']))
				unset($paramsTemp['id']);
			return $this->insert($paramsTemp);
		}
	}
	public function insert(&$paramsTemp) {
		$ret = \r\table(static::$table)->insert($paramsTemp)->run(self::$conn)->toNative();
		if (isset($ret['generated_keys']))
			$this->id = $ret['generated_keys'][0];
		return $this->id;
	}
	public function update(&$paramsTemp) {
		return \r\table(static::$table)->get($this->id)->update($paramsTemp)->run(self::$conn);
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
	public static function runMany($rdb) {
		$ret = array();
		$arrs = $rdb->run(self::$conn)->toNative();
		foreach ($arrs as $arr) {
			$ret[] = self::mapArray($arr);
		}
		return $ret;
	}
	public static function runOne($rdb) {
		$run = $rdb->run(self::$conn);
		if ($run->valid())
			return self::mapArray($run->current()->toNative());
		else
			return new Static();
	}
	public static function createIndex() {

	}
	public static function browse($skip = 0, $limit = 0, $orderBy = array()) {
		$shops = \r\table(static::$table);
		if ($skip > 0)
			$shops->skip($skip);
		if ($limit > 0)
			$shops->limit($limit);
		if (!empty($orderBy))
			$shops->orderBy($orderBy);
		return static::runMany($shops);
	}
}
?>
