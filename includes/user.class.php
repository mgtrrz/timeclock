<?php

class User {
    private $userID;
    private $userIP;
    private $admin;
    public $firstName;
    public $lastName;
    private $timeFormat;
    
    public function __construct($userID) {
        $this->userIP = getUserIP();
        if (isset($userID)) {
            $this->userID = $userID;
        } else {
            // Fall back... if no userID is provided.. grab the session user ID
            $this->userID = $_SESSION['sid'];
        }
        
        // Set the user's name and add it to the class' variables
        $this->setUserRealName();
        // Set their preferred 12/24 hr format
        $this->timeFormat();
    }
    
    
    /**
     * Sets the User ID for this User instance.
     *
     * @param   string    user ID.
     * @access  public
     */
    public function setUserID($userID){
        if (isset($userID)) {
            $this->userID = $userID;
        }
        
        $this->setUserRealName();
    }
    
    public function timeFormat() {
        
        if (empty($timeFormat)) {
            $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
            try {
                $data = $db->prepare('select time_format from users WHERE staff_id=:userid');
                $data->bindParam(':userid', $this->userID);
                $data->execute();
                $result = $data->fetch(PDO::FETCH_ASSOC);
                $db = null;
                
                if (!empty($result)) {
                    if ($result['time_format'] ==  "12") {
                        $this->timeFormat = "12";
                        return "12";
                    } elseif ($result['time_format'] == "24") {
                        $this->timeFormat = "24";
                        return "24";
                    } else {
                        $this->timeFormat = "24";
                        return "24";
                    }
                }
                
            } catch(PDOException $db) {
                echo 'ERROR: ' . $db->getMessage();
            }
            
        } else {
            return $timeFormat;
        }
        
    }
    
    public function askForEarnings() {
        return true;
    }
    
    public function createdDate() {
        
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        try {
            $data = $db->prepare('select created from users WHERE staff_id=:userid');
            $data->bindParam(':userid', $this->userID);
            $data->execute();
            $result = $data->fetch(PDO::FETCH_ASSOC);
            $db = null;
            
            if (!empty($result)) {
                return $result['created'];
            } else {
                // Returning a default date
                return "2014-01-01";
            }
            
        } catch(PDOException $db) {
            echo 'ERROR: ' . $db->getMessage();
            return "2014-01-01";
        }

        
    }
    
    public function setTimeFormat($newFormat) {
        
        if ($newFormat != $timeFormat) {
            $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // uh..
            /*
            if ($newFormat != "24" && $newFormat != "12") {
                $newFormat = "24";
            }
            */
            
            try {
                $data = $db->prepare('UPDATE users SET time_format=:newformat WHERE staff_id=:userid');
                $data->bindParam(':newformat', $newFormat);
                $data->bindParam(':userid', $this->userID);
                if ($data->execute()) {
                    return true;
                } else {
                    return false;
                }

            } catch(PDOException $db) {
                echo 'ERROR: ' . $db->getMessage();
                return false;
            }
            
        } 
        
    }
    
    
    /**
     * Returns the User ID for this User instance.
     *
     * @return   string    user ID.
     * @access  public
     */
    public function getUserID() { return $this->userID; }
    
    
    
    /**
     * Returns the full name of the user
     *
     * @param   bool    (optional) whether or not to format it and return as a string e.g. "John Doe"
     * @return  array   returns 2 keys in an array, 'first_name' and 'last_name'
     * @access  public
     */
    public function getUserRealName($formatted = false) {
        
        if ($formatted) {
            return $this->firstName." ".$this->lastName;
        } else {
            $nameArray = array("first_name" => $this->firstName, "last_name" => $this->lastName);
            return $nameArray;
        }

    }
    
    
    private function setUserRealName() {
	    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        try {
            $data = $db->prepare('select first_name, last_name from users WHERE staff_id=:userid');
            $data->bindParam(':userid', $this->userID);
            $data->execute();
            $result = $data->fetch(PDO::FETCH_ASSOC);
            $db = null;
            
            $this->firstName = $result['first_name'];
            $this->lastName = $result['last_name'];
            
        } catch(PDOException $db) {
            echo 'ERROR: ' . $db->getMessage();
        }
    }
    
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
            //return $result['is_working'];
            
            if ($result['is_working'] == 1)
                return true;
            else
                return false;
            
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
        
        if ($this->admin == "") {
            $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            try {
                $data = $db->prepare('SELECT is_admin FROM users WHERE staff_id=:userid');
                $data->bindParam(':userid', $this->userID);
                $data->execute();
                $result = $data->fetch(PDO::FETCH_ASSOC);
                $db = null;
                
                if ($this->admin == "") {
                    $this->admin = $result['is_admin'];
                }
                
                return $result['is_admin'];
                
            } catch(PDOException $db) {
                //echo $errorMsg;
                echo 'ERROR: ' . $db->getMessage();
            }
        } else {
            return $this->admin;
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
                $data = $db->prepare('SELECT id, timestamp, previous_punch AS previous_timestamp, hours_worked, earnings from punches WHERE user_id=:userid AND in_out=0 AND hours_worked IS NOT NULL ORDER BY timestamp DESC LIMIT :num');
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
     * @param  string   $date   (optional) date to get hours worked. if not provided, returns hours worked for today. This needs to be in MySQL DATE format: YYYY-MM-DD
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
                $data = $db->prepare('SELECT hours_worked FROM punches WHERE user_id=:userid AND DATE(timestamp) = CURDATE() AND previous_punch > CURDATE() AND in_out=0 ORDER BY timestamp DESC');
            } else {
                
                $startDate = "$date 00:00:00";
                $enddate = "$date 23:59:59";
                
                $data = $db->prepare('SELECT hours_worked FROM punches WHERE user_id=:userid AND timestamp > :startDate AND timestamp < :endDate AND previous_punch > :startDate AND in_out=0 ORDER BY timestamp DESC');
                $data->bindParam(':startDate', $startDate);
                $data->bindParam(':endDate', $endDate);
            }
            

