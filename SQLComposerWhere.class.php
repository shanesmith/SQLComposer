<?php
require_once "SQLComposer.class.php";

abstract class SQLComposerWhere extends SQLComposerBase {

	protected $where = array( );

	public function where($where, $params = null, $mysqli_types = "") {
		$this->where[] = $where;
		$this->_add_params('where', $params, $mysqli_types);
		return $this;
	}

	public function where_in($where, array $params, $mysqli_types = "") {
		list($where, $params, $mysqli_types) = SQLComposer::in($where, $params, $mysqli_types);
		return $this->where($where, $params, $mysqli_types);
	}

	public function open_where_and() {
		$this->where[] = array( '(', 'AND' );
		return $this;
	}

	public function open_where_or() {
		$this->where[] = array( '(', 'OR' );
		return $this;
	}

	public function close_where() {
		$this->where[] = array( ')' );
		return $this;
	}

	protected function _render_where() {
		return SQLComposerBase::_render_bool_expr($this->where);
	}


}