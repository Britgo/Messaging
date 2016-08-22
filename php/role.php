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

class Role {

   public $Rolename;
   public $Description;
   public $Aliasname;
   public $Aliasperson;
   public $Ordering;
   
   public function __construct($r = "", $d = "") {
	   $this->Rolename = $r;
	   $this->Description = $d;
	   $this->Aliasname = "";
	   $this->Aliasperson = null;
	   $this->Ordering = -1;
	}
	
	public function isdefined()  {
		return  strlen($this->Rolename) != 0  &&  strlen($this->Aliasname) != 0;
	}
	
	public function is_same($rl) {
		return strcasecmp($this->Rolename, $rl->Rolename) == 0;
	}
	
	public function queryof() {
	   $qname = mysql_real_escape_string($this->Rolename);
	   return "role='$qname'";
	}
	
	public function formencode() {
      return urlencode($this->Rolename);
   }
   
   public function urlof() {
	   $f = urlencode($this->Rolename);
      return "role=$f";
   }
   
   public function save_hidden() {
		$f = htmlspecialchars($this->Rolename);
		return "<input type=\"hidden\" name=\"rolename\" value=\"$f\">";
	}
	
	public function fetchalias() {
	   $ret = mysql_query("SELECT description,mainalias,ordering FROM roles WHERE {$this->queryof()}");
	   if  (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not fetch roles - $e");
      }
      if (mysql_num_rows($ret) == 0)
         throw new Messerr("Could not find role {$this->Name}");
      $row = mysql_fetch_assoc($ret);
      $this->Description = $row['description'];
      $this->Aliasname = $row['mainalias'];
      $this->Ordering = $row['ordering'];
      return  $this;
	}
	
	public function fetchperson() {
	   if (!$this->isdefined())
	     throw new Messerr("Undefined role in use");
	   $this->Aliasperson = new Person($this->Aliasname, "", true);
	   $this->Aliasperson->fetchdetsfromalias();
	   return $this;
	}
	
	public function create()  {
	   $qn = mysql_real_escape_string($this->Rolename);
	   $qa = mysql_real_escape_string($this->Aliasname);
	   $qd = mysql_real_escape_string($this->Description);
	   if ($this->Ordering < 0)
	     $this->Ordering = Role::get_next_ordering();
	   $qo = $this->Ordering;
	   $ret = mysql_query("INSERT INTO roles (role,mainalias,ordering,description) VALUES ('$qn','$qa',$qo,'$qd')");
	   if  (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not create roles - $e");
      }
      return $this;
	}
	
	public function update()  {
	   $qa = mysql_real_escape_string($this->Aliasname);
	   $qd = mysql_real_escape_string($this->Description);
      if ($this->Ordering < 0)
	      $this->Ordering = Role::get_next_ordering();
	   $qo = $this->Ordering;
	   $ret = mysql_query("UPDATE roles set mainalias='$qa',ordering=$qo,description='$qd' WHERE {$this->queryof()}");
	   if  (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not update roles - $e");
      }
      return $this;
	}
	
	public function delete()  {
	   $qrole = $this->queryof();
	   $ret = mysql_query("DELETE FROM roles WHERE $qrole");
	   if  (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not update roles - $e");
      }
      $ret = mysql_query("DELETE FROM rmemb WHERE $qrole");
	   if  (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not update role membs - $e");
      }
      return $this;
	}
	
	public function text_name() {
	   return  $this->Rolename;
	}
	
	public function display_name() {
 		return  htmlspecialchars($this->Rolename);
 	}
 	
 	public function display_description() {
 		return  htmlspecialchars($this->Description);
 	}
 	
 	public function display_person() {
      if (is_null($this->Aliasperson))
         return "Person not loaded for {$this->display_name()}";
      return  $this->Aliasperson->display_name(); 	    
 	}
 	
 	public function get_email() {
 	   return $this->Aliasperson->Email;
 	}
	
	public static function get_roles_list($aliasdict = null) {
	   $ret = mysql_query("SELECT role,mainalias,ordering,description FROM roles ORDER BY ordering");
	   if  (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not fetch roles - $e");
      }
      $result = array();
      while ($row = mysql_fetch_assoc($ret))  {
         $r = new Role($row['role']);
         $r->Aliasname = $row['mainalias'];
         $r->Ordering = $row['ordering'];
         $r->Description = $row['description'];
         array_push($result, $r);
      }
      if  (!is_null($aliasdict))  {
         foreach ($result as $r)  {
            if  (isset($aliasdict[$r->Aliasname]))
               $r->Aliasperson = $aliasdict[$r->Aliasname];
         }
      }
      return  $result;
	}
	
   public static function get_personal_roles($pers = null)  {
	   $result = array();
	   if ($pers)
	      $ret = mysql_query("SELECT role FROM roles WHERE {$pers->queryofalias()} ORDER by ordering");
	   else
         $ret = mysql_query("SELECT role FROM roles ORDER by ordering");
	   if  (!$ret) {
         $e = mysql_error();
         throw new Messerr("Could not fetch roles for person - $e");
      }
      while ($row = mysql_fetch_array($ret))
         array_push($result, $row[0]);
      return $result;
	}

   public static function get_next_ordering()  {
      $ret = mysql_query("SELECT MAX(ordering) from roles");
      if  (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not fetch max role ordering - $e");
      }
      if (mysql_num_rows($ret) == 0)
         return  0;
      $row = mysql_fetch_array($ret);
      return  $row[0] + 1000;
   }
}
?>