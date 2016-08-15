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
include '../php/checklogged.php';
include '../php/person.php';
include '../php/role.php';
include '../php/mailing.php';

try {
   opendb();
   $mypers = new Person($userid, "", true);
   $mypers->fetchdetsfromalias();
   $people = get_person_list(true);
   $people_dict = get_alias_dict($people);
   $current_roles = Role::get_roles_list($people_dict);
}
catch (Messerr $e) {
   $mess = "Open database: " . $e->getMessage();
   include '../php/wrongentry.php';
   exit(0);
}

$Title = "List of roles";
include '../php/head.php';
?>
<body>
<script language="javascript" src="/webfn.js"></script>
<script language="javascript">
function okdel(name, url)  {
   if  (!confirm("Do you really want to delete role" + name + " from the alias system"))
      return;
   document.location = "/admin/delrole.php?" + url;
}
</script>
<h1>List of roles on alias system.</h1>
<table cellpadding="3" cellspacing="5">
<tr>
   <th>Position</th>
   <th>Person</th>
   <th>Actions</th>
</tr>
<?php
foreach ($current_roles as $role) {
   $roleurl = $role->urlof();
   print <<<EOT
<tr>
   <td>{$role->display_name()}</td>
   <td>{$role->display_person()}</td>
   <td><a href="/admin/updrole.php?$roleurl" title="Update details this role">Update</a>
   &nbsp;<a href="javascript:okdel('{$role->text_name()}', '$roleurl');" title="Remove this role from the system">Delete</a></td>
</tr>

EOT;
}
?>
</table>
<p><a href="/admin/updrole.php">Click here</a> to add a new role to the system.</p>
</body>
</html>
