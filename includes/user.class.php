<?php

class User {
    private $userID;
    private $userIP;
    
    public function __construct($userID) {
        $this->userIP = getUserIP();
        if (isset($userID)) {
            $this->userID = $userID;
        }
    }
    
    public function setUserID($userID){
        if (isset($userID)) {
            $this->userID = $userID;
        }
    }
    
    /**
     * Returns the full name of the user
     *
     * @param   bool    (optional) whether or not to format it and return as a string e.g. "John Doe"
     * @return  array   returns 2 keys in an array, 'first_name' and 'last_name'
     * @access  public
     */
    public function getUserRealName($formatted = false) {
	    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        try {
            $data = $db->prepare('select first_name, last_name from users WHERE staff_id=:userid');
            $data->bindParam(':userid', $this->userID);
            $data->execute();
            $result = $data->fetch(PDO::FETCH_ASSOC);
            $db = null;
            
            
            if ($formatted) {
                return $result['first_name']." ".$result['last_name'];
            } else {
                return $result;
            }
            
        } catch(PDOException $db) {
            echo 'ERROR: ' . $db->getMessage();
        }
    }
    
    public function getUserID() { return $this->userID; }    
    
    
    /**
     * Verifies if the user is working according to what is set in the users table.
     *
     * @return int  returns 1 or 0 if the user is clocked in (working) or clocked out (not working)
     * @access public
     */
    public function isWorking() {
        
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        try {
            $data = $db->prepare('SELECT is_working FROM users WHERE staff_id=:userid');
            $data->bindParam(':userid', $this->userID);
            $data->execute();
            $result = $data->fetch(PDO::FETCH_ASSOC);
            $db = null;
            return $result['is_working'];
            
        } catch(PDOException $db) {
            //echo $errorMsg;
            echo 'ERROR: ' . $db->getMessage();
        }
    }
    
    /**
     * Returns whether the user is an Administrator or not.
     *
     * @return int  returns 1 or 0 if the user is admin.
     * @access public
     */
    public function isAdmin() {
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        try {
            $data = $db->prepare('SELECT is_admin FROM users WHERE staff_id=:userid');
            $data->bindParam(':userid', $this->userID);
            $data->execute();
            $result = $data->fetch(PDO::FETCH_ASSOC);
            $db = null;
            return $result['is_admin'];
            
        } catch(PDOException $db) {
            //echo $errorMsg;
            echo 'ERROR: ' . $db->getMessage();
        }
    }
    
    
    /**
     * Returns whether the user is Freelance or not
     *
     * @return bool  returns true or false if the user is freelance.
     * @access public
     */
    public function isFreelance() {
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        try {
            $data = $db->prepare('SELECT is_freelance FROM users WHERE staff_id=:userid');
            $data->bindParam(':userid', $this->userID);
            $data->execute();
            $result = $data->fetch(PDO::FETCH_ASSOC);
            $db = null;
            
            if ($result['is_freelance']) {
                return true;
            } else {
                return false;
            }
            
        } catch(PDOException $db) {
            //echo $errorMsg;
            echo 'ERROR: ' . $db->getMessage();
        }
    }
    

    /**
     * Provides last timestamp(s) for the user.
     *
     * @param  int   $num   (optional) number of punches to return
     * @return string|array returns the last time punch if no parameter is passed and timestamp, previous timestamp, and hours worked as an array was passed.
     * @access public
     */
    public function lastPunch($num = "") {
        
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        try {
            
            if ($num == "") {
                $data = $db->prepare('SELECT MAX(timestamp) AS last_punch FROM punches WHERE user_id=:userid');
                
                $data->bindParam(':userid', $this->userID);
                $data->execute();
                $result = $data->fetch(PDO::FETCH_ASSOC);
                
                return $result['last_punch'];
                
            } else {
            
            	$limitby = intval($num);
                $data = $db->prepare('SELECT timestamp, previous_punch AS previous_timestamp, hours_worked from punches WHERE user_id=:userid AND in_out=0 AND hours_worked IS NOT NULL ORDER BY timestamp DESC LIMIT :num');
                $data->bindParam(':userid', $this->userID);
                $data->bindParam(':num', $limitby, PDO::PARAM_INT);
                $data->execute();
                
                $result = $data->fetchALL(PDO::FETCH_ASSOC);
    
                return $result;
            }

        } catch(PDOException $db) {
            echo 'ERROR: ' . $db->getMessage();
        }
    }
    
    
    
