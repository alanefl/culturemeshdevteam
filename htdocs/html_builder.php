<?php

include_once "zz341/fxn.php";

class HTMLBuilder
{
	public static function displayPopNetwork($network)
	{
		$title = HTMLBuilder::formatNetworkTitle($network);
		$post_count = $network->post_count;
		$member_count = $network->member_count;
		
		if ($post_count == NULL)
			$post_count = 0;
		if ($member_count == NULL)
			$member_count = 0;
		// Print network
		echo "
		<div id='pn_{$network->id}' class='popnet'>
			<div class='popnet-info'>
				<a href='network/{$network->id}'><p class='bottom-text'>{$title}</p></a>
				<p class='network-stats'>{$member_count} Members | {$post_count} Posts</p>
			</div>
			<div class='clear'></div>
		</div>
		";
	}
	
	public static function displayNetwork($network)
	{
		$title = HTMLBuilder::formatNetworkTitle($network);
		
		//var_dump($network);
		echo "
		<div>
			<div class='net-info'>
				<a href='network/{$network->id}'><p class='bottom-text'>{$title}</p></a>
				<p class='network-stats'>{$network->member_count} Members | {$network->post_count} Posts</p>
			</div>
			<div class='clear'></div>
		</div>
		";
	}

	public static function displayPossibleNetwork($query)
	{
		if (is_array($query)) {

			$title = self::formatQueryTitle($query);
			echo "
			<div>
				<div class='net-info'>
					<form method='POST' action='search_launch_network.php'> 
						<p class='bottom-text'>{$title}</p>
						<input type='submit' class='launch-network' value='Launch Network'></input>
						<input type='hidden' name=type value='{$query[0]}'/>
						<input type='hidden' name=city_cur value='{$query[1]}'/>
						<input type='hidden' name=region_cur value='{$query[2]}'/>
						<input type='hidden' name=country_cur value='{$query[3]}'/>
						<input type='hidden' name=q_1 value='{$query[4]}'/>
						<input type='hidden' name=q_2 value='{$query[5]}'/>
						<input type='hidden' name=q_3 value='{$query[6]}'/>
					</form>
				</div>
				<div class='clear'></div>
			</div>
			";
		}
		else {
			$title = self::formatNetworkTitle($query);
			$q1 = null;
			$q2 = null;
			$q3 = null;

			// write the title
			if ( $query->network_class == '_l') {
				$q1 = $query->language_origin;
			}
			else {
				$q1 = $query->city_origin;
				$q2 = $query->region_origin;
				$q3 = $query->country_origin;
			}

			$html = <<<HTML
			<div>
				<div class='net-info'>
					<form method='POST' action='search_launch_network.php'> 
						<p class='bottom-text'>$title</p>
						<input type='submit' class='launch-network' value='Launch Network'></input>
						<input type='hidden' name=type value='$query->network_class'/>
						<input type='hidden' name=city_cur value='$query->city_cur'/>
						<input type='hidden' name=region_cur value='$query->region_cur'/>
						<input type='hidden' name=country_cur value='$query->country_cur'/>
						<input type='hidden' name=q_1 value='$q1'/>
						<input type='hidden' name=q_2 value='$q2'/>
						<input type='hidden' name=q_3 value='$q3'/>
					</form>
				</div>
				<div class='clear'></div>
			</div>

HTML;
			echo $html;
		}

		/*
		if (is_array($query))
		{
			// locations start at 1, have three addresses reserved (may be NULL)
			// rest is variable
			$query_length = count($query);
			$title = null;
			$location = null;
			$query_str = null;
			$location_start = 1;
			$location_end = 4;

			// get current location
			for ($i = $location_start; $i < $location_end; $i++)
			{
				if ($query[$i] != null)
				{ 
					$location .= $query[$i];
					if ( $location_end  - $i != 1)
					  { $location .= ", "; }
				}

			}

			// get origin
			if ($query[0] != "_l")
			{
				for ($i = $location_end; $i < count($query); $i++)
				{
					if ($query[$i] != null)
					{
						$query_str .= $query[$i];
						if (count($query) - $i != 1)
						  { $query_str .= ", "; }
					}
				}
			}

			switch ($query[0])
			{
			case "_l":
				$title = "{$query[4]} speakers in {$location}";
				break;
			case "co":
				$title = "From {$query_str} in {$location}";
				break;
			case "rc":
				$title = "From {$query_str} in {$location}";
				break;
			case "cc":
				$title = "From {$query_str} in {$location}";
				break;
			}

			echo "
			<div>
				<div class='net-info'>
					<form method='POST' action='search_launch_network.php'> 
						<p class='bottom-text'>{$title}</p>
						<input type='submit' class='launch-network' value='Launch Network'></input>
						<input type='hidden' name=type value='{$query[0]}'/>
						<input type='hidden' name=city_cur value='{$query[1]}'/>
						<input type='hidden' name=region_cur value='{$query[2]}'/>
						<input type='hidden' name=country_cur value='{$query[3]}'/>
						<input type='hidden' name=q_1 value='{$query[4]}'/>
						<input type='hidden' name=q_2 value='{$query[5]}'/>
						<input type='hidden' name=q_3 value='{$query[6]}'/>
					</form>
				</div>
				<div class='clear'></div>
			</div>
			";
		}
		else	// network_dt object
		{
			// locations start at 1, have three addresses reserved (may be NULL)
			// rest is variable
			$title = null;
			$location = '';
			$origin = '';
			$query_str = null;
			$q1 = null;
			$q2 = null;
			$q3 = null;

			// get location
			if (isset($query->city_cur))
				$location .= $query->city_cur.', ';
			if (isset($query->region_cur))
				$location .= $query->region_cur.', ';
			if (isset($query->country_cur))
				$location .= $query->country_cur;
			
			// get origin
			if (isset($query->city_origin))
				$origin .= $query->city_origin.', ';
			if (isset($query->region_origin))
				$origin .= $query->region_origin.', ';
			if (isset($query->country_origin))
				$origin .= $query->country_origin;

			// write the title
			switch ($query->network_class)
			{
			case "_l":
				$title = "$query->language_origin speakers in {$location}";
				$q1 = $query->language_origin;
				break;
			case "co":
				$title = "From {$origin} in {$location}";
				$q3 = $query->country_origin;
				break;
			case "rc":
				$title = "From {$origin} in {$location}";
				$q2 = $query->region_origin;
				$q3 = $query->country_origin;
				break;
			case "cc":
				$title = "From {$origin} in {$location}";
				$q1 = $query->city_origin;
				$q2 = $query->region_origin;
				$q3 = $query->country_origin;
				break;
			}
			$html = <<<HTML
			<div>
				<div class='net-info'>
					<form method='POST' action='search_launch_network.php'> 
						<p class='bottom-text'>$title</p>
						<input type='submit' class='launch-network' value='Launch Network'></input>
						<input type='hidden' name=type value='$query->network_class'/>
						<input type='hidden' name=city_cur value='$query->city_cur'/>
						<input type='hidden' name=region_cur value='$query->region_cur'/>
						<input type='hidden' name=country_cur value='$query->country_cur'/>
						<input type='hidden' name=q_1 value='$q1'/>
						<input type='hidden' name=q_2 value='$q2'/>
						<input type='hidden' name=q_3 value='$q3'/>
					</form>
				</div>
				<div class='clear'></div>
			</div>

HTML;
			echo $html;
		}
		 */
	}

