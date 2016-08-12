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

class Mailing {

   public $Name;
   public $Description;
   
   public function __construct($n = "", $d = "") {
	   $this->Name = $n;
	   $this->Description = $d;
	}
	
	public function isdefined()  {
		return  strlen($this->Name) != 0  &&  strlen($this->Description) != 0;
	}
	
	public function queryof() {
	   $qname = mysql_real_escape_string($this->Name);
	   return "name='$qname'";
	}
	
	public function formencode() {
      return urlencode($this->Name);
   }
	
	public function fetchdescr() {
	   $ret = mysql_query("SELECT description FROM mailings WHERE {$this->queryof()}");
	   if  (!$ret)  {
         $e = mysql_error();
         throw Messerr("Could not fetch mailing - $e");
      }
      if (mysql_num_rows($ret) == 0)
         throw Messerr("Could not find mailing {$this->Name}");
      $row = mysql_fetch_assoc($ret);
      $this->Description= $row['description'];
      return  $this;
	}
	
	public function display_name() {
 		return  htmlspecialchars($this->Name);
 	}
 	
 	public function display_description() {
 		return  htmlspecialchars($this->Description);
 	}
	
	public static function get_mailings_list() {
	   $ret = mysql_query("SELECT name,description FROM mailings ORDER BY description");
	   if  (!$ret)  {
         $e = mysql_error();
         throw Messerr("Could not fetch mailings - $e");
      }
      $result = array();
      while ($row = mysql_fetch_assoc($ret))  {
         $m = new Mailing($row['name'], $row["description"]);
         array_push($result, $m);
      }
      return  $result;
	}
}
?>