<?php
require_once 'SQLComposer.class.php';


class SQLComposerUpdate extends SQLComposerWhere {

	protected $set = array( );
	protected $order_by = array( );
	protected $limit = null;
	protected $ignore = false;

	public function __construct($table = null) {
		if (isset($table)) $this->update($table);
	}

	public function update($table) {
		$this->add_table($table);
	}

	public function set($set, array $params = null, $mysqli_types = null) {
		if (is_array($set)) {
			foreach ($set as $col => $val) $this->set[] = "{$col}=?";
			$params = array_values($set);
		} else {
			$this->set[] = $set;
		}
		$this->_add_params('set', $params, $mysqli_types);
		return $this;
	}

	public function order_by($order_by) {
		$this->order_by[] = $order_by;
		return $this;
	}

	public function limit($limit) {
		$this->limit = $limit;
		return $this;
	}

	public function ignore($ignore = true) {
		$this->ignore = $ignore;
		return $this;
	}

	public function render() {
		$ignore = $this->ignore ? "IGNORE" : "";

		$tables = implode("\n\t", $this->tables);

		$set = "\nSET" . implode(", ", $this->set);

		$where = $this->_render_where();

		$order_by = empty($this->order_by) ? "" : "\nORDER BY " . implode(", ", $this->order_by);

		$limit = isset($this->limit) ? "\nLIMIT {$this->limit}" : "";

		return "UPDATE {$ignore} {$tables} {$set} {$where} {$order_by} {$limit}";
	}

	public function getParams() {
		return $this->_get_params("set", "where");
	}

}