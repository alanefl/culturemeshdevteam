<?php
namespace dal;

abstract class DBOp {

	protected $name;
	protected $query;
	protected $test_query;
	protected $returning;
	protected $returning_array;
	protected $returning_list;
	protected $returning_class;
	protected $nullable;
	protected $params;
	protected $param_types;
	protected $type_dict;
	protected $results;
	protected $connection;

	public function setValues($array=array()) {

		if (count($array) == 0) {
			throw InvalidArgumentException('Must include all values');
		}

		$this->type_dict = array(
			'b' => \PDO::PARAM_BOOL,
			'n' => \PDO::PARAM_NULL,
			'i' => \PDO::PARAM_INT,
			's' => \PDO::PARAM_STR,
			'l' => \PDO::PARAM_LOB
		);

		$this->name = $array['name'];
		$this->query = $array['query'];
		$this->test_query = $array['test_query'];
		$this->returning = $array['returning'];
		$this->nullable = $array['nullable'];
		$this->returning_value = $array['returning_value'];
		$this->returning_assoc = $array['returning_assoc'];
		$this->returning_list = $array['returning_list'];
		$this->returning_class = $array['returning_class'];
		$this->returning_cols = $array['returning_cols'];
		$this->params = $array['params'];
		$this->param_types = $array['param_types'];
	}

	public function getScheme() {

		return array(
			'params' => $this->params,
			'param_types' => $this->param_types,
			'nullable' => $this->nullable,
			'returning' => $this->returning,
			'returning_value' => $this->returning_value,
			'returning_assoc' => $this->returning_assoc,
			'returning_list' => $this->returning_list,
			'returning_class' => $this->returning_class,
			'returning_cols' => $this->returning_cols);
	}

	public function setConnection($con) {
		$this->connection = $con;
	}

	public function getConnection() {
		return $this->connection;
	}

	public abstract function execute($args, $type_string);
}

?>
