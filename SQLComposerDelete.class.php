<?php
require_once 'SQLComposer.class.php';

/**
 * SQLComposerDelete
 *
 * A DELETE query
 */
class SQLComposerDelete extends SQLComposerWhere {

	/**
	 * @var array
	 */
	protected $using = array( );

	/**
	 * IGNORE
	 *
	 * @var bool
	 */
	protected $ignore = false;

	/**
	 * ORDER BY
	 *
	 * @var array
	 */
	protected $order_by = array( );

	/**
	 * LIMIT
	 *
	 * @var int
	 */
	protected $limit = null;


	/*******************
	 **  CONSTRUCTOR  **
	 *******************/

	/**
	 * Constructor.
	 *
	 * @param string $table
	 */
	public function __construct($table=null) {
		if (isset($table)) $this->delete_from($table);
	}

	/**
	 * Add a table to the DELETE FROM clause
	 *
	 * @param string $table
	 * @param array $params
	 * @param string $mysqli_types
	 * @return SQLComposerDelete
	 */
	public function delete_from($table, array $params = null, $mysqli_types = "") {
		$this->add_table($table, $params, $mysqli_types);
		return $this;
	}

	/**
	 * Add a table to the USING clause
	 *
	 * @param string $table
	 * @param array $params
	 * @param string $mysqli_types
	 * @return SQLComposerDelete
	 */
	public function using($table, array $params = null, $mysqli_types = "") {
		$this->using[] = $table;
		$this->_add_params('using', $params, $mysqli_types);
		return $this;
	}

	/**
	 * ORDER BY
	 *
	 * @param string $order_by
	 * @return SQLComposerDelete
	 */
	public function order_by($order_by) {
		$this->order_by[] = $order_by;
		return $this;
	}

	/**
	 * LIMIT
	 *
	 * @param int $limit
	 * @return SQLComposerDelete
	 */
	public function limit($limit) {
		$this->limit = $limit;
		return $this;
	}


	/*****************
	 **  RENDERING  **
	 *****************/

	/**
	 * Render the SQL query
	 *
	 * @return string
	 */
	public function render() {

		$tables = implode("\n\t", $this->tables);

		$using = empty($this->using) ? "" : "\nUSING " . implode("\n\t", $this->using);

		$where = $this->_render_where();

		$order_by = empty($this->order_by) ? "" : "\nORDER BY " . implode(", ", $this->order_by);

		$limit = !isset($this->limit) ? "" : "\nLIMIT " . $this->limit;

		return "DELETE FROM {$tables} {$using} WHERE {$where} {$order_by} {$limit}";
	}

	/**
	 * Get the parameters
	 *
	 * @return array
	 */
	public function getParams() {
		return $this->_get_params('tables', 'using', 'where');
	}

}