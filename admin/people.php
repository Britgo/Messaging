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

try {
   opendb();
   $mypers = new Person($userid, "", true);
   $mypers->fetchdetsfromalias();
   $people = get_person_list(true);
}
catch (Messerr $e) {
   $mess = "Open database: " . $e->getMessage();
   include '../php/wrongentry.php';
   exit(0);
}

$Title = "List of people on alias system";
include '../php/head.php';
?>
<body>
<h1>List of people on alias system.</h1>
<table>
<tr>
   <th>Name</th>
   <th>Main alias</th>
   <th>Other aliases</th>
   <th>Role(s)</th>
   <th>Actions</th>
</tr>
<?php
foreach ($people as $pers) {
   try {
      $aliases = $pers->get_alt_aliases();
      $isadm = $pers->is_admin();
      $roles = Role::get_personal_roles($pers);
   }
   catch (Messerr $e) {
      print <<<EOT
<tr><td colspan="5">Some sort of error with {$pers->display_name()}</td></tr>

EOT;
      continue;
   }
   $oa = htmlspecialchars(join(", ", $aliases));
   $rls = htmlspecialchars(join(", ", $roles));
   print <<<EOT
<tr>
   <td>{$pers->display_name()}</td>
   <td>{$pers->display_alias()}</td>
   <td>$oa</td>
   <td>$rls</td>
   <td><a href="/admin/updpers.php?{$pers->urlofalias()}" title="Update details for this person">Update</a>
EOT;
   if (count($roles) == 0)
      print <<<EOT
<a href="javascript:okdel('{$pers->text_name_nq()}', '{$pers->urlofalias()}');" title="Remove this person from the system">Delete</a>
EOT;
   if ($isadm)
      print <<<EOT
<a href="javascript:okunadm('{$pers->text_name_nq()}', '{$pers->urlofalias()}');" title="Cancel this person's admin rights">Un-admin</a>
EOT;
   else
       print <<<EOT
<a href="javascript:giveadm('{$pers->text_name_nq()}', '{$pers->urlofalias()}');" title="Give this person admin rights">Make admin</a>
EOT;
   print "</tr>\n";
}
?>
</table>
<p><a href="/admin/newperson.php">Click here</a> to add a new person to the system.</p>
</body>
</html>
