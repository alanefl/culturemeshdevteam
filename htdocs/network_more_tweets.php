<?php
	include 'environment.php';
	$cm = new \Environment();

	session_name($cm->session_name);
	session_start();

	$json_response = array(
		'error' => NULL,
		'html' => NULL,
		'continue' => 'n',
		'postSwitchValues' => array(
			'nmp_more_tweets' => NULL,
			'nmp_cur_roster_level' => NULL,
			'nmp_tweet_until_date' => NULL),
		'debug' => array(
			'location_ratio' => NULL,
			'origin_ratio' => NULL,
			'tweet_count' => NULL,
			'query_info' => NULL)
		);

	if (!isset($_POST['nmp_more_tweets']) || !isset($_POST['nmp_cur_roster_level']) 
		|| !isset($_POST['nmp_tweet_until_date']) || !isset($_POST['nid'])) {

			$json_response['error'] = 'Required variables were not set';
			echo json_encode($json_response);
			exit();
	}

	// setting up date stuff
	$this_moment = new \DateTime();
	$date_string = "";

	if ($_POST['nmp_tweet_until_date'] !== "0") {
		$date_string = $_POST['nmp_tweet_until_date'];
	}

	$date_dt = new DateTime($date_string);
	//$until_date = $date_dt->format('Y-m-d');
	$cur_until_date = $date_dt->format('Y-m-d');

	$dal = new \dal\DAL($cm->getConnection());
	$dal->loadFiles();
	$do2db = new \dal\Do2Db();
	$site_user = \dobj\User::createFromId((int) $_SESSION['uid'], $dal, $do2db);

	// get network for real
	$network = \dobj\Network::createFromId((int) $_POST['nid'], $dal, $do2db);

	// REALLY IMPORTANT
	$roster = $network->getNetworkQueryRoster();

	// get level
	$cur_level = (int) $_POST['nmp_cur_roster_level'];

	$loop_count = 0;
	$tweets = array();

	// TWEET REQUEST LOOP
	while (count($tweets) <= 0 && $loop_count < 3
		&& $cur_level < count($roster)) {
			
		$query = $roster[ $cur_level ];

		if ($query['component'] == 'both') {

			$network->query_origin_scope = $query['origin_level'];
			$network->query_location_scope = $query['location_level'];
		}

		if ($query['component'] == 'location')  {

			$network->query_location_scope = $query['level'];
		}

		if ($query['component'] == 'origin') {

			$network->query_origin_scope = $query['level'];
		}

		$tweet_manager = new \api\TweetManager($cm, $network,
				$dal, $do2db);

		$tweets = $tweet_manager->requestTweets('network_addtl', NULL, 
				array('until_date' => $cur_until_date, 
					'component' => $query['component']));

		if (count($tweets) < 15) {
			$cur_level++;
		}

		$loop_count++;
	}

	$json_response['debug']['tweet_count'] = count($tweets);

	$query_info = $tweet_manager->getQueryInfo();
	$json_response['until_date'] = $query_info['until_date'];
	$json_response['debug']['query_info'] = $query_info;

	$cm->closeConnection();

	/////////////////
	// GETTING THE HTML
	//
	// /////////////////////////////
	/////// make components //////////
	$m_comp = new \misc\MustacheComponent();

	$tweets->setMustache($m_comp);

	try
	{
		$p_html = $tweets->getHTML('network', array(
			'cm' => $cm,
			'network' => $network,
			'site_user' => $site_user,
			'mustache' => $m_comp
			)
		);

		$json_response['error'] = 'Success';
	}
	catch (\Exception $e)
	{
		$p_html = NULL;
		$json_response['error'] = 'Failure: ' . $e;
	}

	$json_response['html'] = $p_html;

	/////////////////////////////////////////////////////////////////////////////////
	// UPDATING UNTIL DATE
	//
	// check tweet date to see if it's < 10 days
	//
	// @new - $exhausted_query, false if we've progressed too far through timeline
	//
	///////////////////////////////////////////////////////////////////////////
	$earliest_tweet_date = new \DateTime($query_info['until_date']);

	// check for equality of until dates
	// if unequal, subtract a day from until date
	//
	if ($cur_until_date == $earliest_tweet_date->format('Y-m-d')) {
		$earliest_tweet_date->sub(new \DateInterval('P4D'));
	}

	// now check to see if we've past the 10 days mark
	$new_difference = $this_moment->diff($earliest_tweet_date);
	$exhausted_query = (int) $new_difference->format('%a') >= 8 || count($tweets) < 15;

	$json_response['debug']['date_difference'] = $new_difference->format('%a');

	/////////////////////////////////////////////////////////////////////////////////////


	/////////////////////////////////////////////////////////////////////////////////////
	// UPDATING THE QUERY
	//
	// if query is not exhausted, don't updated 
	// scope values
	//
	// @change last updated if haven't exhausted query
	//
	/////////////////////////////////////////////////
	if ( !$exhausted_query ) {

		$date = $earliest_tweet_date->format('Y-m-d');
		$json_response['until_date'] = $date;
		$json_response['postSwitchValues']['nmp_tweet_until_date'] = $date;
		$json_response['postSwitchValues']['nmp_cur_roster_level'] = $cur_level;

	}
	// else, update, change until dates to null
	else {
		$json_response['until_date'] = "0";
		$json_response['postSwitchValues']['nmp_tweet_until_date'] = "0";
		$json_response['postSwitchValues']['nmp_cur_roster_level'] = $cur_level + 1;
	}

	$json_response['continue'] = 'y';
	$json_response['postSwitchValues']['nmp_more_tweets'] = 1;

	// Stops the entire mechanism when we've exhausted queries for all locations
	// and origins
	//
	if ($json_response['postSwitchValues']['nmp_cur_roster_level'] === count($roster)) {

		$json_response['continue'] = 'n';
		$json_response['postSwitchValues']['nmp_more_tweets'] = 0;
	}

	// return stuff
	echo json_encode($json_response);
?>
