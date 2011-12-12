<?php 

class DB {

	private static $_db;

	public static function init ($db_name, $db_host='localhost', $db_user='', $db_password='') {
		self::$_db =  new mysqli($db_host, $db_user, $db_password, $db_name);
		self::$_db->query('SET NAMES utf8');
	}

	public static function getItem($query) {	
		$result = self::$_db->query($query);
	
		if (self::$_db->error) {
			throw new Exception('DB Error (in: ' . $query . '): ' . self::$_db->error);
		}
		
		if ($result) {
			return $result->fetch_assoc();
		} else {
			return false;
		}
	}
	
	public static function getList($query) {
		$result = self::$_db->query($query);	
		
		if (self::$_db->error) {
			throw new Exception('DB Error (in: ' . $query . '): ' . self::$_db->error);
		}
		
		if ($result && $result->num_rows) {
			//	return $result->fetch_all(MYSQLI_ASSOC);
			$arr = array();
			while ($line = $result->fetch_assoc()) {
				array_push($arr, $line);
			}
			return $arr;
		} else {
			return array();
		}
	}
	
	public static function query($query) {
		self::$_db->query($query);
		
	
		if (self::$_db->error) {
			throw new Exception('DB Error (in: ' . $query . '): ' . self::$_db->error);
		}
		if (self::$_db->error) {
			throw new Exception(self::$_db->error);
		}
	}
	
	public static function setParam($table, $id, $param, $value, $quote_value=true) {
		if ($quote_value) {
			$value = '\'' . $value . '\'';
		} 
		
		$query = '
			UPDATE
				' . $table . '
			SET
				' . $param . '=' . $value . '
			WHERE
				id=' . $id . ' 
		';

		self::$_db->query($query);		
	
		if (self::$_db->error) {
			throw new Exception('DB Error (in: ' . $query . '): ' . self::$_db->error);
		}
		if (self::$_db->affected_rows) {
			return true;
		} else {
			return false;
		}
	}

	public static function last_id (){
		return self::$_db->insert_id;
	}
	
	public static function affected () {
		return self::$_db->affected_rows;
	}
	
	public static function escape ($str) {
		return self::$_db->real_escape_string($str);	
	}
	
	public static function error() {
		return self::$_db->error;
	}
	
}
