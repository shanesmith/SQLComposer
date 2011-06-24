<?php
require_once 'SQLComposer.class.php';

/**
 * SQLComposerReplace
 *
 * A REPLACE query
 */
class SQLComposerReplace extends SQLComposerBase {

	/**
	 * To create an REPLACE INTO ... SELECT ...
	 *
	 * @var SQLComposerSelect
	 */
	protected $select;

	/*******************
	 **  CONSTRUCTOR  **
	 *******************/

	/**
	 * Constructor.
	 *
	 * @param string|array $table
	 */
	public function __construct($table = null) {
		if (isset($table)) $this->into($table);
	}

	/**
	 * REPLACE INTO
	 *
	 * @param string|array $table
	 * @return SQLComposerReplace
	 */
	public function replace_into($table) {
		return $this->into($table);
	}

	/**
	 * REPLACE INTO
	 *
	 * @param string|array $table
	 * @return SQLComposerReplace
	 */
	public function into($table) {
		$this->add_table($table);
		return $this;
	}

	/**
	 * Set the columns for REPLACE INTO table (col1, col2, ...)
	 *
	 * @param string|array $columns
	 * @return SQLComposerReplace
	 */
	public function columns($column) {
		$this->columns = array_merge($this->columns, (array)$column);
		return $this;
	}

	/**
	 * Provide a set of values to be replaced.
	 *
	 * If the columns are not yet set and an associative array is passed,
	 * the array keys will be used as the columns.
	 *
	 * ex:
	 *  SQLComposer::replace_into('table')->values(array( 'id' => '25', 'name' => 'joe', 'fav_color' => 'green' ));
	 *
	 * will result in
	 *
	 *  REPLACE INTO table (id, name, fav_color) VALUES (25, 'joe', 'green')
	 *
	 * @param array $values
	 * @param string $mysqli_types
	 * @return SQLComposerReplace
	 */
	public function values(array $values, $mysqli_types = "") {
		if (isset($this->select)) throw new SQLComposerException("Cannot use 'REPLACE INTO ... VALUES' when a SELECT is already set!");

		if (!isset($this->columns) && SQLComposer::is_assoc($values)) {
			$this->columns(array_keys($values));
		}

		return $this->_add_params('values', array( $values ), $mysqli_types);
	}

	/**
	 * Return a SQLComposerSelect object to be used in a query of the type REPLACE INTO ... SELECT ...
	 *
	 * @param string|array $select
	 * @param array $params
	 * @param string $mysqli_types
	 * @return SQLComposerSelect
	 */
	public function select($select = null, array $params = null, $mysqli_types = "") {
		if (isset($this->params['values'])) throw new SQLComposerException("Cannot use 'REPLACE INTO ... SELECT' when values are already set!");

		if (!isset($this->select)) {
			$this->select = SQLComposer::select();
		}

		return $this->select->select($select, $params, $mysqli_types);
	}


	/*****************
	 **  RENDERING  **
	 *****************/

	/**
	 * Render the REPLACE query
	 *
	 * @return string
	 */
	public function render() {
		$table = $this->tables[0];

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

		return "REPLACE INTO {$table} {$columns} {$values}";
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

			if (!empty($this->mysqli_types['values'])) {
				array_unshift($params, $this->mysqli_types['values']);
			}

		}

		return $params;
	}

}