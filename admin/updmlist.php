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
if  (isset($_GET['mlist']))
   $updating = $_GET['mlist'];

$rolelookupalias = array();
$perslookupalias = array();

try {
   opendb();
   $people = get_person_list(true);
   $peopledict = get_alias_dict($people);
   $existing_roles = Role::get_roles_list($peopledict);
   if  (is_null($updating))  {
      //  Not updating get role list to check against so we don't make one to clash      
      $existing_aliases = Person::get_every_alias();
      $mnames = Mailing::get_mailings_names();
      $updmlist = new Mailing('***INVALID***');        // Something for the select thing not to match
      $Title = "Create new mailling list";
   }
   else  {
      // Updating, fill in existing stuff
      $updmlist = new Mailing($updating);
      $updmlist->fetchdescr();
      $rmembs = $updmlist->get_role_membs();
      // Build array for setting existing roles as checked
      foreach ($rmembs as $r)
         $rolelookupalias[$r] = 1;
      $mmembs = $updmlist->get_name_membs($peopledict);
      // Build array for setting existing people as checked
      foreach ($mmembs as $m)
         $perslookupalias[$m->Mainalias] = 1;
      $Title = "Update mailing list {$updmlist->display_name()}";
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
   var nam = fm.name.value;
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
   if (!nonblank(fm.description.value)) {
      alert("No description");
      return  false;
   }
   //var checkd = 0;
   //var plist = document.getElementsByName("pers[]");
   //for  (var i=0;  i < plist.length;  i++)
	//	if  (plist[i].checked)
	//	   checkd++;
	//if (checkd == 0)  {
	//	alert("No perople selected to go on list");
	//	return  false;
	//}
   return  true;
}

</script>
<?php
if (is_null($updating))  {
   print <<<EOT
<h1>Creating a new mailing list</h1>
<p>Please enter the name and full description of the new mailing list, normally the first letter is capitalised.</p>
<form name="mform" action="/admin/updmlist2.php" method="post" enctype="application/x-www-form-urlencoded" onsubmit="javascript:return checkform();">
<input type="hidden" name="isnew" value="y">

EOT;
}
else  {
   print <<<EOT
<h1>Updating the role of {$updmlist->display_name()}</h1>
<p>Please select the full description of the new mailing list {$updmlist->display_name()}. If you wanted to change the name, just delete and start
again.</p>
<form name="mform" action="/admin/updmlist2.php" method="post" enctype="application/x-www-form-urlencoded" onsubmit="javascript:return checkform();">
<input type="hidden" name="isnew" value="n">

EOT;
}
?>
<table cellpadding="3" cellspacing="4">
<?php
if (is_null($updating))
   print <<<EOT
<tr>
   <td><b>Name of mailing list</b></td>
   <td><input type="text" name="name" size="30"></td>
</tr>

EOT;
else
   print "{$updmlist->save_hidden()}";
?>
<tr>
<td><b>Description</b></td>
<?php
print <<<EOT
<td><input type="text" name="description" value="{$updmlist->text_descr()}" size="30"></td>

EOT;
?>
</tr>
</table>
<h3>Roles to go in list</h3>
<p>Select the people who should go in this list by definition of the roles they hold.</p>
<table>
<?php
$nrm = count($existing_roles);
$cols = min(4,ceil($nrm/10));
$rows = ceil($nrm/$cols);
for ($row = 0;  $row < $rows;  $row++)  {
   print "<tr>\n";
   for ($col < 0;  $col < $cols;  $col++)  {
      $n = $col * $rows + $row;
      print "<td>";
      if  ($n < $nrm)  {
         $sel = !is_null($updating) && isset($rolelookupalias[$existing_roles[$n]->Aliasname])? " checked=\"checked\"": "";
         print <<<EOT
         <input type="checkbox" name="roles[]" value="{$rmembs[$n]}"$sel>
EOT;
      }
      print "</td>\n";
   }
   print "</tr>\n";
}
?>
</table>
<h3>People to go in list</h3>
<p>Select the people who should go in this list by name.</p>
<table>
<?php
$nrm = count($people);
$cols = min(4,ceil($nrm/10));
$rows = ceil($nrm/$cols);
for ($row = 0;  $row < $rows;  $row++)  {
   print "<tr>\n";
   for ($col < 0;  $col < $cols;  $col++)  {
      $n = $col * $rows + $row;
      print "<td>";
      if  ($n < $nrm)  {
         $sel = !is_null($updating) && isset($perslookupalias[$people[$n]]->Mainalias)? " checked=\"checked\"": "";
         print <<<EOT
         <input type="checkbox" name="pers[]" value="{$mmembs[$n]->Mainalias}"$sel>
EOT;
      }
      print "</td>\n";
   }
   print "</tr>\n";
}
?>
<table cellpadding="3" cellspacing="4">
<tr>
   <td><a href="javascript:history.go(-1);">Go Back</a></td>
   <td><input type="submit" name="subm" value="Save"></td>
</tr>
</table>
</form>
<p>Putting a negative weighting will put the role at the end of the list.</p>
</body>
</html>
