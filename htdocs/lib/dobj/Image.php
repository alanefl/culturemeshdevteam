<?php
namespace dobj;

class Image extends DisplayDObj {

	protected $hash;
	protected $post;
	protected $id_post;
	protected $event;
	protected $id_event;
	protected $profile;
	protected $id_profile;
	protected $date_loaded;
	protected $real_url;
	protected $host_url;

	public function __construct() {

		$this->post = 0;
		$this->event = 0;
		$this->profile = 0;
	}

	public function display($context) {

	}

	public static function createFromId($id, $dal, $do2db) {
		return new Image();
	}

	public function insert($dal, $do2db) {

		if (!isset($this->hash)) 
			throw new \Exception('No hash is set');

		if (strlen($this->hash) != 32)
			throw new \Exception('This hash is the wrong length');

		if ($this->post == 0 && $this->event == 0 && $this->profile == 0)
			throw new \Exception('Must specify an image type before inserting');

		$result = $do2db->execute($dal, $this, 'insertImage');

		if ($result != True) {
			return False;
		}
		else {
			$this->id = $dal->lastInsertId();
			return $this->id;
		}
	}

	public function register($dal, $do2db, $oid, $class) {

		if (!isset($oid))
			throw new Exception('object id isn\'t set');

		switch ($class) {

		case 'post':
			$obj = new \dobj\Blank();
			$obj->id_post = $oid;
			$obj->id = $this->id;

			$do2db->execute($dal, $obj, 'registerPostImage');
			break;
		}

	}

	public function getHTML($context, $vars) {

	}

	public function convertToDir($hash) {

		$file_arr = str_split($hash);
		$filename_dir = '';
		$slash_pos = array(2, 4, 4, 4, 4, 4, 4, 3, 3);
		
		$i = 0;
		while (isset($slash_pos[$i])) {

			$count = $slash_pos[$i];
			// countdown to next slash
			while($count > -1) {

				if ($count == 0 && $i < count($slash_pos) - 1) 
				  { $filename_dir .= DIRECTORY_SEPARATOR; }
				else 
				  { $filename_dir .= array_shift($file_arr); }

				$count--;
			}

			// increment i 
			$i++;
		}

		return $filename_dir;
	}

	public function getPathAndName($class=NULL) {

		$pn = $this->convertToDir($this->hash);
		$thumb = NULL;

		switch ($class) {
		case 'post':
			$thumb = $pn . '_pthumb';
			break;
		default:
			break;
		}

		// add extension
		$pn .= '.png';
		$thumb .= '.png';

		// give to the people
		return array(
			'dir' => $pn,
			'thumb' => $thumb
		);
	}

	/*
	 * Check if image is exists
	 */
	public function doesExist() {

		if (file_exists($this->getPathAndName()['dir']))
			return true;
		else
			return false;
	}
}

?>
