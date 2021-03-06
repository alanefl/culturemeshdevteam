<?php
namespace dobj;

class DObjList implements \Countable, \Iterator, \ArrayAccess {

	private $dlist = array();
	private $position = 0;
	private $m;

	private $ulhtml;
	private $lihtml;
	private $ulid;
	private $ulclass;

	protected $display_mode;

	public function __construct() {

		// just makin' sure
		$this->position = 0;
	}

	/*
	 * Insert dObj into list
	 */
	public function dInsert($item) {

		if (!$item instanceOf DObj) {
			throw new \InvalidArgumentException('Must insert type DObj.');
		}

		array_push($this->dlist, $item);

		return true;
	}

	/*
	 * Merge one array (or DObjList with this one)
	 *
	 * Could easily allow for multiple arrays in the future
	 *
	 * Params:
	 *   candidate - an array or dobjlist
	 */
	public function merge($candidate) {

		if (get_class($candidate) == 'dobj\DObjList') {
			$this->dlist = array_merge($this->dlist, $candidate->dlist);
			return True;
		}

		if (is_array($candidate)) {
			$this->dlist = array_merge($this->dlist, $candidate);
			return True;
		}

		// if neither option was reached
		return False;
	}

	/*
	 * Creating a version of array_values,
	 *   if used raw, dobjlist will be made null
	 */
	public function array_values() {

		$this->dlist = array_values($this->dlist);
	}

	/////// DISPLAY FUNCTIONS ///////////
	/// give a mustache engine
	public function setMustache($m) {
		$this->m = $m;
	}

	public function setUlId($ulid) {
		$this->ulid = $ulid;
	}

	public function setUlClass($ulclass) {
		$this->ulclass = $ulclass;
	}

	public function getHTML($context, $vars) {

		$displayable = false;

		// give the possibility for list template
		$mustache = $vars['mustache'];
		$list_template = $vars['list_template'];

		// check if the item is displayable
		// by checking first item in list
		//
		if (count($this->dlist) > 0) {

			$first_thing = $this->dlist[0];

			if (method_exists($first_thing, 'getHTML')) {
				$displayable = true;
			}
			else
			 { throw new \Exception(get_class($first_thing) . ' has no method, \'getHTML\''); }
		}
		else 
		 { throw new \Exception('This list is empty'); }

		 if ($displayable) {

			 $this->li_html = array();

			 foreach ($this->dlist as $obj) {

				// what now?
				$html = $obj->getHTML($context, $vars);

				array_push($this->li_html, $html);
			 }

			 if ($list_template === NULL) {
			   return $this->li_html;
			 }
			 else { // render full list
				 return $mustache->render($list_template,
					 array('items' => $this->li_html,
				 		'list_vars' => $vars['list_vars']));
			 }
		 }
	}

