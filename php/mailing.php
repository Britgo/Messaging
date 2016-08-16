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
	
	public function is_same($rl) {
		return strcasecmp($this->Description, $rl->Description) == 0;
	}
	
	public function urlof() {
	   $f = urlencode($this->Name);
      return "mlist=$f";
   }
	
	public function formencode() {
      return urlencode($this->Name);
   }
	
	public function save_hidden() {
		$f = htmlspecialchars($this->Name);
		return "<input type=\"hidden\" name=\"name\" value=\"$f\">";
	}
	
	public function fetchdescr() {
	   $ret = mysql_query("SELECT description FROM mailings WHERE {$this->queryof()}");
	   if  (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not fetch mailing - $e");
      }
      if (mysql_num_rows($ret) == 0)
         throw new Messerr("Could not find mailing {$this->Name}");
      $row = mysql_fetch_assoc($ret);
      $this->Description= $row['description'];
      return  $this;
	}
	
	private function create_rmembs($rmembs)  {
	   $qn = mysql_real_escape_string($this->Name);
	   foreach ($rmembs as $rn)  {
	      $qma = mysql_real_escape_string($rn);
	      $ret = mysql_query("INSERT INTO rmemb (name,role) VALUES ('$qn','$qma')");
	      if  (!$ret)  {
            $e = mysql_error();
            throw new Messerr("Could create role membs - $e");
         }
	   }
	}
	
	private function create_pmembs($mmembs)  {
	   $qn = mysql_real_escape_string($this->Name);
	   foreach ($mmembs as $mm)  {
	      $qmm = mysql_real_escape_string($mm);
	      $ret = mysql_query("INSERT INTO mmemb (name,mainalias) VALUES ('$qn','$qmm')");
	      if  (!$ret)  {
            $e = mysql_error();
            throw new Messerr("Could create pers membs - $e");
         }
	   }
	}
	
	private function del_rmembs()  {
	   $ret = mysql_query("DELETE FROM rmemb WHERE {$this->queryof()}");
	   if  (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not update role membs - $e");
      }
	}
	
	private function del_pmembs()  {
	   $ret = mysql_query("DELETE FROM mmemb WHERE {$this->queryof()}");
	   if  (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not update person membs - $e");
      }
	}
	
	public function create($rmembs = null, $mmembs = null)  {
	   $qn = mysql_real_escape_string($this->Name);
	   $qd = mysql_real_escape_string($this->Description);
	   $ret = mysql_query("INSERT INTO mailings (name,description) VALUES ('$qn','$qd')");
	   if  (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not create mailing - $e");
      }
      if  (!is_null($rmembs))
         $this->create_rmembs($rmembs);
      if  (!is_null($mmembs))
         $this->create_pmembs($mmembs);
      return $this;
	}
	
	public function update($rmembs = null, $mmembs = null)  {
	   $qd = mysql_real_escape_string($this->Description);
      $ret = mysql_query("UPDATE mailings set description='$qd' WHERE {$this->queryof()}");
	   if  (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not update mailings - $e");
      }
      if  (!is_null($rmembs))  {
         $this->del_rmembs();
         $this->create_rmembs($rmembs);
      }
      if  (!is_null($mmembs))  {
         $this->del_pmembs();
         $this->create_pmembs($mmembs);
      }
      return $this;
	}
	
	public function delete()  {
	   $ret = mysql_query("DELETE FROM mailings WHERE {$this->queryof()}");
	   if  (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not update mailings - $e");
      }
      del_rmembs();
      del_pmembs();
      return $this;
	}

	public function text_name() {
	   return  $this->Name;
	}
	
	public function display_name() {
 		return  htmlspecialchars($this->Name);
 	}
 	
 	public function text_description() {
      return  $this->Description;	
 	}

 	public function display_description() {
 		return  htmlspecialchars($this->Description);
 	}

   // These functions get a list of people, we try to be careful about the ordering
    	
 	public function get_role_membs()  {
 	   $ret = mysql_query("SELECT rmemb.role FROM rmemb INNER JOIN roles ON rmemb.role=roles.role WHERE rmemb.{$this->queryof()} ORDER BY roles.ordering");
 	   if  (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not fetch role members of $this->Name - $e");
      }
      $result = array();
      while ($row = mysql_fetch_array($ret))
         array_push($result, $row[0]);
      return  $result;
 	}
 	
 	public function get_name_membs($aliasdict)  {
 	   $ret = mysql_query("SELECT mmemb.mainalias FROM mmemb INNER JOIN person ON mmemb.mainalias=person.mainalias WHERE mmemb.{$this->queryof()} ORDER BY person.last,person.first");
 	   if  (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not fetch role members of $this->Name - $e");
      }
      $result = array();
      while ($row = mysql_fetch_array($ret))
         if (isset($aliasdict[$row[0]]))
            array_push($result, $aliasdict[$row[0]]);
      return  $result;
 	}
	
	public static function get_mailings_list() {
	   $ret = mysql_query("SELECT name,description FROM mailings ORDER BY description");
	   if  (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not fetch mailings - $e");
      }
      $result = array();
      while ($row = mysql_fetch_assoc($ret))  {
         $m = new Mailing($row['name'], $row["description"]);
         array_push($result, $m);
      }
      return  $result;
	}
	
	// Just get names for checking against
	
	public static function get_mailings_names() {
      $ret = mysql_query("SELECT name FROM mailings");
	   if  (!$ret)  {
         $e = mysql_error();
         throw new Messerr("Could not fetch mailings - $e");
      }
      $result = array();
      while ($row = mysql_fetch_array($ret))
         array_push($result, $row[0]);
      return  $result;
	}
}
?>