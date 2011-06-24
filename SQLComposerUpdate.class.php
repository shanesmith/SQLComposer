<?php
require_once 'SQLComposer.class.php';

/**
 * SQLComposerUpdate
 *
 * An UPDATE query
 */
class SQLComposerUpdate extends SQLComposerWhere {

	/**
	 * SET
	 *
	 * @var array
	 */
	protected $set = array( );

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

	/**
	 * IGNORE
	 *
	 * @var bool
	 */
	protected $ignore = false;


	/*******************
	 **  CONSTRUCTOR  **
	 *******************/

	/**
	 * Constructor.
	 *
	 * @param string $table
	 */
	public function __construct($table = null) {
		if (isset($table)) $this->update($table);
	}


	/**************
	 **  METHODS **
	 **************/

	/**
	 * UPDATE
	 *
	 * @param string $table
	 * @return SQLComposerUpdate
	 */
	public function update($table) {
		$this->add_table($table);
		return $this;
	}

	/**
	 * SET
	 *
	 * Can either be a string of the form "col=?"
	 * or an array with columns as keys and corresponding values as parameters.
	 *
	 * ex:
	 *  SQLComposer::update('table')->set(array( 'name' => 'john', 'fav_color' => 'blue' ))->where("id=?", array(25));
	 *
	 * will result in
	 *
	 *  UPDATE table SET name=?, fav_color=? WHERE id=?
	 *
	 * and parameters
	 *
	 *  array('john', 'blue', 25)
	 *
	 * @param string|array $set
	 * @param array $params
	 * @param null $mysqli_types
	 * @return SQLComposerUpdate
	 */
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

	/**
	 * ORDER BY
	 *
	 * @param string $order_by
	 * @return SQLComposerUpdate
	 */
	public function order_by($order_by) {
		$this->order_by[] = $order_by;
		return $this;
	}

	/**
	 * LIMIT
	 *
	 * @param int $limit
	 * @return SQLComposerUpdate
	 */
	public function limit($limit) {
		$this->limit = $limit;
		return $this;
	}

	/**
	 * IGNORE
	 *
	 * @param bool $ignore
	 * @return SQLComposerUpdate
	 */
	public function ignore($ignore = true) {
		$this->ignore = $ignore;
		return $this;
	}


	/****************
	 ** RENDERING  **
	 ****************/

	/**
	 * Render the SQL query
	 *
	 * @return string
	 */
	public function render() {
		$ignore = $this->ignore ? "IGNORE" : "";

		$tables = implode("\n\t", $this->tables);

		$set = "\nSET" . implode(", ", $this->set);

		$where = $this->_render_where();

		$order_by = empty($this->order_by) ? "" : "\nORDER BY " . implode(", ", $this->order_by);

		$limit = isset($this->limit) ? "\nLIMIT {$this->limit}" : "";

		return "UPDATE {$ignore} {$tables} {$set} {$where} {$order_by} {$limit}";
	}

	/**
	 * Get the parameters
	 *
	 * @return array
	 */
	public function getParams() {
		return $this->_get_params("set", "where");
	}

}