	/*
	 * Transforms array into sectioned list
	 *
	 * Params:
	 *   - property_arg - Can be a string representing some object property,
	 *        can be a closure determining how to separate objects 
	 *
	 *   - display_mode_arg - Determines how section titles will
	 *        be displayed when the lists elements are finally rendered
	 *        
	 *        > inline : Sections are denoted by a header within the list
	 *        > class : Items in the section are given a special css class
	 */
	public function splits($property_arg, $display_mode_arg='inline') {

		$splits = array();
		$property = NULL;


		// get display mode arg
		// if it's an array, shift off the first property
		// whether or not property_arg is an array
		//
		// in case the user makes a mistake, and only intends on 
		// one level of sectioning, only use the
		// first item of array as a display mode arg
		//
		// after this, array display_mode_arg will have one less element
		// and be ready to pass to a recursive splits call
		//
		if (is_array($display_mode_arg)) 
		  $display_mode = array_shift($display_mode_arg);
		else
		  $display_mode = $display_mode_arg;

		if (is_array($property_arg)) {
			$property = array_shift($property_arg);

		}
		else {
			$property = $property_arg;
		}

		// Loop through each object
		// and fold into a new dobjlist
		// based on a property of the object
		//
		// if there isn't already a list representing
		// the value of a property, a new list will
		// be created
		//
		foreach ($this->dlist as $obj) {
			
			// can be either a closure or a string
			if (is_object($property) && $property instanceof \Closure) {

				$result = $property($obj);
				$section = $result['section'];
				$key = $result['key'];
			}
			else {

				// split on object
				$section = $obj->getSplit($property);

				// split into a compact string
				// and also keep the parts recorded in object
				// in case we need them in display
				//
				if (is_object($section)) {

					$section_object = $section;
				  	$section = $section->standOut();
				}

				$key = $property;
			}

			// check for existing value
			$found = false;
			$index = 0;

			for ($i = 0; $i < count($splits); $i++) {
				$split = $splits[$i];

				// add to the thing
				if (isset($split['section']) && $split['section'] == $section) {
					$found = true;
					$index = $i;
					break;
				}

			}

			if ($found)
   			  $splits[$index]['array']->dInsert($obj);
			else {
				$new_list = new DObjList();
				$new_list->dInsert($obj);

				array_push($splits, array(
					'array' => $new_list,
					'section' => $section,
					'section_object' => $section_object,
					'key' => $key)
				);
			}
		}

		// If we've been given an array of properties
		// do the thing again with the recursion and stuff
		//
		if (is_array($property_arg)) {

			if (count($property_arg) > 0) {

				// make sure we have enough of display mode arg
				// to go around
				//
				if (count($display_mode_arg) <= 0)
					$display_mode_arg = $display_mode;

				foreach ($splits as &$split) {
					
					$split['array'] = $split['array']->splits($property_arg, $display_mode_arg);
				}
			}
		}

		$sl = new SectedDObjList();
		$sl->display_mode = $display_mode;
		$sl->slist = $splits;
		return $sl;
	}

	//////////// NICE TO HAVE ////////////////////
	public function reverse() {
		$this->dlist = array_reverse($this->dlist);
	}

	public function slice($offset, $length=NULL, $returning=False) {
		if ($returning === False)
		  $this->dlist = array_slice($this->dlist, $offset, $length);
		else {
			$list = new DObjList();

			if ($length > count($this))
			  $length = count($this);

			for($i = $offset; ($i < ($offset + $length)) && (isset($this[$i])); $i++) {
			  $list->dInsert( $this[$i] );
			}

			return $list;
		}

	}

	//////////// THINGS I MUST SET ////////////////
	public function count() {

		return count($this->dlist);
	}

	public function sort($options) {

		$default_options = array(
			'key' => NULL,
			'order' => 'desc',
			'sorting_date' => False
		);

		$options = array_merge($default_options, $options);

		usort($this->dlist, function($a, $b) use ($options) {

			$a = $a->$options['key'];
			$b = $b->$options['key'];

			// Make into a date
			//
			if ($options['sorting_date'] == True) {

				$a = new \DateTime($a);
				$b = new \DateTime($b);
			}

			if ($options['order'] == 'desc') {
				return ($a > $b) ? 1 : -1;
			}
			else if ($options['order'] == 'asc') {
				return ($a > $b) ? -1 : 1;
			}
			else {
				throw new \Exception('DObjList->sort: ' . $options['order'] . ' is not a valid sorting arrangement. Choose \'asc\' or \'desc\'');
			}
		});
	}

	public function me() {
		return $this->dlist;
	}

	// return current thing
	function current() {
		return $this->dlist[$this->position];
	}	

	// return key of current element
	function key() {
		return $this->position;
	}

	// move forward by one
	function next() {
		$this->position += 1;
	}

	// reset at zero
	function rewind() {
		$this->position = 0;
	}

	// get end element
	function end() {
		return end($this->dlist);
	}

	// checks if current position is valid
	function valid() {
		return isset($this->dlist[$this->position]);
	}

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->dlist[] = $value;
		} else {
			$this->dlist[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->dlist[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->dlist[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->dlist[$offset]) ? $this->dlist[$offset] : null;
	}
}

?>
