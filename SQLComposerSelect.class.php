<?php
require_once 'SQLComposer.class.php';

class SQLComposerSelect extends SQLComposerWhere {

	protected $distinct = false;
	protected $offset = null;

	protected $group_by = array( );
	protected $with_rollup = false;
	protected $having = array( );
	protected $order_by = array( );

	public function __construct($select = null, array $params = null, $mysqli_types = "") {
		if (isset($select)) {
			$this->select($select, $params, $mysqli_types);
		}
	}

	public function select($select, array $params = null, $mysqli_types = "") {
		$this->columns = array_merge($this->columns, (array)$select);
		$this->_add_params('select', $params, $mysqli_types);
		return $this;
	}

	public function distinct($distinct = true) {
		$this->distinct = (bool)$distinct;
		return $this;
	}

	public function group_by($group_by, array $params = null, $mysqli_types = "") {
		$this->group_by[] = $group_by;
		$this->_add_params('group_by', $params, $mysqli_types);
		return $this;
	}

	public function with_rollup($with_rollup = true) {
		$this->with_rollup = $with_rollup;
		return $this;
	}

	public function having($having, array $params = null, $mysqli_types = "") {
		$this->having[] = $having;
		$this->_add_params('having', $params, $mysqli_types);
		return $this;
	}

	public function order_by($order_by, array $params = null, $mysqli_types = "") {
		$this->order_by[] = $order_by;
		$this->_add_params('order_by', $params, $mysqli_types);
		return $this;
	}

	public function offset($offset) {
		$this->offset = (int)$offset;
		return $this;
	}

	public function having_in($having, array $params, $mysqli_types = "") {
		list($having, $params, $mysqli_types) = SQLComposer::in($having, $params, $mysqli_types);
		return $this->having($having, $params, $mysqli_types);
	}

	public function open_having_and() {
		$this->having[] = array( '(', 'AND' );
		return $this;
	}

	public function open_having_or() {
		$this->having[] = array( '(', 'OR' );
		return $this;
	}

	public function close_having() {
		$this->having[] = array( ')' );
		return $this;
	}

	protected function _render_having() {
		return SQLComposerBase::_render_bool_expr($this->having);
	}

	public function render() {
		$columns = empty($this->columns) ? "*" : implode(", ", $this->columns);

		$distinct = $this->distinct ? "DISTINCT" : "";

		$from = "\nFROM " . implode("\n\t", $this->tables);

		$where = empty($this->where) ? "" : "\nWHERE " . $this->_render_where();

		$group_by = empty($this->group_by) ? "" : "\nGROUP BY " . implode(", ", $this->group_by);

		$with_rollup = $this->with_rollup ? "WITH ROLLUP" : "";

		$having = empty($this->having) ? "" : "\nHAVING " . $this->_render_having();

		$order_by = empty($this->order_by) ? "" : "\nORDER BY " . implode(", ", $this->order_by);

		$limit = "";
		if ($this->limit) {
			$limit = "\nLIMIT {$this->limit}";
			if ($this->offset) {
				$limit .= "\nOFFSET {$this->offset}";
			}
		}

		return "SELECT {$distinct} {$columns} {$from} {$where} {$group_by} {$with_rollup} {$having} {$order_by} {$limit}";
	}

	public function getQuery() {
		return $this->render();
	}

	public function getParams() {
		return $this->_get_params('select', 'tables', 'where', 'group_by', 'having', 'order_by');
	}

}
