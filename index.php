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

include 'php/messerr.php';
include 'php/opendb.php';
include 'php/person.php';

try {
   opendb();
}
catch (Messerr $e) {
   $mess = "Open database: " . $e->getMessage();
   include 'php/wrongentry.php';
   exit(0);
}

$Title = 'BGA Messaging Create Message';
include 'php/head.php';
?>
<body>
<script language="javascript" src="webfn.js"></script>
<script language="javascript">
function checkform()
{
   var form = document.mform;
   if  (!okname(form.name.value))  {
      alert("Invalid player name given");
      return false;
   }
   return  true;
}
</script>
<form name="mform" action="msgsend.php" method="post" enctype="application/x-www-form-urlencoded" onsubmit="javascript:return checkform();">
<table>
<tr>
   <td><b>From</td>
   <td><input type="text" name="from" size="30"></td>
</tr>
<tr>
   <td><b>Email</td>
   <td><input type="email" name="email" size="30"></td>
</tr>
<tr>
   <td><b>To</b></td>
   <td><select name="recip">
   <option value="">Select recipient</option>
<?php
$people = get_person_list();
foreach ($people as $pers)
   print <<<EOT
   <option value="Pers:{$pers->formencode()}">{$pers->display_name()}</option>

EOT;
?>
   </select></td>
</tr>
<tr>
   <td><b>Subject</b></td>
   <td><input type="text" name="subject" size="60"></td>
</tr>
<tr>
   <td>&nbsp;</td><td><textarea name="mess" rows="10" cols="60"></textarea></td>
</tr>
<?php include 'php/sumchallenge.php' ?>
<tr><td align="center"><input type="submit" name="sub" value="Submit"></td></tr>
</table>
</form>
</body>
</html>