	public static function displaySearchBar()
	{
		echo "
		<div id='search'>
			<form id ='search-form' method='GET' action='search_results.php' autocomplete='off'>
			<div id='opening' class='network'>Find people who
			<select id='verb-select' name='verb' class='netsearch'>
				<option value='arefrom'>are from</option>
				<option value='speak'>speak</option>
			</select>
			<span class='at network'>In/Near</span>
			</div>
			<input type='text' id='search-1' class='net-search' name='search-1' autocomplete='off'/>
				<ul id='s-query' class='network search'></ul>
				<ul id='s-var' class='network search'></ul>
				<input type='hidden' id='clik1' name='clik1' value=0></ul>
			<input type='text' id='search-2' class='net-search' name='search-2' autocomplete='off'/>
				<ul id='s-location' class='network search'></ul>
				<input type='hidden' id='clik2' name='clik2' value=0></ul>
			<input type='submit' class='network search-button' value='Go'>
			<input type='hidden' id='search-topic' name='search-topic'></input>
			</form>
		</div>
		";
	}

	public static function displayShareScript() {

		$html = <<<EHTML
<script>
	window.fbAsyncInit = function() {
		  FB.init({
		    appId      : '670914089652347',
		    status     : true,
		    xfbml      : true,
		    version    : 'v2.0'
		  });
  	}

	(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.0";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
</script>
<script src="https://apis.google.com/js/plusone.js"></script>
EHTML;

		return $html;
	}

	public static function displayShare($nid) {
		$html = <<<EHTML
<div id="share">
	<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://www.culturemesh.com/network.php?id=$nid" data-count="none">Tweet</a>
	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
	<div class="fb-like" data-href="http://www.culturemesh.com/network.php?id=$nid" data-layout="button" data-action="like" data-show-faces="true" data-share="true"></div>
	<g:plus action="share" data-href="www.culturemesh.com/network.php?id=$nid" data-annotation="bubble" data-width="200"></g:plus>
</div>
EHTML;

		return $html;
					
	}
	
	public static function displayLrgNetwork($network)
	{
		$title = HTMLBuilder::formatNetworkTitle($network);
		
		echo "
		<div>
			<div class='net-info'>
				<h1 class='h-network'>{$title}</h1>
				<p class='lrg-network-stats'>{$network->member_count} Members | {$network->post_count} Posts</p>
			</div>
			<div class='clear'></div>
		</div>
		";
	}
	
	// be careful, it's a fucking mess in here
	//  sorry, just trying to resolve nojs with js
	//  this is why templates are helpful
	public static function displayPost($post, $replies, $REPLY_MAX)
	{

		// jsonencode post
		$jpost = json_encode($post);
		// jsonencode user
		$juser = json_encode($user);

		// get image
		$img_link = NULL;
		if ($post->img_link == NULL)
			$img_link = BLANK_IMG;
		else
			$img_link = IMG_DIR.$post->img_link;

		// set reply count if null
		if ($post->reply_count == NULL)
			$post->reply_count = 0;

		// parse name
		$name = NULL;
		if ($post->first_name == '')
			//$name = $post->email;
			$name = "UNNAMED USER";
		else {
			$name = $post->first_name;
			if (isset($post->last_name))
				$name .= " ".$post->last_name;
		}

////////////////////////////////////////////

		if ($post->id_user == $_SESSION['uid']) {
			$del_button = <<<EHTML
<div class='reply-button delete'>
	<form id='post-delete-$post->id' class='personal post_delete' method="POST" action="network_post_delete.php">
		<noscript>
		<input type="hidden" name="NOJS" value="NOJS"/>
		</noscript>
		<input type="hidden" name="replies" value="$post->reply_count" />
		<input type="hidden" name="pid" value="$post->id"/>
		<input type="hidden" name="nid" value="$post->id_network"/>
		<button class='post delete-button user'>&#10006</button>
	</form>
</div>
EHTML;
		}

		if (isset($_SESSION['uid'])) {
			$uid = $_SESSION['uid'];
			$rr_toggle = <<<EHTML
<div class='reply-button'>
	<form class="request_reply" method="POST" action="network_request_reply.php">
		<noscript>
		<input type="hidden" name="NOJS" value="NOJS"/>
		</noscript>
		<input type="hidden" name="uid" value="$uid"/>
		<input type="hidden" name="pid" value="$post->id"/>
		<input type="hidden" name="nid" value="$post->id_network"/>
		<button class='post'>Reply</button>
	</form>
</div>
EHTML;
		}

		// GET Replies from get, NOJS
		$rids = urlencode($_GET['reply']);
		$rids_ray = explode('+', $rids);

		$nid = $post->id_network;
		$pid = $post->id;

		if (count($replies) <= $REPLY_MAX && !in_array($post->id, $rids_ray)) {
			$display = 'display:none';
		}
		
		$sr_toggle = <<<EHTML
<div class='show_reply_div reply-button'>
	<form id="" class="show_reply" method="POST" style="$display" action="network_show_reply.php">
		<input type="hidden" name="rids" value="$rids"/>
		<input type="hidden" name="pid" value="$pid"/>
		<input type="hidden" name="nid" value="$nid"/>
		<noscript>
		<input type="hidden" name="NOJS" value="NOJS"/>
		</noscript>
		<input type="submit" class="post show" value="Show All Replies"/>
	</form>
</div>
EHTML;

		$reply_ul = self::displayReplies($replies, NULL, NULL, $REPLY_MAX);

		if(isset($_SESSION['uid']) && $post->id == $_GET['pid']) {
			$reply_request = self::displayReplyPrompt($post->id, $_SESSION['uid'], $post->id_network, $REPLY_MAX);
		}

		// wiped post
		if ($post->post_text == NULL) {
			$img_link = BLANK_IMG;
			$post_info = <<<EHTML
<div class='post-img'>
	<img id='profile-post' src='$img_link' width='45' height='45'>
</div>
<div class='post-info'>
	<p class='network'><i>The user has deleted this post</i></p>
	<p class='network reply_count'>$post->reply_count replies</p>
	$rr_toggle
	$reply_request
	<div class="clear"></div>
</div>
EHTML;
		} 
		// regular post
		else {
			$post_info = <<<EHTML
<div class='post-img'>
	<img id='profile-post' src='$img_link' width='45' height='45'>
</div>
<div class='post-info'>
	<h5 class='h-network post'>$name</h5> 
	$del_button
	<div class="clear"></div>
	<p class='network'>$post->post_text</p>
	<p class='network date'><i>$post->post_date</i></p>
	$rr_toggle
	$reply_request
	<div class="clear"></div>
</div>
EHTML;
		}

/////////////////////////////////////////////
		$post_html = <<<EHTML
<li class='network-post' id='post-$post->id'>
	$post_info
	<div class='clear'></div>
	<div class="replies">
		<ul class="replies">
			$reply_ul
		</ul>
	</div>
	<div class="prompt"></div>
	$sr_toggle
</li>
EHTML;




///////////////////////////////
		return $post_html;
	}

	public static function displayReplyPrompt($pid, $uid, $nid, $root)
	{
////////////////////////////////////////
		$post = <<<EHTML
<form method="POST" class="member reply_form" action="$root/network_post_reply.php">
<!--<img id="profile-reply" src="<?php //echo $img_link; ?>" width="45" height="45">-->
	<textarea class="reply-text" name="reply_text" placeholder="Post reply..."></textarea>
	<div class="clear"></div>
	<input type="submit" class="post send" value="Send"></input>
	<input type="hidden" name="nid" value="$nid"/>
	<input type="hidden" name="uid" value="$uid"/>
	<input type="hidden" name="id_parent" value="$pid"/>
	<noscript>
		<input type="hidden" name="NOJS" value="NOJS"/>
	</noscript>
</form>
EHTML;
/////////////////////////////
		return $post;
	}

	public static function displayReplies($replies, $pid=NULL, $nid=NULL, $REPLY_MAX=99999) {

		$list = '';

		// construct each reply
		for($i = 0; $i < $REPLY_MAX && $i < count($replies); $i++) {
			$list .= HTMLBuilder::constructReply($replies[$i]); 
		}

		return $list;
	}

	public static function constructReply($reply) {
		// get image
		$img_link = NULL;
		if ($reply->img_link == NULL)
			$img_link = BLANK_IMG;
		else
			$img_link = IMG_DIR.$reply->img_link;

		// parse name
		$name = NULL;
		if ($reply->first_name == '')
			//$name = $reply->email;
			$name = "UNNAMED USER";
		else {
			$name = $reply->first_name;
			if (isset($reply->last_name))
				$name .= " ".$reply->last_name;
		}

		// stuffs
		if ($reply->id_user == $_SESSION['uid']) {
			$list.=$del_button;
			$del_button = <<<EHTML
			<div class='reply-button delete'>
				<form class='personal delete_reply' method="POST" action="network_reply_delete.php">
					<noscript>
					<input type="hidden" name="NOJS" value="NOJS"/>
					</noscript>
					<input type="hidden" name="rid" value="$reply->id"/>
					<input type="hidden" name="nid" value="$reply->id_network"/>
					<input type="hidden" name="pid" value="$reply->id_parent"/>
					<button class='post delete-button user reply'>&#10006</button>
				</form>
			</div>
EHTML;
		}

		$li_reply = <<<EHTML
<li class='reply'>
	<div class='post-img'>
		<img id='profile-post' src='$img_link' width='45' height='45'>
	</div>
	<div class='post-info'>
		<h5 class='h-network post'>$name</h5>
		$del_button
		<div class="clear"></div>
		<p class='network reply'>$reply->reply_text</p>
		<p class='network reply date'>$reply->reply_date</p>
	</div>
	<div class='clear'></div>
</li>
EHTML;
		return $li_reply;
	}
	
	public static function displayEvent($event)
	{
		$datetime = strtotime($event->event_date);
		$datetime = date("m/d/y g:i", $datetime);
		
		echo "
		<li class='event'>
			<div class=''>
				<h3 class='h-network'>{$event->title}</h3>
			</div>
			<div class=''>
				<p id='event-info'>Hosted by {$event->first_name} {$event->last_name} and set for {$datetime}</p>
				<p id='event-desc'>{$event->description}</p>
				
			</div>
		</li>
		";
	}

	public static function displayEventMonth($month, $year)
	{
		echo "
		<td class='event-card month'>
			<p>{$month}</p>
			<p>{$year}</p>
		</td>
		";
	}

	public static function displayEventCard($event)
	{
		// get image
		$img_link = NULL;
		if ($event->img_link == NULL)
			$img_link = BLANK_IMG;
		else
			$img_link = IMG_DIR.$event->img_link;

		// format name
		$name = NULL;
		if ($event->first_name == '')
			//$name = $event->email;
			$name = "UNNAMED USER";
		else {
			$name = $event->first_name;
			if (isset($event->last_name))
				$name .= " ".$event->last_name;
		}

		$datetime = strtotime($event->event_date);
		$cur_date = date("m/d/y");
		$datetime = date("m/d/y g:i", $datetime);

		if ($cur_date > $datetime) {
			$datetime = "Event is over.";
		}
		else {
			$datetime = self::formatDateTime($datetime);
		}
		
		//echo "
		$card = <<<EHTML
		<td class='event-card card'>
			<div>
				<h3 class='h-network'>$event->title</h3>
			</div>
			<div class='card-content'>
				<div class='card-img'>
					<img src='$img_link' alt='No image' width='65' height='65'></img>
				</div>
				<div class='card-info'>
					<p id='event-info'>With $name</p>
					<p id='event-date'>$datetime</p>
					<a data-toggle="modal" href="#event-modal-$event->id">More Info</a>
				</div>
			</div>
			<div class='clear'></div>
		</td>
EHTML;

		echo $card;
	}

//////////////////////
public static function displayEventModal($event) {

//////////////////////
// create join form
$join_form = null;
$attending_div = null;
$nid = $_GET['id'];
$uid = $_SESSION['uid'];

// date information
$cur_date = date("m/d/y");
$dt = strtotime($event->event_date);
$dt = date("m/d/y g:i", $dt);

$datetime = self::formatDateTime($dt);
// this is split into three parts
// right now, not exactly pretty,
// but I need it to make a part of the
// template conditional

$modal_1 = <<<EHTML
<div id="event-modal-$event->id" class="modal event hide fade" tabindex="-1" role="dialog" aria-labelledby="blogPostLabel" aria-hidden="true">
	<div class="modal-header event">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
	</div>
	<div class="modal-body event">
		<div id="event-modal-info-$event->id" class="modal-event info">
		    <form class="event-modal-form" method="POST" action="network_update-event.php" >
			<div class="event-modal-section">
			<h1 class="h-network nomargin info-$event->id">$event->title</h1>
				<input type="text" id="title" name="title" class="event-text-modal edit-$event->id" placeholder="Name of Event " value="$event->title"></input>
			</div>
			<div class="event-modal-section">
			<h3 class="h-network">With $event->first_name $event->last_name</h3>
			<p class="event-modal info-$event->id">$event->description</p>
				<textarea id="description" name="description" class="event-text-modal edit-$event->id" placeholder="What's happening?">$event->description</textarea>
			</div>
			<div class="event-modal-section">
			<h3 class="h-network">Date</h3>
				<input type="text" id="datetimepicker" class="event-text-modal edit-$event->id datetimepicker" name="datetime" class="event-text" placeholder="Event Date" value="$event->event_date">
				<input type="text" class="hidden-field" name="date"></input>
			<p class="event-modal info-$event->id">$datetime</p>
			</div>
			<div class="event-modal-section">
			<h3 class="h-network">Address</h3>
			<p class="event-modal info-$event->id">$event->address_1</p>
				<input type="text" name="address_1" class="event-text-modal edit-$event->id" placeholder="Address 1" value="$event->address_1"/>
			<p class="event-modal info-$event->id">$event->address_2</p>
				<input type="text" name="address_2" class="event-text-modal edit-$event->id" placeholder="Address 2" value="$event->address_2"/>
			<p class="event-modal info-$event->id">$event->city, $event->region</p>
				<input type="text" name="city" class="event-text-modal edit-$event->id" placeholder="City" value="$event->city"/>
				<input type="text" name="region" class="event-text-modal edit-$event->id" placeholder="Region" value="$event->region"/>
			</div>
			<div>
				<p id="ueerror-$event->id" class='event-error'></p>
				<p id="jeerror-$event->id" class='event-error'></p>
				<p id="leerror-$event->id" class='event-error'></p>
			</div>
				<input type="hidden" name="id_event" class="edit-$event->id" value="$event->id"/>
				<input type="submit" class="submit edit-$event->id" value="Submit Changes"></input>
		    </form>

		$modal_anchor
		<div id="join-event-form-$event->id">
		<form id="je-form-$event->id" method="POST" action="network_join-event.php">
			<input type="hidden" name="nid" value="$nid"/>
			<input type="hidden" value="$uid" name="uid"/>
			<input type="hidden" name="event_id" value="$event->id"/>
			<button class="event-modal">Join Event</button>
		</form>
		</div>
		<div id="attending_div-$event->id">
			<p>You're attending this event</p>
			<form id="je-form-$event->id" method='POST' action='network_leave-event.php'>
				<input type="hidden" name="uid" value="$uid"/>
				<input type="hidden" name="eid" value="$event->id"/>
				<button class="event-modal">Leave Event</button>
			</form>
		</div>
		</div>
EHTML;

$modal_anchor = NULL;
if ($_SESSION['uid'] == $event->id_host)
{
$modal_anchor= <<<EHTML
		<a id="event-modify-toggle-$event->id" class="">Edit Event</a> 
EHTML;
}

$modal_2 = <<<EHTML
	</div>
	<div class="modal-footer event">
	</div>
</div>
<script>
	// all the variables
	//var info$event->id = document.getElementById("event-modal-info-$event->id");
	var link$event->id = document.getElementById("event-modify-toggle-$event->id");
	if (link$event->id != null)
	{
		//var form$event->id = document.getElementById("event-modal-form-$event->id");
		//var editList$event->id = document.getElementsByClassName("");
		//var displayList$event->id = document.getElementsByClassName("");
		var info$event->id = document.getElementsByClassName("info-$event->id");
		var form$event->id = document.getElementsByClassName("edit-$event->id");

		link$event->id.onclick = function() {
			if (form$event->id[0].style.display == "none"
				|| form$event->id[0].style.display == "") { 
				link$event->id.innerHTML = "Cancel Changes";
				for (var i = 0; i < form$event->id.length; i++) {
					form$event->id[i].style.display = "block";
				}
				for (var i = 0; i < info$event->id.length; i++) {
					info$event->id[i].style.display = "none";
				}
			}
			else {
				link$event->id.innerHTML = "Edit Event";
				for (var i = 0; i < form$event->id.length; i++) {
					form$event->id[i].style.display = "none";
				}
				for (var i = 0; i < info$event->id.length; i++) {
					info$event->id[i].style.display="block";
				}
			}
		}
	}
</script>
EHTML;

// Check for attendance
$ad_display = null;
$jf_display = null;

if (($_SESSION['uid'] == $event->id_host)
	|| ($event->attending) || ($cur_date > $dt)) {
				////////////////////////
		$jf_display = <<<EHTML
			<style>
			#join-event-form-$event->id {
				display:none;
			}
			</style>
EHTML;
//////////////////////////////////////////
}  // endif

if (!$event->attending) {
		$ad_display = <<<EHTML
			<style>
			#attending_div-$event->id {
				display:none;
			}
			</style>
EHTML;
} // endif
///////////////////////////////////////////////////
	// DISPLAY THE STUFFFFF!!!!  
	echo $modal_1 . $modal_anchor . $modal_2 . $ad_display . $jf_display; 
} 
///////////////////////////////////////////////////
public static function googleMapsEmbed($location) {

	// google fix
	//
	// depends on location including country_name
	if ($location == 'Georgia')
		$location = 'Country Georgia';

////////////////////////////////////////////////
	$key = $GLOBALS['G_API_KEY'];
/////////////////////////////////////////////////
$template = <<<EHTML
<div class="map">
	<iframe
	   width="100%" height="252" 
	   frameborder="0" style="border:0"
	   src="https://www.google.com/maps/embed/v1/place?key=$key&q=$location">
	</iframe>
</div>
EHTML;
////////////////////////////////////////////////
	echo $template;
}

