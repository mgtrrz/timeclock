<?php

class Admin {
    
    public function usersWorking() {
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        try {
            $data = $db->prepare('SELECT staff_id, first_name, last_name, is_working, schedule FROM users WHERE is_working=1');
            $data->execute();
            $result = $data->fetchALL(PDO::FETCH_ASSOC);
            
            $newResult = $result;
            
            $counter = 0;
            foreach ($result as $row) {
                
                $data = $db->prepare('SELECT MAX(timestamp) as timestamp FROM punches WHERE user_id=:userid AND in_out=1');
                $data->bindParam(':userid', $row['staff_id']);
                $data->execute();
                $timeresult = $data->fetch(PDO::FETCH_ASSOC);
                
                $newResult[$counter]['timestamp'] = $timeresult['timestamp'];
                $counter++;
            }
            
            return $newResult;
            
        } catch(PDOException $db) {
            //echo $errorMsg;
            echo 'ERROR: ' . $db->getMessage();
        }
    }
    
    public function createUser($details) {
	    
    }
    
    public function getUserPunches($num = "20") {
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        try {
            
            if ($num == 'ALL') {
                $data = $db->prepare('SELECT id, user_id, timestamp, in_out, event, ip_address, note FROM punches ORDER BY timestamp DES');
            } elseif (is_numeric($num)) {
                $limitby = intval($num);
                $data = $db->prepare('SELECT id, user_id, timestamp, in_out, event, ip_address, note FROM punches ORDER BY timestamp DESC LIMIT :num');
                $data->bindParam(':num', $limitby, PDO::PARAM_INT);
            } else {
                echo 'Number parameter is incomplete';
                exit;
            }
            
            $data->execute();
            $result = $data->fetchALL(PDO::FETCH_ASSOC);
            
            return $result;
        } catch(PDOException $db) {
            //echo $errorMsg;
            echo 'ERROR: ' . $db->getMessage();
        }
    }
    
    public function getUserSchedule($users = 'ALL') {
        // select staff_id, dept_id, first_name, last_name, is_working, is_freelance, schedule FROM users WHERE is_active=1 AND schedule IS NOT Null
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        try {
            
            if ($users == 'ALL') {
                $data = $db->prepare('select staff_id, dept_id, first_name, last_name, is_working, is_freelance, schedule FROM users WHERE is_active=1 AND schedule IS NOT Null');
            } elseif (is_numeric($users)) {
                $data = $db->prepare('select staff_id, dept_id, first_name, last_name, is_working, is_freelance, schedule FROM users WHERE staff_id=:userid AND is_active=1 AND schedule IS NOT Null');
                $data->bindParam(':userid', $row['staff_id']);
            } else {
                echo 'Users parameter is incomplete';
                exit;
            }
            
            $data->execute();
            $result = $data->fetchALL(PDO::FETCH_ASSOC);
            
            return $result;
        } catch(PDOException $db) {
            //echo $errorMsg;
            echo 'ERROR: ' . $db->getMessage();
        }
    }
    
    
    public function getAllUsers() {
        // select staff_id, dept_id, first_name, last_name, is_working, is_freelance, schedule FROM users WHERE is_active=1 AND schedule IS NOT Null
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        try {
            
            $data = $db->prepare('SELECT staff_id, username, first_name, last_name FROM users ORDER BY first_name');
            $data->execute();
            $result = $data->fetchALL(PDO::FETCH_ASSOC);
            
            return $result;
        } catch(PDOException $db) {
            //echo $errorMsg;
            echo 'ERROR: ' . $db->getMessage();
        }
    }
    
}