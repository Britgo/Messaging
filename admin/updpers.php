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

include '../php/session.php';
include '../php/messerr.php';
include '../php/opendb.php';
include '../php/session.php';
include '../php/checklogged.php';
include '../php/person.php';
include '../php/role.php';
include '../php/mailing.php';

if  (!isset($_GET['alias']))  {
   $mess = "No person given";
   include '../php/wrongentry.php';
   exit(0);
}

$updalias = $_GET['alias'];

try {
   opendb();
   $mypers = new Person($updalias, "", true);
   $mypers->fetchdetsfromalias();
   $cpw = htmlspecialchars($mypers->get_passwd());
}
catch (Messerr $e) {
   $mess = "Open database: " . $e->getMessage();
   include '../php/wrongentry.php';
   exit(0);
}

$Title = "Update email/password for user";
include '../php/head.php';
?>
<body>
<script language="javascript" src="/webfn.js"></script>
<script language="javascript">
function checkform()  {
   var fm = document.pform;
   if (!nonblank(fm.email.value))  {
      alert("No email address specified");
      return false;
   }
   
   if (fm.passw1.value != fm.passw2.value)  {
      alert("Passwords do not match");
      return  false;
   }
   
   return true;
}
</script>
<?php
print <<<EOT
<h1>Updating details for {$mypers->display_name()}</h1>
<form name="pform" action="/admin/updpers2.php" method="post" enctype="application/x-www-form-urlencoded" onsubmit="javascript:return checkform();">
<p>This person has system user id and main alias of <b>{$mypers->display_alias()}</b>.</p>

EOT;
?>
<table cellpadding="3" cellspacing="4">
<tr>
   <td><b>Email address</b></td>
<?php
print <<<EOT
   <td><input type="text" name="email" value="{$mypers->display_email()}" size="30"></td>

EOT;
?>
</tr>
<tr>
<?php
if ($mypers->Display)
   print "<td align=\"right\"><input type=\"checkbox\" name=\"dispok\" checked=\"checked\"></td>\n";
else
   print "<td align=\"right\"><input type=\"checkbox\" name=\"dispok\"></td>\n";
?>
<td>Select if your name (not email) may appear on the drop-down list.</td>
</tr>
<tr>
   <td>Gender (for proper address only)</td>
   <td><select name="gender">
<?php
$usel = $msel = $fsel = "";
switch  ($mypers->Gender)  {
   default:
      $usel = " selected=\"selected\"";
      break;
   case 'M':
      $msel = " selected=\"selected\"";
      break;
   case 'F':
      $fsel = " selected=\"selected\"";
      break;
 }
print <<<EOT
<option value="U"$usel>(Not given)</option>
<option value="M"$msel>Male</option>
<option value="F"$fsel>Female</option> </select></td>
</tr>

EOT;
if ($mypers->is_admin())  {
   print <<<EOT
<tr>
  <td><b>Password</b></td>
  <td><input type="password" name="passw1" size="20" value="$cpw"></td>
</tr>
<tr>
   <td><b>Confirmed</b></td>
   <td><input type="password" name="passw2" size="20" value="$cpw"></td>
</tr>

EOT;
}
?>
<tr>
   <td><a href="javascript:history.go(-1);">Go Back</a></td>
   <td><input type="submit" name="subm" value="Save Changes"></td>
</tr>
</table>
</form>
</body>
</html>
