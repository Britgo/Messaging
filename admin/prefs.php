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

try {
   opendb();
   $mypers = new Person($userid, "", true);
   $mypers->fetchdetsfromalias();
   $current_aliases = $mypers->get_alt_aliases();
   $not_aliases = $mypers->get_alt_aliases(true);
   $cpw = htlmspecialchars($mypers->get_passwd());
   $roles = Role::get_personal_roles($mypers);
}
catch (Messerr $e) {
   $mess = "Open database: " . $e->getMessage();
   include 'php/wrongentry.php';
   exit(0);
}

// Make a copy of the aliases and pad out with blanks to 20

$alias_copy = $current_aliases;        // Suppose to make new copy
while (count($alias_copy) < 20)
   array_push($alias_copy, "");
   
$Title = "Update personal options";
include '../php/head.php';
?>
<body>
<script language="javascript" src="/webfn.js"></script>
<script language="javascript">
function checkform()  {
   var fm = document.pform;  
}
function goBack() {
   window.history.back();
}
</script>
<?php
print <<<EOT
<h1>Updating preferences for {$mypers->display_name()}</h1>
<form action="/admin/prefs2.php" method="post" enctype="application/x-www-form-urlencoded" onsubmit="javascript:return checkform();">
<p>Your system user id and main alias on the system is <b>{$mypers->display_alias()}</b> and you have
the following additional aliases:</p>
<table>

EOT;
for ($row = 0; $row < 3;  $row++)  {
   print "<tr>\n";
   for ($col = 0;  $col < 4;  $col++)  {
      $n = $row * 4 + $col;
      print <<<EOT
   <td><input type="text" name="alias$n" value="{$alias_copy[$n]}" size="20">

EOT;
   }
   print "<\tr>\n";
}
?>
</table>
<?php
if (count($roles) > 0)  {
   print <<<EOT
<p>You are also holder of the following offices:</p>
<ul>

EOT;
   foreach ($roles as $rl)  {
      $qr = htmlspecialchars($rl);
      print "<li>$qr</li>\n";
   }
   print "</ul>\n";
}
print <<<EOT
<table>
<tr>
   <td><b>Email address</b></td>
   <td><input type="text" name="email" value="{$mypers->display_email()}" size="30"></td>
 </tr>
 <tr>
 
 EOT;
 if ($mypers->Display)
   print "<td><input type=\"checkbox\" name=\"dispok\" checked="\checked\"></td>\n";
 else
   print "<td><input type=\"checkbox\" name=\"dispok\"></td>\n";
 
 print <<<EOT
   <td>Select if your name (not email) may appear on the drop-dwon list.</td>
 </tr>
 <tr>
   <td>Gender (for proper address only)</td>
   <td><select name="gender">
 EOT;
 $usel = $msel = $fsel = "";
 switch  ($mypers->Gender)  {
   default:
      $usel = " checked=\"checked";
      break;
   case 'M':
      $msel = " checked=\"checked";
      break;
   case  'F':
      $fsel = " checked=\"checked";
      break;
 }
 print <<<EOT
 <option value="U" label="not given"$usel></option>
 <option value="M" label="not given"$msel></option>
 <option value="F" label="not given"$fsel></option>
   </select></td>
 </tr>
 <tr>
   <td><b>Password</b></td>
   <td><input type="password" name="passw1" size="20" value="$cpw"></td>
</tr>
 <tr>
   <td><b>Confirmed</b></td>
   <td><input type="password" name="passw1" size="20" value="$cpw"></td>
   
EOT;
?>
</tr>
<tr>
   <td><button name="goback" value="Cancel" type="button" onclick="goBack()"></button></td>
   <td><button name="subm" value="Save changes" type="submit"></button></td>
</tr>
</table>
</form>
</body>
</html>
