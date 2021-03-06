<?php

/**
  * @operations - 
  * 	CREATE
  *         createNetRegistration
  *	READ
  *	    getAllNetRegistrations
  *	UPDATE
  *	    updateNetRegistration
  *	DELETE
  *         deleteNetRegistration
  *         deleteRegistrationsByUser
  *         deleteRegistrationsByNetwork
**/ 

include_once("zz341/fxn.php");
include_once("dal_network_registration-dt.php");
include_once("data/dal_query_handler.php");

class NetworkRegistration
{
	////////////////////// CREATE OPERATIONS ////////////////////////////////////////
	public static function createNetRegistration($netreg_dt)
	{
		if (func_num_args() == 2)
		{ $con = func_get_arg(1); }
		else
		{ $con = getDBConnection();}
		//$con = func_get_arg(1);
		
		if (mysqli_connect_errno())
		{
			echo "Failed to connect to MySQL: ";
		}
		
		if (!mysqli_query($con, "INSERT INTO network_registration
			(id_user, id_network, join_date) 
			VALUES (". $netreg_dt->id_user . ", ". $netreg_dt->id_network . ", NOW())"))
		{
			echo "Error message: " . $con->error;	
		}
		
		if (func_num_args() < 2)
		{ mysqli_close($con); }
	}
	
	////////////////////// READ OPERATIONS //////////////////////////////////////////////
	public static function getAllNetRegistrations()
	{
		if (func_num_args() == 1)
		{ $con = func_get_arg(0); }
		else
		{ $con = getDBConnection();}
		
		// Check connection
		if (mysqli_connect_errno())
		{
		  	  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		
		$result = mysqli_query($con,"SELECT * FROM network_registration");
		
		if (func_num_args() < 1)
			mysqli_close($con);
		
		return $result;
		
		/*
		while($row = mysqli_fetch_array($result))
		{
		  	  echo $row['id_user'] . " " . $row['id_network'] . " " . $row['join_date'];
		  	  echo "<br>";
		}
		*/
		
		//mysqli_close($con);
	}
	
	public static function getNetworksByUserId($id)
	{
		if (func_num_args() == 2)
		{ $con = func_get_arg(1); }
		else
		{ $con = getDBConnection();}
		
		// Check connection
		if (mysqli_connect_errno())
		{
		  	  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		
		"";
		$result = mysqli_query($con,
			"SELECT n.*, mc.member_count, pc.post_count, nr.join_date
			FROM networks n
			JOIN ( SELECT id_network, join_date 
				   FROM network_registration
				   WHERE id_user ={$id}) nr
			ON nr.id_network = n.id
			LEFT JOIN ( SELECT id_network, COUNT(id_network) AS member_count
				   FROM network_registration
				   GROUP BY id_network
				   ORDER BY member_count) mc
			ON mc.id_network=nr.id_network AND mc.id_network=n.id
			LEFT JOIN (SELECT id_network, COUNT(id_network) AS post_count
				  FROM posts
				  GROUP BY id_network
				  ORDER BY post_count) pc
				  ON pc.id_network=nr.id_network 
				  AND pc.id_network=mc.id_network 
				  AND pc.id_network=n.id");
		
		if (func_num_args() < 2)
			mysqli_close($con);
		
		$networks = array();
		
		while ($row = mysqli_fetch_array($result))
		{
			$network_dt = new NetworkDT();
			$network_dt->id = $row['id'];
			$network_dt->city_cur = $row['city_cur'];
			$network_dt->region_cur = $row['region_cur'];
			$network_dt->country_cur = $row['country_cur'];
			$network_dt->city_origin = $row['city_origin'];
			$network_dt->region_origin = $row['region_origin'];
			$network_dt->country_origin = $row['country_origin'];
			$network_dt->language_origin = $row['language_origin'];
			$network_dt->network_class = $row['network_class'];
			$network_dt->member_count = $row['member_count'];
			$network_dt->post_count = $row['post_count'];
			$network_dt->join_date = $row['join_date'];
			
			array_push($networks, $network_dt);
		}
		
		return $networks;
	}
	
	public static function getMemberCount($id)
	{
		if (func_num_args() == 2)
		{ $con = func_get_arg(1); }
		else
		{ $con = getDBConnection();}
		
		// Check connection
		if (mysqli_connect_errno())
		{
		  	  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		
		$result = mysqli_query($con,"SELECT COUNT(id_network) as member_count FROM network_registration WHERE id_network={$id}");
		
		while ($row = mysqli_fetch_array($result))
			$count = $row['member_count'];
		
		if (func_num_args() < 2)
			mysqli_close($con);
		
		return $count;
	}
	
	public static function checkRegistration($netreg_dt)
	{
		if (func_num_args() == 2)
		{ $con = func_get_arg(1); }
		else
		{ $con = getDBConnection();}
		
		// Check connection
		if (mysqli_connect_errno())
		{
		  	  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		
		$result = mysqli_query($con,"SELECT COUNT(id_user) AS user_count FROM network_registration WHERE id_user={$netreg_dt->id_user} AND id_network={$netreg_dt->id_network}");
		
		if (func_num_args() < 2)
			mysqli_close($con);
		
		$row = mysqli_fetch_array($result);
		
		if ($row['user_count'] == 0) 
		{return false;}
		else
		{return true;}
	}
	////////////////////// UPDATE OPERATIONS /////////////////////
	public static function updateNetRegistration($netreg_dt)
	{
		if (func_num_args() == 2)
		{ $con = func_get_arg(1); }
		else
		{ $con = getDBConnection();}
		//$con = func_get_arg(1);
		
		if (mysqli_connect_errno())
		{
			echo "Failed to connect to MySQL: ";
		}
		
		mysqli_query($con, "UPDATE network_registration
			SET id_user=". $netreg_dt->id_user .", id_network=". $netreg_dt->id_network .
			", join_date=". $netreg_dt->join_date . 
			" WHERE id_user=". $netreg_dt->id_user ." AND id_network=". $netreg_dt->id_network);
		
		if (func_num_args() < 2)
		{ mysqli_close($con); }
	}
	
	////////////////////// DELETE OPERATIONS /////////////////////
	public static function deleteNetRegistration($uid, $nid, $con=NULL)
	{
		$query = <<<SQL
			DELETE FROM network_registration 
			WHERE id_user=$uid 
			AND id_network=$nid
SQL;

		return QueryHandler::executeQuery($query, $con);
	}
	
	public static function deleteRegistrationByUser($id)
	{
		if (func_num_args() == 2)
		{ $con = func_get_arg(1); }
		else
		{ $con = getDBConnection();}
		//$con = func_get_arg(1);
		
		if (mysqli_connect_errno())
		{
			echo "Failed to connect to MySQL: ";
		}
		
		mysqli_query($con, "DELETE FROM network_registration 
			WHERE id_user=". $id);
		
		if (func_num_args() < 2)
		{ mysqli_close($con); }
	}
	
	public static function deleteRegistrationsByNetwork($id)
	{
		if (func_num_args() == 2)
		{ $con = func_get_arg(1); }
		else
		{ $con = getDBConnection();}
		//$con = func_get_arg(1);
		
		if (mysqli_connect_errno())
		{
			echo "Failed to connect to MySQL: ";
		}
		
		mysqli_query($con, "DELETE FROM network_registration 
			WHERE id_network=". $id);
		
		if (func_num_args() < 2)
		{ mysqli_close($con); }
	}
}
?>
