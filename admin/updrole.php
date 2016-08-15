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

$updating = null;
if  (isset($_GET['role']))
   $updating = $_GET['role'];

try {
   opendb();
   $people = get_person_list(true);
   $peopledict = get_alias_dict($people);
   if  (is_null($updating))  {
      //  Not updating get role list to check against so we don't make one to clash
      $existing_roles = Role::get_roles_list($peopledict);
      $existing_aliases = Person::get_every_alias();
      $mnames = Mailing::get_mailings_names();
      $updrole = new Role('***INVALID***');        // Something for the select thing not to match
      $Title = "Create new role";
   }
   else  {
      // Updating, fill in existing person
      $updrole = new Role($updating);
      $updrole->fetchalias();
      $updrole->Aliasperson = $peopledict[$updrole->Aliasname];
      $Title = "Update role {$updrole->display_name()}";
   }
}
catch (Messerr $e) {
   $mess = "Open database: " . $e->getMessage();
   include '../php/wrongentry.php';
   exit(0);
}

include '../php/head.php';
?>
<body>
<script language="javascript" src="/webfn.js"></script>
<script language="javascript">
<?php
if (is_null($updating))  {
   print "Existing_aliases = new Array();\n";
   foreach ($existing_aliases as $al) {
      $lal = strtolower($al);
      print "Existing_aliases['$lal'] = 1;\n";
   }
   // We're lumping those in with other aliases as well
   foreach ($existing_roles as $al) {
      $lal = strtolower($al->Rolename);
      print "Existing_aliases['$lal'] = 1;\n";
   }
   foreach ($mnames as $al)  {
      $lal = strtolower($al);
      print "Existing_aliases['$lal'] = 1;\n";
   }
}
?>

function checkform()  {
   var fm = document.rform;
<?php
if (is_null($updating))
   print <<<EOT
   var nam = fm.rolename.value;
   var lnam = String.toLocaleLowerCase(nam);
   if  (!nonblank(nam))  {
      alert("No name given");
      return  false;
   }
   if  (!okalias(nam))  {
      alert("Invalid name");
      return false;
   }
   if  (Existing_aliases[lnam])  {
      alert(nam + " clashes with an existing name or alias");
      return false;
   }

EOT;
?>       
   if (fm.person.selectedIndex <= 0)  {
      alert("No person selected");
      return  false;
   }
   
   return  true;
}

</script>
<?php
if (is_null($updating))  {
   print <<<EOT
<h1>Creating a new role</h1>
<p>Please enter the name of the new role, normally the first letter is capitalised, and select from the drop-down list the
person who will fill that role.</p>

EOT;
}
else  {
   print <<<EOT
<h1>Updating the role of {$updrole->display_name()}</h1>
<p>Please select the person to fill the role of {$updrole->display_name()}. If you wanted to change the name, just delete and start
again.</p>

EOT;
}
?>
<form name="rform" action="/admin/updrole2.php" method="post" enctype="application/x-www-form-urlencoded" onsubmit="javascript:return checkform();">
<table cellpadding="3" cellspacing="4">
<?php
if (is_null($updating))
   print <<<EOT
<tr>
   <td><b>Name of role</b></td>
   <td><input type="text" name="rolename" size="30"></td>
</tr>

EOT;
else
   print "{$updrole->save_hidden()}";
?>
<tr>
<td><b>Person</b></td>
<td>
<select name="person"><option value="--">Select person</option>
<?php
foreach ($people as $pers) {
   $sel = $pers->Mainalias == $updrole->Aliasname? ' selected="selected"': "";
   print <<<EOT
<option value="{$pers->formencode()}"$sel>{$pers->display_name()}</option>

EOT;
}
?>
</select>
</td>
</tr>
<tr>
   <td><a href="javascript:history.go(-1);">Go Back</a></td>
   <td><input type="submit" name="subm" value="Save"></td>
</tr>
</table>
</form>
</body>
</html>
