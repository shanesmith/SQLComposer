<?php
require_once "SQLComposerBase.class.php";
require_once "SQLComposerWhere.class.php";

require_once "SQLComposerSelect.class.php";
require_once "SQLComposerInsert.class.php";
require_once "SQLComposerReplace.class.php";
require_once "SQLComposerUpdate.class.php";
require_once "SQLComposerDelete.class.php";

/**
 * SQLComposer
 *
 * A factory class for queries.
 *
 * ex:
 *  SQLComposer::select(array("id", "name", "role"))->from("users");
 */
abstract class SQLComposer {

	/**************
	 **  SELECT  **
	 **************/

	/**
	 * Start a new SELECT statement
	 *
	 * @see SQLComposerSelect::__construct()
	 * @param string $select
	 * @param array $params
	 * @param string $mysqli_types
	 * @return SQLComposerSelect
	 */
	public static function select($select = null, array $params = null, $mysqli_types = null) {
		return new SQLComposerSelect($select, $params, $mysqli_types);
	}


	/**************
	 **  INSERT  **
	 **************/

	/**
	 * Start a new INSERT statement
	 *
	 * @see SQLComposerInsert::__construct()
	 * @param string $table
	 * @return SQLComposerInsert
	 */
	public static function insert($table=null) {
		return self::insert_into($table);
	}

	/**
	 * Start a new INSERT statement
	 *
	 * @see SQLComposerInsert::__construct()
	 * @param string $table
	 * @return SQLComposerInsert
	 */
	public static function insert_into($table = null) {
		return new SQLComposerInsert($table);
	}


	/***************
	 **  REPLACE  **
	 ***************/

	/**
	 * Start a new REPLACE statement
	 *
	 * @see SQLComposerReplace::__construct()
	 * @param string $table
	 * @return SQLComposerReplace
	 */
	public static function replace($table = null) {
		return self::replace_into($table);
	}

	/**
	 * Start a new REPLACE statement
	 *
	 * @see SQLComposerReplace::__construct()
	 * @param string $table
	 * @return SQLComposerReplace
	 */
	public static function replace_into($table = null) {
		return new SQLComposerReplace($table);
	}

	/**************
	 **  UPDATE  **
	 **************/

	/**
	 * Start a new UPDATE statement
	 *
	 * @see SQLComposerUpdate::__construct()
	 * @param string $table
	 * @return SQLComposerUpdate
	 */
	public static function update($table=null) {
		return new SQLComposerUpdate($table);
	}


	/**************
	 **  DELETE  **
	 **************/

	/**
	 * Start a new DELETE statement
	 *
	 * @see SQLComposerDelete::__construct()
	 * @param string $table
	 * @return SQLComposerDelete
	 */
	public static function delete($table=null) {
		return self::delete_from($table);
	}

	/**
	 * Start a new DELETE statement
	 *
	 * @see SQLComposerDelete::__construct()
	 * @param string $table
	 * @return SQLComposerDelete
	 */
	public static function delete_from($table=null) {
		return new SQLComposerDelete($table);
	}


	/***************
	 **  HELPERS  **
	 ***************/

	/**
	 * Given an sql snippet in the form "column in (?)"
	 * and an array of parameters to be used as operands,
	 * will return an array of the form array(sql, params, mysqli_types)
	 * with the sql's '?' expanded to the number of parameters.
	 * If the given mysqli_types is only one character, it will be repeated
	 * the number of parameters.
	 *
	 * ex:
	 *  $sizes = array(24, 64, 84, 13, 95);
	 *  SQLComposer::in("size in (?)", $sizes, "i");
	 *
	 * will return
	 *
	 *  array("size in (?, ?, ?, ?, ?)", array(24, 64, 84, 13, 95), "iiiii")
	 *
	 * @param string $sql
	 * @param array $params
	 * @param string $mysqli_types
	 * @return array
	 */
	public static function in($sql, array $params, $mysqli_types="") {
		$placeholders = implode(",", array_fill(0, sizeof($params), "?"));
		$sql = str_replace("?", $placeholders, $sql);
		if (strlen($mysqli_types) == 1) {
			$mysqli_types = str_repeat($mysqli_types, sizeof($params));
		}
		return array($sql, $params, $mysqli_types);
	}

	/**
	 * Whether the given array is associative
	 *
	 * @param $array
	 * @return bool
	 */
	public static function is_assoc($array) {
		return (array_keys($array) !== range(0, count($array) - 1));
	}
}

/**
 * SQLComposerException
 *
 * The main exception to be used within these classes
 */
class SQLComposerException extends Exception {}

?>