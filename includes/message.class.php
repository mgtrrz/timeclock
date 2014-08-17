<?php

class Message {
	private $message = "";
	private $alertStyle = "";
	
	public function __construct($message = "", $alertStyle = "", $persistent = false) {
		$this->setMessage($message, $alertStyle, $persistent);
    }
	
	public function setMessage($message, $alertStyle, $persistent = false) {
		$this->message = $message;
		$this->alertStyle = $alertStyle;
		if ($persistent) {
			$_SESSION['message_alert'] = $message;
			$_SESSION['message_alertStyle'] = $alertStyle;
		}
	}
	
	public function displayMessage($clear = true) {
		if (isset($_SESSION['message_alert'])) {
		
			echo '<div id="alert">';
			echo '<div class="alert '.$_SESSION['message_alertStyle'].' fade in"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>'.$_SESSION['message_alert'].'</div>'; 
			echo '</div>';
			$this->clearMessage();
			
		} else {
		
			echo '<div id="alert">';
			echo '<div class="alert '.$this->alertStyle.' fade in"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>'.$this->message.'</div>'; 
			//$alert = "";
			//$alertStyle = ""; 
			echo '</div>';
			if ($clear) {
				$this->clearMessage();
			}
			
		}
	}
	
	public function clearMessage() {
		$this->message = "";
		$this->alertStyle = "";
		unset($_SESSION['message_alert']);
		unset($_SESSION['message_alertStyle']);
		
	}
}