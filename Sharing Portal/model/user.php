<?php

/**
 * 
 */
class User //extends AnotherClass
{
	/**
	  *	User Id
	  * @var int
	  */
	private $uid;

	/**
	  *	Name of user
	  * @var string
	  */
	private $name;

	/**
	  *	Email Id of user
	  * @var string
	  */
	private $email;

	/**
	  *	Username of the user to be displayed on-screen
	  * @var string
	  */
	private $username;

	/**
	  *	Access Level of user
	  * 0 - User not verified
	  * 1 - User verified
	  * 2 - Admin level access 
	  * @var int
	  */
	private $access_level;

	/**
  	  * MySQL object - $conn - variable containing conection details
 	  * @var MySQLi	Object
  	  */	
	private $conn;
	

/** ==============================================
				ACCESSORS AND MODIFIERS
	==============================================*/

	/**
	  * All these modifiers set the private variables of the User object after validation check
	  * @param value needed to be set
	  * @return 0 : invalid parameter
	  * 		1 : Valid parameter and value set 
	  */

	function setUid($uid)
	{
		$this->uid = (int)$uid;
	}

	function setName($name)
	{
		if(preg_match('/^[a-zA-Z0-9.\s]*$/', $name)){
			$this->name = $name;
			return 1;
		} else {
			return 0;
		}
	}

	function setEmail($email)
	{
		if(filter_var($email, FILTER_VALIDATE_EMAIL)){
			$this->email = $email;
			return 1;
		} else {
			return 0;
		}
	}

	function setUsername($username)
	{
		if(preg_match('/^[a-zA-Z0-9.\s]*$/', $username)){
			$this->username = $username;
			return 1;
		} else {
			return 0;
		}
	}

	function setAccessLevel($access_level)
	{
		if( in_array((int)$access_level, array(0,1,2)) ){
			$this->access_level = (int)$access_level;
			return 1;
		} else {
			return 0;
		}
	}

	function getUid()
	{
		return $this->uid;
	}

	function getName()
	{
		return $this->name;
	}	

	function getUsername()
	{
		return $this->username;
	}	

	/**
	  * This returns the access level of the User object
	  *	@return Access Level of the user
	  */
	function getAccessLevel(){
		return $this->access_level;
	}

/** ==============================================
			CONSTRUCTORS AND DESTRUCTORS
	==============================================*/

	function __construct($conn)
	{
		$this->uid = null;
		$this->name = null;
		$this->email = null;
		$this->username = null;
		$this->access_level = null;
		$this->conn = $conn;		
	}

/** ==============================================
					METHODS
	==============================================*/

	/**
	  *	Checks if any detail/variable is left undefined
	  * @return 0 : error - If atleast one variable left undefined
	  *			1 : Success - All variables checked and defined
	  */
	function validateDetails()
	{
		if ($this->uid==null || $this->name==null || $this->email==null || $this->username==null || $this->access_level==null) {
			return 0;
		}

		return 1;
	}

	/**
	  *	register user to the database
	  * @param $password - string - password given by the user
	  * @return array - array of details about the user
	  * 		['status'] : 200 : successfully added to database
	  *					   : 400 : Invalid credentials
	  *					   : 401 : Email Id already used
 	  *					   : 501 : Server Error
	  * 		['message'] : Message corresponding the status code 
	  */
	function registerUser($password)
	{
		if($this->validateDetails()) {
			$sql = "INSERT INTO `users` "
            	. "(`name`, `email`, `password`, `username`, `access_level`) "
            	. "VALUES "
            	. "( '" .$this->name."', '".$this->email."', '".sha1($password)."', '".$this->username."', ".$this->access_level. " )";

        	$resultObj = $dbCon->query($sql);
    
    		$ret = array();
    		if ($dbCon->affected_rows > 0) {
    		 	$status = 200;
    		 	$msg =  "Saved to DB. Sending Verification"; 
    		} elseif ($dbCon->affected_rows==-1) {
     			if ($dbCon->errno==1062) {
        			$status = 401;
        			$msg = "Duplicate entry! Email Id already in use...";
      			} else {
        			$status = 501;
        			$msg = "Server error! Couldnot fetch result!";
      			}
    		} else {
     	 		$status = 501;
      			$msg = "Server error! Couldnot fetch result!";
   		 	}
   		} else {
   			$msg = "Invalid credentials!!";
   			$status = 400;
   		}
    
    	$ret['status'] = $status;
    	$ret['message'] = $msg;
    	//$ret['sql'] = $sql;
    	return $ret;
	}	

