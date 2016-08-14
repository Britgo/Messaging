<?php

//   Copyright 2016 John Collins

// *****************************************************************************
// PLEASE BE CAREFUL ABOUT EDITING THIS FILE, IT IS SOURCE-CONTROLLED BY GIT!!!!
// Your changes may be lost or break things if you don't do it correctly!
// *****************************************************************************

//   This program is free software: you can redistribute it and/or modify
//   it under the terms of the GNU General Public License as published by
//   the Free Software Foundation, either version 3 of the License, or
//   (at your option) any later version.

//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.

//   You should have received a copy of the GNU General Public License
//   along with this program.  If not, see <http://www.gnu.org/licenses/>.

class Person {
	public $First;            // Name stuff
	public $Last;
	public $Mainalias;        // Used as ID in various places
	public $Email;
	public $Gender;           // U M or F
	public $Display;          // Whether to display
	
	public function parsename($f = "", $l = "") {
  		$f = trim($f);
		$l = trim($l);
		if (strlen($f) != 0)  {
			if (strlen($l) != 0) {
				$this->First = $f;
				$this->Last = $l;
			}
			elseif (preg_match("/^\s*(\S+)\s+(.+?)\s*$/", $f, $matches))  {
				$this->First = $matches[1];
				$this->Last = $matches[2];
			}
			elseif (preg_match("/^\S+$/", $f))  {
				$this->First = $f;
				$this->Last = "_";
			}
			else
				throw new Messerr("Cannot parse name", "Person error");
		}
		else  {
			$this->First = $this->Last = $f;
		}
	}

	public function __construct($f = "", $l = "", $isalias = false) {
      if  ($isalias)  {
         $this->Mainalias = $f;
         $this->First = $this->Last = "";
      }
      else  { 
         $this->parsename($f, $l);
         $this->Mainalias = "";
      }
      $this->Email = "";
		$this->Gender = 'M';        // Not sexist most are.
		$this->Display = false;
	}
	
	public function isdefined()  {
		return  strlen($this->First) != 0  &&  strlen($this->Last) != 0  && strlen($this->Mainalias) != 0;
	}
	
	public function queryofname() {
		$qf = mysql_real_escape_string($this->First);
		$ql = mysql_real_escape_string($this->Last);
		return "first='$qf' and last='$ql'";
	}
	
	public function queryofalias($not = false)  {
	   $qid = mysql_real_escape_string($this->Mainalias);
	   $op = $not? '!=' : '=';
	   return  "mainalias$op'$qid'";
	}
	
	public function urlofname() {
	   $f = urlencode($this->First);
      $l = urlencode($this->Last);
      return "f=$f&l=$l";
   }
   
   public function urlofalias() {
	   $f = urlencode($this->Mainalias);
      return "alias=$f";
   }
   
   public function formencode() {
      return urlencode($this->First . ' ' . $this->Last);
   }

	public function fromgetfl() {
   	$this->First = $_GET["f"];
      $this->Last = $_GET["l"];
      return  $this;
 	}
 	
 	public function fromgetname() {
 	   parsename($_GET['name']);
 	   return $this;
 	}
 	
 	public function fromgetalias() {
   	$this->Mainalias = $_GET["alias"];
  	}
  	
  	public function fetchdetsfromname()  {
      $ret = mysql_query("SELECT mainalias,email,gender,display FROM person WHERE {$this->queryofname()}");
      if (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not fetch person record - $e");
      }
      if  (mysql_num_rows($ret) == 0)
         throw new Messerr("No person found for {$this->text_name()}");
      $row = mysql_fetch_assoc($ret);
      $this->Mainalias = $row["mainalias"];
      $this->Email = $row["email"];
      $this->Gender = $row["gender"];
      $this->Display = $row["display"];
      return  $this;
   }
   
   public function fetchdetsfromalias()  {
      $ret = mysql_query("SELECT first,last,email,gender,display FROM person WHERE {$this->queryofalias()}");
      if (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not fetch person record - $e");
      }
      if  (mysql_num_rows($ret) == 0)
         throw new Messerr("No person found for {$this->First} {$this->Last}");
      $row = mysql_fetch_assoc($ret);
      $this->First = $row["first"];
      $this->Last = $row["last"];
      $this->Email = $row["email"];
      $this->Gender = $row["gender"];
      $this->Display = $row["display"];
      return  $this;
   }
   