///////////////////////////////////////////////// echo $template; } 
	/**************** USER DASHBOARD STUFF 	********************/
	public static function displayDashPost($post, $u_data = false)
	{
		// add a user image class for ajax change
		$i_class = '';
		if ($u_data) 
		  { $i_class = 'usr_image'; }

		// get image
		$img_link = NULL;
		if ($post->img_link == NULL)
			$img_link = BLANK_IMG;
		else
			$img_link = IMG_DIR.$post->img_link;

		// parse name
		$name = NULL;
		if ($post->first_name == '')
			//$name = $post->email;
			$name = "UNNAMED USER";
		else {
			$name = $post->first_name;
			if (isset($post->last_name))
				$name .= " ".$post->last_name;
		}

		return "
		<li class='network-post dashboard'>
			<div class='post-img {$i_class}'>
				<img id='profile-post' src='{$img_link}' class='{$i_class}' width='45' height='45'>
			</div>
			<div class='post-info'>
				<h5 class='h-network'><a href='network.php?id={$post->id_network}&ap=true#post-{$post->id}'>{$name}</a></h5>
				<p class='network'>{$post->post_text}</p>
			</div>
			<div class='clear'></div>
		</li>
		";
	}
	
	public static function displayDashEvent($event, $u_data = false)
	{
		//$net_title = self::formatNetworkTitle($event);

		$host = NULL;

		if ($_SESSION['uid'] == $event->id_host)
			$host = 'YOU';
		else {
			// last resort, email
			if (isset($event->email))
				$host = $event->email;

			// make host username
			if (isset($event->username))
				$host = $event->username;

			// prioritize names
			if (isset($event->first_name)) {
				$host = $event->first_name;

				if (isset($event->last_name))
					$host .= ' '.$event->last_name;
			}
		}

		$i_class = '';
		if ($u_data) 
		  { $i_class = 'usr_image'; }

		// get image
		$img_link = NULL;
		if ($event->img_link == NULL)
			$img_link = BLANK_IMG;
		else
			$img_link = IMG_DIR.$event->img_link;

		$datetime = strtotime($event->event_date);
		$datetime = date("m/d/y g:i", $datetime);
		
		return "
		<li class='event dashboard'>
			<div class='event-host'>
				<img src='{$img_link}' class='{$i_class}' width='72' height='72'/>
			</div>
			<div class='event-text'>
				<div class='event-title'>
					<h3 class='h-network'><a href='network.php?id={$event->id_network}&elink={$event->id}'>{$event->title}</a></h3>
				</div>
				<div class='event-info'>
					<p id='event-info'>Hosted by {$host} and set for {$datetime}</p>
					<p id='event-desc'>{$event->description}</p>
					
				</div>
			</div>
			<div class='clear'></div>
		</li>
		";
	}
	
	public static function displayDashNetwork($network)
	{
		$title = HTMLBuilder::formatNetworkTitle($network);
		$date = HTMLBuilder::formatDate($network->join_date);

		return "
		<div class='net-info dashboard'>
			<a href='network/{$network->id}'><p class='bottom-text dashboard'>{$title}</p></a>
			<p class='network-stats'>{$network->member_count} Members | {$network->post_count} Posts</p>
			<p class='network-stats'>Joined {$date}</p>
		</div>
		";
	}

	public static function displayDashNetworkTitle($network)
	{
		$title = HTMLBuilder::formatNetworkTitle($network);

		return "<p>{$title}</p>";
	}
	
	public static function displayDashConversation($conversation)
	{
		$email = "";
		// check to see which email object should be used, user 1 or user 2
		if ($conversation->id_user2_dt == null)
			$email = $email.$conversation->id_user1_dt->email;
		else
			$email = $email.$conversation->id_user2_dt->email;

		// get image
		$img_link = NULL;
		if ($post->img_link == NULL)
			$img_link = BLANK_IMG;
		else
			$img_link = IMG_DIR.$post->img_link;
			
		echo "
		<div class='user-img dashboard'>
			<img src='images/blank_profile.png' width='72' height='72'/>
		</div>
		<div class='user-info dashboard'>
			<h3 class='h-network'>{$email}</h3>
			<p class='bottom-text dashboard'>{$conversation->start_date}</p>
		</div>
		<div class='clear'></div>
		";
	}

	
	public static function formatQueryTitle($query) {
		// initialize location
		$location = 'in/near ';

		for ($i = 1; $i < 4; $i++) {
			// add query items
			if($query[$i] != NULL) {
				$location .= $query[$i];

				// if we aren't on the last thing
				// add comma
				if($i < 3) {
					$location .= ', ';
				}
			}
		}

		// initialize query string
		$qstring = '';

		// if it's a language, simply add
		if ($query[0] == '_l') {
			$qstring .= 'People who speak '. $query[4];
		}

		// if it's a location....
		if ($query[0] == 'cc' ||
			$query[0] == 'rc' ||
			$query[0] == 'co')
		{	
			$qstring .= 'People from ';
			for ($i = 4; $i < 7; $i++) {
				// add query items
				if($query[$i] != NULL) {
					$qstring .= $query[$i];

					// if we aren't on the last thing
					// add comma
					if($i < 6)
						$qstring .= ', ';
				}
			}
		}

		// return string
		return $qstring . ' ' . $location;
	}

	// formats network title
	// 	+ if current country and origin country
	// 	+ are the same, it doesn't include country
	public static function formatNetworkTitle($network)
	{

		$curloc_arr = array();
		$origin_arr = array();

		$cur_keys = array('city_cur', 'region_cur', 'country_cur');
		$origin_keys = array('city_origin', 'region_origin', 'country_origin', 'language_origin');

		// fill current location array
		foreach ($cur_keys as $key) {
			if ($network->$key != null && $network->$key != '') {
				array_push($curloc_arr, $network->$key);
			}
		}

		// fill origin array
		foreach ($origin_keys as $key ) {
			if ($network->$key != null && $network->$key != '') {
				array_push($origin_arr, $network->$key);
			}
		}


		$title = '';
		$origin = self::arrayToNetworkValue($origin_arr);
		$location = self::arrayToNetworkValue($curloc_arr);
		
		if ($network->network_class == '_l') 
			$title = "{$origin} speakers in {$location}";
		else {
			$title = "From {$origin} in {$location}";
		}

		return $title;

		/*
	
		$exceptions = array('Washington, D.C.');

		if ($network->city_cur != null)
			$location .= $network->city_cur . ", ";
		if ($network->region_cur != null)
			$location .= $network->region_cur;


		if($network->country_cur != $network->country_origin && $network->network_class != 'co') {
			// check to see if there's anything before
			if ($location == '') {
				$location .= $network->country_cur;
			}
			else {
				$location .= ", " . $network->country_cur;
			}
		}
		

		switch($network->network_class)
		{
		case '_l':	// LANGUAGE
			$title = "{$network->language_origin} speakers in {$location}";
			break;
		case 'cc':	// CITY,REGION
			if ($network->country_origin == $network->country_cur)
			{ 
				$title .= "From {$network->city_origin}";
				if ($network->region_origin != null)
					$title .= ", {$network->region_origin} near {$location}"; 
			}

			else if ($network->region_origin == null) {

				$title = "From {$network->city_origin}, {$network->country_origin} in {$location}";
			}
			else
			{
				$title = "From {$network->city_origin}, {$network->region_origin}, {$network->country_origin} in {$location}";
			}
			break;
		case 'rc':	// REGION
			if ($network->id_country_origin == $network->id_country_cur)
				$title = "From {$network->region_origin} in {$location}";
			else
				$title = "From {$network->region_origin}, {$network->country_origin} in {$location}";
			break;
		case 'co':	// COUNTRY
			$title = "From {$network->country_origin} in {$location}";
			break;
		}


		 */
		
	}

	// takes an array, returns stringed out location
	private static function arrayToNetworkValue($arr) {
		$value = '';
		for ($i = 0; $i < count($arr); $i++) {

			// add thing
			$value .= $arr[$i];
			// if we're not on the last item, add comma
			if ($i+1 != count($arr))
				$value .= ', ';
		}

		return $value;
	}

	private static function formatDateTime($date)
	{
		$months = array("NOTHING", "January", "February", "March", "April",
			"May", "June", "July", "August", "September",
			"October", "November", "December");

		// turn date into assoc array
		$p_date = date_parse($date);

		// assign month
		$month = $months[$p_date['month']];

		// handle hours
		$hour = $p_date['hour'];
		$period = "AM";
		if ($hour > 12) {
			$hour -= 12;
			$period = "PM";
		}

		// check minute
		$minute = $p_date['minute'];
		if ($minute < 10)
			$minute = '0'.$minute;

		// format and return string
		$f_datetime = $month." ".$p_date['day'].", ".$p_date['year']." @ ".$hour.":".$minute." ".$period;
		return $f_datetime;
	}

	private static function formatDate($date) {
		$months = array("NOTHING", "January", "February", "March", "April",
			"May", "June", "July", "August", "September",
			"October", "November", "December");

		// turn date into assoc array
		$p_date = date_parse($date);

		// assign month
		$month = $months[$p_date['month']];

		// format and return string
		$f_datetime = $month." ".$p_date['day'].", ".$p_date['year'];
		return $f_datetime;
	}
}
?>
