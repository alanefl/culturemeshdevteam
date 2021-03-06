<?php
namespace dal;

class StubStatement extends \PDOStatement {//\mysqli_stmt{

	protected $query;
	protected $result;

	public function __construct($query) {

		if (!is_string($query))
			throw new \InvalidArgumentException('I can tell this isn\'t a real query');

		$this->query = $query;
	}

	public function bind_param($types, $var1) {

		// get args, and shift off type string
		$args = func_get_args();
		array_shift($args);
		$query = $this->query;

		$count = 0;
		$qc = 0;
		for ($i = 0; $i < strlen($query); $i++) {

			if ($query[$i] == '?') {
				$query = substr($query, 0, $i).$args[$count].substr($query, $i+1);
				$count++;
				$qc++;
			}
		}

		if (count($args) > $qc){
			throw new \Exception('Expected '. count($args) . ' ?\'s. Found ' . $qc);
		}

		$this->query = $query;
		return $this->query;
	}

	public function bindParam() {
		return $this->query;
	}

	public function execute() {

		// stub, nothing happens
	}

	public function fetch() {
		
		$sr = new StubResult(1);
		return $sr->arrayify();
	}

	public function fetchAll() {

		// generate 5 stub results
		$all = array();
		for ($i = 0; $i < 5; $i++) {
			array_push($all, new StubResult($i));
		}
		return $all;

	}
}

?>