   public function  create()  {
      $qfirst = mysql_real_escape_string($this->First);
      $qlast = mysql_real_escape_string($this->last);
      $qemail = mysql_real_escape_string($this->Email);
      $qalias = mysql_real_escape_string($this->Mainalias);
      $qgender = mysql_real_escape_string($this->Gender);
      $qdisplay = $this->Display? 1: 0;
      $ret = mysql_query("INSERT INTO person (first,last,mainalias,email,gender,display) VALUES ('$qfirst','$qlast','$qmainalias','$qemail','$qgender',$qdisplay)");
      if (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not create person record - $e");
      }
      return $this;
   }
   
   public function update()  {
      $qemail = mysql_real_escape_string($this->Email);
      $qgender = mysql_real_escape_string($this->Gender);
      $qdisplay = $this->Display? 1: 0;
      $ret = mysql_query("UPDATE person set email='$qemail',gender='$qgender',display=$qdisplay WHERE {$this->queryofalias()}");
      if (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not updae person record - $e");
      }
   }
  
   public function text_name()  {
      if  ($this->Last == '_')
			return  $this->First;
 		return  $this->First . ' ' . $this->Last;
   }
   
   public function text_name_nq()  {
      $ret = $this->text_name();
      $ret = str_repalce("'", "", $ret);
      $ret = str_repalce('"', "", $ret);
      return  $ret;
   }
   
	public function display_name() {
		return  htmlspecialchars($this->text_name());
 	}
 	
 	public function display_alias() {
		return  htmlspecialchars($this->Mainalias);
 	}
 	
 	public function display_email()  {
 	   return  htmlspecialchars($this->Email);
 	}
 	
 	public function is_same($pl) {
		return  strcasecmp($this->First, $pl->First) == 0  && strcasecmp($this->Last, $pl->Last) == 0;
	}
	
	public function is_admin() {
	   $ret = mysql_query("SELECT COUNT(*) FROM logins WHERE {$this->queryofalias()}");
	   if (!$ret || mysql_num_rows($ret) == 0)
	     return  false;
	   $row = mysql_fetch_array($ret);
	   return $row[0] != 0;
	}
	
	public function set_admin($pw) {
	   if ($this->is_admin())
	      throw new Messerr("User is already admin");
	   $qpw = mysql_real_escape_string($pw);
	   $qma = mysql_real_escape_string($this->Mainalias);
	   $ret = mysql_query("INSERT INTO logins (mainalias,password) VALUES ('$qma','$qpw')");
	   if (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not set admin - $e");
      }
	}

	public function delete_admin() {
	   $ret = mysql_query("DELETE FROM logins WHERE {$this->queryofalias()}");
	   if (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not delete admin - $e");
      }
	}
	
	public function get_passwd() {
	   $ret = mysql_query("SELECT password FROM logins WHERE {$this->queryofalias()}");
	   if (!$ret || mysql_num_rows($ret) == 0)
	     return  "";
	   $row = mysql_fetch_array($ret);
	   return $row[0];
	}
	
	public function reset_password($npw)  {
	   $qpw = mysql_real_escape_string($npw);
	   $ret = mysql_query("UPDATE logins SET password='$qpw' WHERE {$this->queryofalias()}");
	   if (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not reset password - $e");
      }
	}
	
	public function get_alt_aliases($not = false)  {
	   $ret = mysql_query("SELECT altalias FROM aliases WHERE {$this->queryofalias($not)} ORDER BY altalias");
	   if (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not read alt alias record - $e");
      }
      $result = array();
      while ($row = mysql_fetch_array($ret))
         array_push($result, $row[0]);
      return  $result;
	}
	
	public function replace_aliases($newaliases)
	{
	   $ret = mysql_query("DELETE FROM aliases WHERE {$this->queryofalias()}");
	   if (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not delete old aliases - $e");
      }
      $qma = mysql_real_escape_string($this->Mainalias);
      foreach ($newaliases as $na)  {
         $qna = mysql_real_escape_string($na);
         $ret = mysql_query("INSERT INTO aliases (mainalias,altalias) VALUES ('$qma','$qna')");
         if (!$ret)  {
            $e = mysql_error();
            throw new Messerr("Could not add alias - $e");
         }
      }
	}
}

function get_person_list($incndisplay = false)  {
   $wh = " WHERE display!=0";
   if ($incndisplay)
      $wh = "";
   $ret = mysql_query("SELECT first,last FROM person$wh ORDER BY last,first");
   if (!$ret)  {
      $e = mysql_error();
      throw new Messerr("Could not search records - $e");
   }
   $result = array();
   while ($row = mysql_fetch_array($ret))
      array_push($result, new Person($row[0], $row[1]));
   foreach ($result as $p)
      $p->fetchdetsfromname();
   return  $result;
}

function get_alias_dict($persons)  {
   $result = array();
   foreach ($persons as $p)
      $result[$p->Mainalias] = $p;
   return  $result;
}
?>
