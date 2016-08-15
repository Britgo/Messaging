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
   $people = get_person_list(true);
   $people_dict = get_alias_dict($people);
   $current_roles = Role::get_roles_list($people_dict);
   $mlists = Mailing::get_mailings_list();
}
catch (Messerr $e) {
   $mess = "Open database: " . $e->getMessage();
   include '../php/wrongentry.php';
   exit(0);
}

$Title = "List of mailinsgs";
include '../php/head.php';
?>
<body>
<script language="javascript" src="/webfn.js"></script>
<script language="javascript">
function okdel(name, url)  {
   if  (!confirm("Do you really want to delete mailing list " + name + " from the alias system"))
      return;
   document.location = "/admin/delmailing.php?" + url;
}
</script>
<h1>Mailing lists on alias system.</h1>
<table cellpadding="3" cellspacing="5">
<tr>
   <th>Name</th>
   <th>Description</th>
   <th>Role members</th>
   <th>Named members</th>
   <th>Actions</th>
</tr>
<?php
foreach ($mlists as $mlist) {
   $mll = $mlist->urlof();
   print <<<EOT
<tr>
   <td>{$mlist->display_name()}</td>
   <td>{$mlist->display_description()}</td>
   <td>Needs doing</td>
   <td>Needs doing</td>
   <td><a href="/admin/updmlist.php?$mll" title="Update details this mailing list">Update</a>
   &nbsp;<a href="javascript:okdel('{$mlist->text_name()}', '$mll');" title="Remove this mailing list from the system">Delete</a></td>
</tr>

EOT;
}
?>
</table>
<p>Please <a href="/admin/index.php">Click here</a> to return to the admin page or <a href="/admin/updmlist.php">click here</a> to add a new mailing list to the system.</p>
</body>
</html>
