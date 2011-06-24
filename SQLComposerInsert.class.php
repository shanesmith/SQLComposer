<?php
require_once 'SQLComposer.class.php';

/**
 * SQLComposerInsert
 *
 * An INSERT query
 */
class SQLComposerInsert extends SQLComposerBase {

	/**
	 * IGNORE
	 *
	 * @var bool
	 */
	protected $ignore = false;

	/**
	 * To create an INSERT INTO ... SELECT ...
	 *
	 * @var SQLComposerSelect
	 */
	protected $select;

	/**
	 * ON DUPLICATE KEY UPDATE
	 *
	 * @var array
	 */
	protected $on_duplicate = array( );


	/*******************
	 **  CONSTRUCTOR  **
	 *******************/

	/**
	 * Constructor.
	 *
	 * @param string $table
	 */
	public function __construct($table = null) {
		if (isset($table)) $this->into($table);
	}


	/***************
	 **  METHODS  **
	 ***************/

	/**
	 * INSERT INTO
	 *
	 * @param string $table
	 * @return SQLComposerInsert
	 */
	public function insert_into($table) {
		return $this->into($table);
	}

	/**
	 * INSERT INTO
	 *
	 * @param string $table
	 * @return SQLComposerInsert
	 */
	public function into($table) {
		$this->add_table($table);
		return $this;
	}

	/**
	 * IGNORE
	 *
	 * @param bool $ignore
	 * @return SQLComposerInsert
	 */
	public function ignore($ignore = true) {
		$this->ignore = $ignore;
		return $this;
	}

	/**
	 * Set the columns for INSERT INTO table (col1, col2, ...)
	 *
	 * @param array $columns
	 * @return SQLComposerInsert
	 */
	public function columns(array $columns) {
		$this->columns = $columns;
		return $this;
	}

	/**
	 * Provide a set of values to be inserted.
	 *
	 * If the columns are not yet set and an associative array is passed,
	 * the array keys will be used as the columns.
	 *
	 * ex:
	 *  SQLComposer::insert_into('table')->values(array( 'id' => '25', 'name' => 'joe', 'fav_color' => 'green' ));
	 *
	 * will result in
	 *
	 *  INSERT INTO table (id, name, fav_color) VALUES (25, 'joe', 'green')
	 *
	 * @param array $values
	 * @param string $mysqli_types
	 * @return SQLComposerInsert
	 */
	public function values(array $values, $mysqli_types = "") {
		if (isset($this->select)) throw new SQLComposerException("Cannot use 'INSERT INTO ... VALUES' when a SELECT is already set!");

		if (!isset($this->columns) && SQLComposer::is_assoc($values)) {
			$this->columns(array_keys($values));
		}

		return $this->_add_params('values', array( $values ), $mysqli_types);
	}

	/**
	 * Return a SQLComposerSelect object to be used in a query of the type INSERT INTO ... SELECT ...
	 *
	 * @param string $select
	 * @param array $params
	 * @param string $mysqli_types
	 * @return SQLComposerSelect
	 */
	public function select($select = null, array $params = null, $mysqli_types = "") {
		if (isset($this->params['values'])) throw new SQLComposerException("Cannot use 'INSERT INTO ... SELECT' when values are already set!");

		if (!isset($this->select)) {
			$this->select = SQLComposer::select();
		}

		if (isset($select)) {
			$this->select->select($select, $params, $mysqli_types);
		}

		return $this->select;
	}

	/**
	 * ON DUPLICATE KEY UPDATE
	 *
	 * @param array $update
	 * @param array $params
	 * @param string $mysqli_types
	 * @return SQLComposerInsert
	 */
	public function on_duplicate(array $update, array $params = null, $mysqli_types = "") {
		$this->on_duplicate = array_merge($this->on_duplicate, $update);
		$this->_add_params('on_duplicate', $params, $mysqli_types);
		return $this;
	}


	/*****************
	 **  RENDERING  **
	 *****************/

	/**
	 * Render the INSERT query
	 *
	 * @return string
	 */
	public function render() {
		$table = $this->tables[0];

		$ignore = $this->ignore ? "IGNORE" : "";

		$columns = (empty($this->columns)) ? "" : "(" . implode(", ", $this->columns) . ")";

		if (isset($this->select)) {
			$values = "\n" . $this->select->render();
		} else {
			// can't count($this->columns) since some entries might have multiple columns
			$num_cols = substr_count($columns, ",") + 1;
			$placeholders = "(" . implode(", ", array_fill(0, $num_cols, "?")) . ")";

			$num_values = count($this->params['values']);

			$values = "\nVALUES " . implode(", ", array_fill(0, $num_values, $placeholders));
		}

		$on_duplicate =
			(empty($this->on_duplicate)) ? "" : "\nON DUPLICATE KEY UPDATE " . implode(", ", $this->on_duplicate);

		return "INSERT {$ignore} INTO {$table} {$columns} {$values} {$on_duplicate}";
	}

	/**
	 * Get the parameters array
	 *
	 * @return array
	 */
	public function getParams() {

		if (isset($this->select)) {

			$params = $this->select->getParams();

		} else {

			$params = array( );
			foreach ($this->params["values"] as $values) {
				if (SQLComposer::is_assoc($values)) {
					foreach ($this->columns as $col) $params[] = $values[$col];
				} else {
					$params = array_merge($params, array_slice($values, 0, sizeof($this->columns)));
				}
			}

		}

		$params = array_merge($params, (array)$this->params['on_duplicate']);

		if (!empty($this->mysqli_types)) {

			if (isset($this->select)) {
				$params[0] .= $this->mysqli_types['on_duplicate'];
			} else {
				$types = $this->mysqli_types['values'] . $this->mysqli_types['on_duplicate'];
				array_unshift($params, $types);
			}

		}

		return $params;
	}

}