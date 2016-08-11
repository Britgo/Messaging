<?php

// Class for exceptions during message creation handling

class Messerr extends Exception {

	public $Header;
	
	public function __construct($msg, $hdr = "Message System Error") {
   	parent::__construct($msg);
   	$this->Header = $hdr;
   }
}
?>
