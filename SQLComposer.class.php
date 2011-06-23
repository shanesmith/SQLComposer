<?php
require_once "SQLComposerBase.class.php";
require_once "SQLComposerWhere.class.php";

require_once "SQLComposerSelect.class.php";
require_once "SQLComposerInsert.class.php";
require_once "SQLComposerReplace.class.php";
require_once "SQLComposerUpdate.class.php";
require_once "SQLComposerDelete.class.php";


abstract class SQLComposer {

	public static function select($select = null, array $params = null, $mysqli_types = null) {
		return new SQLComposerSelect($select, $params, $mysqli_types);
	}


	public static function insert($table=null) {
		return self::insert_into($table);
	}

	public static function insert_into($table = null) {
		return new SQLComposerInsert($table);
	}


	public static function replace($table = null) {
		return self::replace_into($table);
	}

	public static function replace_into($table = null) {
		return new SQLComposerReplace($table);
	}


	public static function update($table=null) {
		return new SQLComposerUpdate($table);
	}

	public static function delete($table=null) {
		return self::delete_from($table);
	}

	public static function delete_from($table=null) {
		return new SQLComposerDelete($table);
	}


	public static function in($sql, array $params, $mysqli_types="") {
		$placeholders = implode(",", array_fill(0, sizeof($params), "?"));
		$sql = str_replace("?", $placeholders, $sql);
		if (strlen($mysqli_types) == 1) {
			$mysqli_types = str_repeat($mysqli_types, sizeof($params));
		}
		return array($sql, $params, $mysqli_types);
	}

	public static function paranthesis($str) {
		return "({$str})";
	}

	public static function is_assoc($array) {
		return (array_keys($array) !== range(0, count($array) - 1));
	}
}

class SQLComposerException extends Exception {}

?>