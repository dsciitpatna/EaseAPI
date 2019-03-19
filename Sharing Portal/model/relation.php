<?php

include_once("user.php");

/**
 * 
 */
class Relation //extends AnotherClass
{
	/**
	  *	User object of logged In user
	  * @var int
	  */
	private $loggedInUser;

	/**
	  *	Array of User Ids of followers of the logged in user
	  * @var int
	  */
	private $followers;

	/**
	  *	Array of User Ids of the users followed by the logged in user 
	  * @var int
	  */
	private $following;

	/**
  	  * MySQL object - $conn - variable containing conection details
 	  * @var MySQLi	Object
  	  */
	private $conn;


	/** ==============================================
				ACCESSORS AND MODIFIERS
	==============================================*/

	function change_loggedInUser($id)
	{
		$user = new User($this->conn);
		$user->arrayToUser($user->getUser($id));
		$this->loggedInUser = $user;
		$this->followers = $this->getAllFollowers();
		$this->following = $this->getAllFollowing();
	}

	/** ==============================================
			CONSTRUCTORS AND DESTRUCTORS
	==============================================*/

	function __construct($conn, User $loggedInUser)
	{
		$this->loggedInUser = $loggedInUser;
		$this->conn = $conn;
		$this->followers = null;
		$this->following = null;
	}

	/** ==============================================
					METHODS
	==============================================*/

	/**
	  *	gets array of all users following the loggedin user
	  * @return array of User object
	  */
	function getAllFollowersOfLoggedIn()
	{
		$sql = "SELECT user2 FROM relation WHERE user1 = '" . $this->loggedInUser->getUid() . "'";

		$result = $this->conn->query($sql);
		if (!$result || $result->num_rows <= 0) {
			$errorH = alog("getuser error: numrows : " . $result->num_rows . "error:" . $this->conn->error);
			$error = "Error in displaying result for given User ID. Err no: #" . $errorH;
			error_log("Error: " . $errorH . ": " . $result->num_rows . " - " . $this->conn->error);
			$status = 501;
			$msg = $error;
		} else {
			$res = array();
			while ($user_ids = $result->fetch_assoc()) {
				$status = 200;
				$user = new User($this->conn);
				$user->getUser($user_ids['user2']);

				$res[] = $user;
			}
			$result->free();
		}

		$ret = array();
		$ret['status'] = $status;
		if ($status == 200) {
			$ret['result'] = $res;
		} else {
			$ret['message'] = $msg;
		}
		return $ret;
	}

	/**
	  *	gets array of all users followed by the loggedin user
	  * @return array of User object
	  */
	function getAllUsersFollowedByLoggedIn()
	{
		$sql = "SELECT user1 FROM relation WHERE user2 = '" . $this->loggedInUser->getUid() . "'";

		$result = $this->conn->query($sql);
		if (!$result || $result->num_rows <= 0) {
			$errorH = alog("getuser error: numrows : " . $result->num_rows . "error:" . $this->conn->error);
			$error = "Error in displaying result for given User ID. Err no: #" . $errorH;
			error_log("Error: " . $errorH . ": " . $result->num_rows . " - " . $this->conn->error);
			$status = 501;
			$msg = $error;
		} else {
			$res = array();
			while ($user_ids = $result->fetch_assoc()) {
				$status = 200;
				$user = new User($this->conn);
				$user->getUser($user_ids['user1']);

				$res[] = $user;
			}
			$result->free();
		}

		$ret = array();
		$ret['status'] = $status;
		if ($status == 200) {
			$ret['result'] = $res;
		} else {
			$ret['message'] = $msg;
		}
		return $ret;
	}

	/**
	  *	check if the user is being followed by loggedin User (Logged in users starts following this user (User $user))
	  * @param User $user : User who is to be followed by Logged in user
	  * @return -1 : process failed (Error)
	  * 		 1 : Logged In user is a follower of $user
	  *		 	 0 : Logged In user is not follower of $user
	  */
	function checkFollowerOf(User $user)
	{
		$strId = (string)$this->loggedInUser->getUid() . "_" . (string)$user->getUid();
		$sql = "SELECT * FROM relation WHERE str_id ='" . $strId . "'";
		$result = $this->conn->query($sql);
		if ($result == false) {
			return -1;
		} else if ($result->num_rows == 0) {
			return 0;
		}
		return 1;
	}

	/**
	  *	adds a user to 'following' list of loggedin User (Logged in users starts following this user (User $user))
	  * @param User $user : User who is to be followed by Logged in user
	  * @return ['status'] 	: 200 : Relationship established (added to database)
	  *					   	: 400 : Invalid Credentials/Already Follower of $user
	  *					   	: 501 : Server Error
	  * 		['message'] : Message corresponding the status code 
	  */
	function addFollowerOf(User $user)
	{ }

	/**
	  *	removes the user from 'following' list of loggedin User (Logged in users stops following this user (User $user))
	  * @param User $user : User who is to be followed by Logged in user
	  * @return ['status'] 	: 200 : Relationship established (added to database)
	  *					   	: 400 : Invalid Credentials/Already Follower of $user
	  *					   	: 501 : Server Error
	  * 		['message'] : Message corresponding the status code 
	  */
	function removeFollowerOf(User $user)
	{ }

	/**
	  *	check if the user is being followed by loggedin User (Logged in users starts following this user (User $user))
	  * @param User $user : User who is to be followed by Logged in user
	  * @return -1 : process failed (Error)
	  * 		 1 : Logged In user followed by $user
	  *		 	 0 : Logged In user is not followed by $user
	  */
	function checkFollowedBy(User $user)
	{ }
}