            $data->bindParam(':userid', $this->userID);
            $data->execute();
            $result = $data->fetchALL(PDO::FETCH_ASSOC);
            
            if ($date == "today" && $this->isWorking()) {
                $totalHoursWorked[] = timeBetweenDates($this->lastPunch());
            } 

            foreach ($result as $punch) {
                $totalHoursWorked[] = $punch['hours_worked'];
            }
            
            if (!empty($totalHoursWorked)) {
                $result = addTimes($totalHoursWorked);
            } else {
                $result = "00:00";
            }

            return $result;
            
        } catch(PDOException $db) {
            echo 'ERROR: ' . $db->getMessage();
        }
    }
    
    
    
    
    public function addNote($message, $timestamp = "last") {
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        try {
            
            if ($timestamp == "last") {
                $timestamp = $this->lastPunch();
            } 
            
            $data = $db->prepare('UPDATE punches SET note=:note WHERE user_id=:userid AND timestamp=:timestamp');
            $data->bindParam(':note', $message);
            $data->bindParam(':userid', $this->userID);
            $data->bindParam(':timestamp', $timestamp);
            
            // need to start doing this for all executes..
            if ($data->execute()) {
                return true;
            } else {
                return false;
            }

        } catch(PDOException $db) {
            //echo $errorMsg;
            echo 'ERROR: ' . $db->getMessage();
            
            return false;
        }
        
    }
    
    
    public function addEarnings($amount) {
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        try {

            $timestamp = $this->lastPunch();
            
            $data = $db->prepare('UPDATE punches SET earnings=:earnings WHERE user_id=:userid AND timestamp=:timestamp');
            $data->bindParam(':earnings', $amount);
            $data->bindParam(':userid', $this->userID);
            $data->bindParam(':timestamp', $timestamp);
            
            // need to start doing this for all executes..
            if ($data->execute()) {
                return true;
            } else {
                return false;
            }

        } catch(PDOException $db) {
            //echo $errorMsg;
            echo 'ERROR: ' . $db->getMessage();
            
            return false;
        }
        
    }
    
    /**
     * Adds a 'punch' to the database for the user.
     *
     * @param  array	$obj   (optional) array that can contain the following keys: time, note, event.
     * @return array	returns an array containing the following keys: success (1 or 0), return (message), timestamp (timestamp)
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
        
        // Getting the information from this user's last punch.
        $lastPunchResults = $this->lastPunch();
        
        if (!empty($lastPunchResults)) {
            
            // Time of the last punch.
    		$lastPunch = date('Y-m-d H:i',strtotime($lastPunchResults));
    		// and our current time..
    		$currentTime = date('Y-m-d H:i',strtotime($obj['time']));
    		
    		// If our current time is the exact same as our last punch, no go.
    		if ($lastPunch == $currentTime) {
    			$punch = false;
    			
    			$results = array(
    		    	"success" => 0,
					"return" => "Duplicate punch detected. Punch not accepted for ".date('H:i M d, Y',strtotime($currentTime)),
					"timestamp" => $obj['time']
				);
    		
				return $results;
    		} else {
    			$punch = true;
    		}
    	} else {
    		// Returned array was empty, there was no previous punches.
    		$punch = true;
    	}
    	
    	// So if everything looks good.. Punch on
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
        	
        	// Checking if notes exist to ad 
        	if (array_key_exists('note', $obj) && $obj['note'] != "") {
                $note = $obj['note'];
        	} else {
                $note = null;
        	}
        	// and checking if an Event exists.
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
    
            // if executing and adding this info to the database was successful..
            if ($data->execute()) {
                // Toggle from working to nonworking or nonworking to working
                $this->toggleWorking();
                // and return the good stuff
                $results = array(
        		    "success" => 1,
        		    "return" => $obj['time'],
        		    "timestamp" => $obj['time']
        	    );
            } else {
                // something bad happened..
                $results = array(
        		    "success" => 0,
        		    "return" => "Error adding punch. Please check with an administrator for this issue.",
        		    "timestamp" => $obj['time']
        	    );
            }
    		
    		return $results;
    		
    	}
        
        
    }
    
    /**
     * Toggles user working state from Working to Non-working or Non-working to Working.
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
	    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		
		$data = $db->prepare('SELECT id, timestamp, in_out, event, note FROM punches WHERE user_id=:userid ORDER BY timestamp DESC LIMIT 10');
            
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
            
            if ($newSchedule == false) {
                // delete schedule
                $data = $db->prepare('UPDATE users SET schedule="" WHERE staff_id=:userid');
            } else {
                $data = $db->prepare('UPDATE users SET schedule=:schedule WHERE staff_id=:userid');
                $data->bindParam(':schedule', $newSchedule);
            }
            
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