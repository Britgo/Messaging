<?php

// Open database and throw error if not nice.

function opendb()
{

	//  CHANGE THESE AS REQUIRED!!!!

	$hostname = "localhost";	
	$username = "messguser";
	$password = "mu1608";
	$dbname = "messaging";
	
	if  (!mysql_connect($hostname, $username, $password)  ||  !mysql_select_db($dbname))  {
		$ecode = mysql_error();
		throw  new  Messerr("Cannot open database, error was $ecode", "Database error");
	}
}

function closedb()
{
	mysql_close();
}
?>
