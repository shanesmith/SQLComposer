<?php
require_once 'SQLComposer.class.php';

abstract class SQLComposerBase {

	protected $limit = null;

	protected $columns = array( );
	protected $tables = array( );
	protected $params = array( );
	protected $mysqli_types = array( );

	public function limit($limit) {
		$this->limit = (int)$limit;
		return $this;
	}

	public function add_table($table, array $params = null, $mysqli_types = "") {
		$this->tables[] = $table;
		$this->_add_params('tables', $params, $mysqli_types);
		return $this;
	}

	public function join($table, array $params = null, $mysqli_types = "") {
		return $this->add_table($table, $params, $mysqli_types);
	}

	public function from($table, array $params = null, $mysqli_types = "") {
		return $this->add_table($table, $params, $mysqli_types);
	}

	protected function _add_params($clause, array $params = null, $mysqli_types = "") {
		if (isset($params)) {

			if (!empty($mysqli_types)) {
				$this->mysqli_types[$clause] .= $mysqli_types;
			}

			if (!isset($this->params[$clause])) {
				$this->params[$clause] = array( );
			}

			$this->params[$clause] = array_merge($this->params[$clause], $params);

		}
		return $this;
	}

	protected function _get_params($order) {
		if (!is_array($order)) $order = func_get_args();

		$params = array( );

		$mysqli_types = "";

		foreach ($order as $clause) {
			if (empty($this->params[$clause])) continue;

			$params = array_merge($params, $this->params[$clause]);

			$mysqli_types .= $this->mysqli_types[$clause];
		}

		if (!empty($this->mysqli_types)) {
			array_unshift($params, $mysqli_types);
		}

		return $params;
	}

	public function getQuery() {
		return $this->render();
	}

	abstract public function getParams();

	abstract public function render();

	public function debug() {
		return $this->getQuery() . "\n\n" . print_r($this->getParams(), true);
	}

	protected function _render_bool_expr(array $expression) {

		$str = "";

		$stack = array( );

		$op = "AND";

		$first = true;
		foreach ($expression as $expr) {

			if (is_array($expr)) {

				if ($expr[0] == '(') {
					if (!$first)
						$str .= " " . $op;

					$str .= " (";

					array_push($stack, $op);
					$op = $expr[1];

					$first = true;
					continue;
				}
				elseif ($expr[0] == ')') {
					$op = array_pop($stack);
					$str .= " )";
				}

			}
			else {

				if (!$first)
					$str .= " " . $op;

				$str .= " (" . $expr . ")";

			}

			$first = false;
		}

		$str .= str_repeat(" )", count($stack));

		return $str;
	}

}