    /**
     * Provides hours worked for the specified date (Today by default). If the user is working, this
     * additionally obtains the last time stamp and does maths.
     *
     * @param  string   $date   (optional) date to get hours worked. if not provided, returns hours worked for today.
     * @return array    array containing timestamp and hours worked.
     * @access public
     */
    public function hoursWorked($date = "today") {
        
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        try {
            
            // The problem here is that we have to
            
            if ($date == "today") {
                // ---------------------------------------------------------------------------------------------- timestamp = TODAY  and the previous punch was created today.
                $data = $db->prepare('SELECT previous_punch AS clocked_in, timestamp AS clocked_out, hours_worked FROM punches WHERE user_id=:userid AND DATE(timestamp) = CURDATE() AND previous_punch > CURDATE() AND in_out=0 ORDER BY timestamp DESC');
            } else {
                
                $startDate = "$date 00:00:00";
                $enddate = "$date 23:59:59";
                
                $data = $db->prepare('SELECT previous_punch AS clocked_in, timestamp AS clocked_out, hours_worked FROM punches WHERE user_id=:userid AND timestamp > :startDate AND timestamp < :endDate AND previous_punch > :startDate AND in_out=0 ORDER BY timestamp DESC');
                $data->bindParam(':startDate', $startDate);
                $data->bindParam(':endDate', $endDate);
            }

            $data->bindParam(':userid', $this->userID);
            $data->execute();
            $result = $data->fetchALL(PDO::FETCH_ASSOC);

            return $result;
            
        } catch(PDOException $db) {
            echo 'ERROR: ' . $db->getMessage();
        }
    }
    
    
    
    /**
     * Adds a 'punch' to the database for the user.
     *
     * @param  array	$obj   (optional) array that can contain the following keys: time, note, event.
     * @return array	returns an array containing the following keys: success (1 or 0), return (timestamp)
     * @access public
     */
    public function punch($obj = array()) {
        
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Setting the time for the punch
        // If $obj['time'] does not exist OR $obj['time'] is EMPTY,
        // .. set the default time.
        if (!array_key_exists('time', $obj) || $obj['time'] == "") {
            $obj['time'] = date("Y-m-d H:i:s");
        }
        
        $lastPunchResults = $this->lastPunch();
        
        if (!empty($lastPunchResults)) {
    		$lastPunch = date('Y-m-d H:i',strtotime($lastPunchResults));
    		
    		$currentTime = date('Y-m-d H:i',strtotime($obj['time']));
    		
    		if ($lastPunch == $currentTime) {
    			$punch = FALSE;
    			
    			$results = array(
    		    	"success" => 0,
					"return" => "Duplicate punch detected. Punch not accepted for ".date('H:i M d, Y',strtotime($currentTime)),
					"timestamp" => $obj['time']
				);
    		
				return $results;
    		} else {
    			$punch = TRUE;
    		}
    	} else {
    		// Returned array was empty, there was no previous punches.
    		$punch = TRUE;
    	}
    	
    	if ($punch) {
            
            if ($this->isWorking()) {
                // User is working, we'll additionally need to check how many hours they worked.
                // Set it so they are no longer working
                $in_out = 0;
                
                // Grab the last punch from this user
                $hoursWorked = timeBetweenDates($lastPunchResults);
                $lastPunchDB = $lastPunchResults;
            } else {
                $in_out = 1;
                $hoursWorked = null;
                $previousPunch = null;
                $lastPunchDB = null;
            }
        	
        	
        	if (array_key_exists('note', $obj) && $obj['note'] != "") {
                $note = $obj['note'];
        	} else {
                $note = null;
        	}
        	
        	if (array_key_exists('event', $obj) && $obj['event'] != "") {
                $event = $obj['event'];
        	} else {
                $event = "Punch";
        	}
        	
        	$data = $db->prepare('INSERT INTO punches (user_id, timestamp, in_out, event, ip_address, note, previous_punch, hours_worked) VALUES ( :userid, :timestamp, :in_out, :event, :ipaddress, :note, :prevpunch, :hours_worked)');
        	
    
            $data->bindParam(':userid', $this->userID);
            $data->bindParam(':timestamp', $obj['time']);
            $data->bindParam(':in_out', $in_out);
            $data->bindParam(':event', $event);
            $data->bindParam(':ipaddress', $this->userIP);
            $data->bindParam(':note', $note);
            $data->bindParam(':prevpunch', $lastPunchDB);
            $data->bindParam(':hours_worked', $hoursWorked);
    
            $data->execute();
            
            $this->toggleWorking();
    
            //$time = date('H:i M d, Y',strtotime($theTime));
            
            // POST/REDIRECT/GET (we do this to avoid having the form be submitted again in a refresh)
    		
    		
    		$results = array(
    		    "success" => 1,
    		    "return" => $obj['time'],
    		    "timestamp" => $obj['time']
    	    );
    		
    		return $results;
    		
    	}
        
        
    }
    
