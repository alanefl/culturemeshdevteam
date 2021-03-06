<?php
namespace dobj;

class Event extends DisplayDObj {

	protected $id_network;
	protected $id_host;
	protected $date_created;
	protected $event_date;
	protected $title;
	protected $address_1;
	protected $address_2;
	protected $city;
	protected $country;
	protected $description;
	protected $region;

	// db stuff
	protected $network;
	protected $network_class;
	protected $city_cur;
	protected $region_cur;
	protected $country_cur;
	protected $city_origin;
	protected $region_origin;
	protected $country_origin;
	protected $language_origin;
	protected $origin;
	protected $location;
	protected $usr_image;

	protected $host;
	protected $email;
	protected $username;
	protected $first_name;
	protected $last_name;
	protected $img_link;

	public static function createFromId($id, $dal, $do2db) {

		// stub
	}

	public function display($context) {

	}

	public function getHTML($context, $vars) {

		// list_vars
		$list_vars = array();
		$list_vars['item_class'] = $vars['item_class'];
		
		if ($list_vars['item_class'] == 'active-event')
		  $list_vars['active'] = true;

		// get vars
		$cm = $vars['cm'];
		$mustache = $vars['mustache'];

		switch($context) {

		case 'card':

			// get template
			$template = file_get_contents($cm->template_dir . $cm->ds . 'network-event-card.html');
			return $mustache->render($template, array(
				'event' => $this->prepare($cm),
				'host' => $this->getName(),
				'date' => $this->formatDate('card'),
				'list_vars' => $list_vars,
				'vars' => $cm->getVars()
				)
			);
			break;

		case 'modal':

			$user = NULL;
			$owner = false;
			$attending = false;
			if (isset($vars['site_user'])) {
				$user = $vars['site_user'];
				
				if ($user->id == $this->id_host) {
					$owner = true;
				}
				if (in_array($this->id, $user->events_attending)) {
					$attending = true;
				}
			}

			// get template
			$template = file_get_contents($cm->template_dir . $cm->ds . 'network-event-modal.html');
			return $mustache->render($template, array(
				'event' => $this->prepare($cm),
				'host' => $this->getName(),
				'user' => $user,
				'attending' => $attending,
				'owner' => $owner,
				'list_vars' => $list_vars,
				'date' => $this->formatDate('modal'),
				'vars' => $cm->getVars()
				)
			);
			break;

		case 'div':

			// get template
			$template = file_get_contents($cm->template_dir . $cm->ds . 'network-event-div.html');
			return $mustache->render($template, array(
				'event' => $this->prepare($cm),
				'host' => $this->getName(),
				'date' => $this->formatDate('card'),
				'list_vars' => $list_vars,
				'vars' => $cm->getVars()
				)
			);
			break;

		case 'dashboard':

			$user = $vars['site_user'];

			if ($user->id == $this->id_host) {
				$owner = true;
			}
			else {
				$owner = false;
			}

			// get template
			$template = file_get_contents($cm->template_dir . $cm->ds . 'dashboard-event.html');
			return $mustache->render($template, array(
				'event' => $this->prepare($cm),
				'host' => $this->getName(),
				'owner' => $owner,
				'date' => $this->formatDate('modal'),
				'list_vars' => $list_vars,
				'vars' => $cm->getVars()
				)
			);
			break;

		}
	}

	public function getJSON() {

		return array(
			'id' => $this->id,
			'id_network' => $this->id_network,
			'id_host' => $this->id_host,
			'title' => $this->title,
			'address_1' => $this->address_1,
			'address_2' => $this->address_2,
			'city' => $this->city,
			'region' => $this->region,
			'country' => $this->country,
			'description' => $this->description,
			'email' => $this->email,
			'username' => $this->username,
			'first_name' => $this->first_name,
			'last_name' => $this->last_name,
			'img_link' => $this->img_link,
			'event_date' => $this->event_date,
			'owner' => $this->owner,
			'attending' => $this->attending
		);
	}

	public function getName() {

			// last resort, email
			if (isset($this->email))
				$name = $this->email;

			// make name username
			if (isset($this->username))
				$name = $this->username;

			// prioritize names
			if (isset($this->first_name)) {
				$name = $this->first_name;

				if (isset($this->last_name))
					$name .= ' '.$this->last_name;
			}
		return $name;
	}

	public function formatDate($context) {
		$date = new \DateTime($this->event_date);

		switch ($context) {
		case 'card':
			return $date->format('D jS @ g:iA'); // thanks, php for easy formatting
		case 'modal':
			return $date->format('l, F jS Y @ g:iA'); // thanks, php for easy formatting
		}
	}

	public function getSplit($property) {

		switch ($property) {
		case 'month':
			$date = new \DateTime($this->event_date);
			$obj = new \dobj\Blank();
			$obj->month = $date->format('M');
			$obj->year = $date->format('Y');
			return $obj;
		case 'network':
			return $this->getNetworkTitle();
		default:
			return $this->$property;
		}
	}

	public function checkMembership($user) {

		$this->attending = False;
		$this->owner = False;

		if ($user->id === $this->id_host) {

			$this->owner = True;
		}	
		else {
			
			$attendees = explode(', ', $this->event_attendees);

			if (in_array($user->id, $attendees)) {
				$this->attending = True;
			}
		}

	}
}
