<?php
require_once 'SQLComposer.class.php';

class SQLComposerDelete extends SQLComposerWhere {

	protected $delete = array( );
	protected $ignore = false;
	protected $order_by = array( );
	protected $limit = null;
	protected $using = array( );

	public function __construct() {
		if (isset($table)) $this->delete_from($table);
	}

	public function delete_from($table, array $params = null, $mysqli_types = "") {
		$this->add_table($table, $params, $mysqli_types);
		return $this;
	}

	public function using($table, array $params = null, $mysqli_types = "") {
		$this->using[] = $table;
		$this->_add_params('using', $params, $mysqli_types);
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

	public function render() {

		$tables = implode("\n\t", $this->tables);

		$using = empty($this->using) ? "" : "\nUSING " . implode("\n\t", $this->using);

		$where = $this->_render_where();

		$order_by = empty($this->order_by) ? "" : "\nORDER BY " . implode(", ", $this->order_by);

		$limit = !isset($this->limit) ? "" : "\nLIMIT " . $this->limit;

		return "DELETE FROM {$tables} {$using} WHERE {$where} {$order_by} {$limit}";
	}

	public function getParams() {
		$this->_get_params('tables', 'using', 'where');
	}

}