    /**
     * Toggles user working state
     *
     * @return void
     * @access public
     */	
     public function toggleWorking() {
	    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
	    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    
	    if ($this->isWorking()) {
	        $toggleTo = 0;
	    } else {
	        $toggleTo = 1;
	    }
	    
	    
	    try {
	        $data = $db->prepare('UPDATE users SET is_working=:toggle WHERE staff_id=:userid');
	        $data->bindParam(':toggle', $toggleTo);
	        $data->bindParam(':userid', $this->userID);
	        $data->execute();
	        //$result = $data->fetch(PDO::FETCH_ASSOC);
	        
	        $db = null;
	        
	    } catch(PDOException $db) {
	        //echo $errorMsg;
	        echo 'ERROR: ' . $db->getMessage();
	    }
	}
    
	
	public function logs($num = "") {
		$db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
	    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$data = $db->prepare('SELECT timestamp, in_out, event, note FROM punches WHERE user_id=:userid ORDER BY timestamp DESC LIMIT 10');
            
        $data->bindParam(':userid', $this->userID);
        $data->execute();
        $result = $data->fetchALL(PDO::FETCH_ASSOC);

        return $result;
	}
	
	/**
     * Change password for the user
     *
     * @param   string  new password to change to
     * @return  bool    true or false if the change was successful. 
     * @access public
     */
	public function changePassword($newPassword) {
	    if (isset($newPassword) && $newPassword != "") {
	        $hspass = password_hash($newPassword, PASSWORD_BCRYPT);
	        
	        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
	        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	        
	        $data = $db->prepare('UPDATE users SET password=:newpass WHERE staff_id=:userid');
            $data->bindParam(':newpass', $hspass);
            $data->bindParam(':userid', $this->userID);
            $data->execute();
	        $db = null;
	        
	        return true;
	    } else {
	        // No password provided. Unsuccessful call. return false
	        return false;
	    }
	}
	
	
	public function getSchedule() {
	    // select staff_id, dept_id, first_name, last_name, is_working, is_freelance, schedule FROM users WHERE is_active=1 AND schedule IS NOT Null
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        try {
            
            $data = $db->prepare('SELECT schedule FROM users WHERE staff_id=:userid');
            $data->bindParam(':userid', $this->userID);
            $data->execute();
            $result = $data->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $db) {
            //echo $errorMsg;
            echo 'ERROR: ' . $db->getMessage();
        }
        
        if (!empty($result)) {
            return json_decode($result['schedule'], true);
        } else {
            return false;
        }

	}
	
	
	public function setSchedule($newSchedule) {
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        try {
            $data = $db->prepare('UPDATE users SET schedule=:schedule WHERE staff_id=:userid');
            $data->bindParam(':schedule', $newSchedule);
            $data->bindParam(':userid', $this->userID);
            $data->execute();
            //$result = $data->fetch(PDO::FETCH_ASSOC);
            
            return true;
        } catch(PDOException $db) {
            //echo $errorMsg;
            echo 'ERROR: ' . $db->getMessage();
            
            return false;
        }

	}
	
	
	public function getEmail() {
		$db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $data = $db->prepare('SELECT email FROM users WHERE staff_id=:userid');
        $data->bindParam(':userid', $this->userID);
        $data->execute();
        $result = $data->fetch(PDO::FETCH_ASSOC);
        
        return $result['email'];
	}
	
	public function updateEmail() {
		
	}

}