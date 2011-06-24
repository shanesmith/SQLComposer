<?php
require_once 'SQLComposer.class.php';

class SQLComposerReplace extends SQLComposerBase {

	protected $select;

	public function __construct($table = null) {
		if (isset($table)) $this->into($table);
	}

	public function replace_into($table) {
		return $this->into($table);
	}

	public function into($table) {
		$this->add_table($table);
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
		if (isset($this->select)) throw new SQLComposerException("Cannot use 'REPLACE INTO ... VALUES' when a SELECT is already set!");

		if (!isset($this->columns) && SQLComposer::is_assoc($values)) {
			$this->columns(array_keys($values));
		}

		return $this->_add_params('values', array( $values ), $mysqli_types);
	}

	public function select($select = null, array $params = null, $mysqli_types = "") {
		if (isset($this->params['values'])) throw new SQLComposerException("Cannot use 'REPLACE INTO ... SELECT' when values are already set!");

		if (!isset($this->select)) {
			$this->select = SQLComposer::select();
		}

		return $this->select->select($select, $params, $mysqli_types);
	}

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