	/**
	  *	gets user details of user with ID = $id
	  * @param $id - int - User Id of user
	  * @return array - array of details about the user
	  * 		['status'] : 200 : user details found
	  *					   : 400 : Invalid Id
	  *					   : 501 : Server Error
	  * 		['message'] : Message corresponding the status code 
	  * 		['result'] : ['uid, 'name', 'email', 'password', 'username', 'access_level'] 
	  */
	function getUser($id)
	{
		$id = (int)$id;

		$sql = "SELECT * FROM users WHERE uid = ".$id;
       	$result = $this->conn->query($sql);
        if(!$result || $result->num_rows!=1){
            $errorH = alog("getuser error: numrows : ". $result->num_rows ."error:". $this->conn->error);
            $error = "Error in displaying result for given User ID. Err no: #".$errorH;
            error_log("Error: ".$errorH .": ". $result->num_rows ." - ". $this->conn->error);
            $status = 501;
            $msg = $error;
        } else {
        	$user_details = $result->fetch_assoc();
        	if(isset($user_details)) {
        		$status = 200;
        		$res = $user_details;
        		$this->arrayToUser($res);
        	} else {
        		$status = 400;
        		$msg = "Invalid Id";
        	}
        	$result->free();
        }

        $ret = array();
        $ret['status'] = $status;
        if($status==200){
        	$ret['result'] = $res;
        } else {
        	$ret['message'] = $msg;
        }
        return $ret;
    }

    /**
	  *	converts given array object to User object (Sets corrsoponding values to current User Object)
	  * @param $arr - array - containing user info
	  */
    function arrayToUser($arr)
    {
    	if (!empty($arr)) {
      		isset($arr['uid']) ? $this->setUid($arr['uid']) : '';
      		isset($arr['name']) ? $this->setName($arr['name']) : '';
      		isset($arr['email']) ? $this->setEmail($arr['email']) : '';
      		isset($arr['username']) ? $this->setUsername($arr['username']) : '';
      		isset($arr['access_level']) ? $this->setAccessLevel($arr['access_level']) : $this->setAccessLevel(0);   
    	}
    }

    /**
	  *	converts current User object to array (Sets corrsoponding values to array)
	  * @return $arr - array - containing user info
	  */
    function userToArray() 
    {
    	$arr = array();
    	if ($this->uid!=null) {
    		$arr['uid'] = $this->uid;
    	}
    	if ($this->name!=null) {
    		$arr['name'] = $this->name;
    	}
    	if ($this->email!=null) {
    		$arr['email'] = $this->email;
    	}
    	if ($this->username!=null) {
    		$arr['username'] = $this->username;
    	}
    	if ($this->access_level!=null) {
    		$arr['access_level'] = $this->access_level;
    	}

    	return $arr;
    }

    /**
	  *	Checks if the username enterd is already in use
	  * @param $username - string - Username to be used/checked
	  * @return int : -1 : error
	  * 			:  0 : Username already in use
	  *				:  1 : Username not in use => can be used
	  */
    function checkUsername($username)
    {
    	$sql = "SELECT username FROM users";
       	$result = $this->conn->query($sql);
        if(!$result || $result->num_rows<=0){
            $errorH = alog("getuser error: numrows : ". $result->num_rows ."error:". $this->conn->error);
            $error = "Error in displaying result for given User ID. Err no: #".$errorH;
            error_log("Error: ".$errorH .": ". $result->num_rows ." - ". $this->conn->error);
            return -1;
        } else {
        	while($users = $result->fetch_assoc()){
        		if($users['username']==$username){
        			return 0;
        		}
        	}
        	$result->free();
        	return 1;
        }
    }

    /**
	  *	Checks if parameter is username or email
	  * @param $in - string - Username or email input to be checked
	  * @return int : -1 : none of these
	  * 			:  0 : Email
	  *				:  1 : Username
	  */
    private function MailOrUsername($in)
    {
    	if($this->setEmail($in)) {
    		return 0;
    	} 
    	if($this->setUsername($in)) {
    		return 1;
    	}

    	return -1;
    }

    function loginUser($inp, $password) 
    {
    	$status = 0;
    	$res = null;
    	$msg = "";
    	$check = $this->MailOrUsername($inp);
    	if ($check==0) {
    		$sql = "SELECT * FROM users WHERE email='". $this->email. "'";
    	} elseif ($check==1) {
    		$sql = "SELECT * FROM users WHERE username='". $this->username. "'";
    	} else {
    		$status = 400;
    		$msg = "email/username invalid!";
    	}
    	
    	if ($status!=400) {
       		$result = ($this->conn)->query($sql);
       		if(!$result || $result->num_rows<=0){
        	    $errorH = alog("getuser error: numrows : ". $result->num_rows ."error:". $this->conn->error);
        	    $error = "Error in displaying result for given User ID. Err no: #".$errorH;
        	    error_log("Error: ". $errorH .": ". $result->num_rows ." - ". $this->conn->error);
        	    $msg = $error;
        	} else {
        		$user_details = $result->fetch_assoc();
        		if (!isset($user_details)) {
        			$status = 501;
        			$msg = "Could not fetch result!!";
        		} else {
        			if($user_details['password']==sha1($password)){
        				$status = 200;
        				$res = $user_details;
        			} else {
        				$status = 400;
        				$msg = "Password or username/email incorrect";
        			}
        		}
        		$result->free();
        	}
    	} 

    	$ret = array();
    	$ret['status'] = $status;
    	if ($status==200) {
    		$ret['result'] = $res;
    		$this->arrayToUser($res);
    	} else {
    		$ret['message'] = $msg;
    	}

    	return $ret;
    }
}
