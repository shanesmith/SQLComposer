<?php
require_once 'SQLComposer.class.php';

class SQLComposerInsert extends SQLComposerBase {

	protected $ignore = false;

	protected $table;

	protected $select;

	protected $on_duplicate = array( );

	public function __construct($table = null) {
		if (isset($table)) $this->into($table);
	}

	public function insert_into($table) {
		return $this->into($table);
	}

	public function into($table) {
		if (isset($this->table)) throw new SQLComposerException("The table has already been set to: {$this->table}");

		$this->table = $table;
		return $this;
	}

	public function ignore($ignore = true) {
		$this->ignore = $ignore;
		return $this;
	}

	public function columns(array $columns) {
		$this->columns = $columns;
		return $this;
	}

	public function add_column($column) {
		$this->columns[] = $column;
		return $this;
	}

	public function values(array $values, $mysqli_types = "") {
		if (isset($this->select)) throw new SQLComposerException("Cannot use 'INSERT INTO ... VALUES' when a SELECT is already set!");

		if (!isset($this->columns) && SQLComposer::is_assoc($values)) {
			$this->columns(array_keys($values));
		}

		return $this->_add_params('values', array( $values ), $mysqli_types);
	}

	public function select($select = null, array $params = null, $mysqli_types = "") {
		if (isset($this->params['values'])) throw new SQLComposerException("Cannot use 'INSERT INTO ... SELECT' when values are already set!");

		if (!isset($this->select)) {
			$this->select = SQLComposer::select();
		}

		return $this->select->select($select, $params, $mysqli_types);
	}

	public function on_duplicate(array $update, array $params = null, $mysqli_types = "") {
		$this->on_duplicate = array_merge($this->on_duplicate, $update);
		$this->_add_params('on_duplicate', $params, $mysqli_types);
		return $this;
	}

	public function render() {
		$table = $this->table;

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