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

if (!isset($_POST['name']) || !isset($_POST['isnew']))  {
   $ness = "Not from form???";
   include '../php/wrongentry.php';
   exit(0);
}
$name = $_POST['name'];
$isnew = $_POST['isnew'];
$descr = $_POST['description'];

if (isset($_POST['roles']))
   $roles = $_POST['roles'];
else
   $roles = array();
if (isset($_POST['pers']))
   $pers = $_POST['pers'];
else
   $pers = array();

try {
   opendb();
   $mymailing = new Mailing($name, $descr);
   if ($isnew == 'y')  {
      $mymailing->create($roles, $pers);
      $Title = "Mailing list created OK";
      $h1 = "created";
   }
   else  {
      $mymailing->update($roles, $pers);
      $Title = "Mailing list updated OK";
      $h1 = "updated";
   }
}
catch (Messerr $e)  {
   $mess = "Update error " . $e->getMessage();
   include '../php/wrongentry.php';
   exit(0);
}
include '../php/head.php';
?>
<body>
<?php
print <<<EOT
<h1>Mailing list $h1 OK</h1>
<p>The mailing list for {$mymailing->display_name()} as {$mymailing->display_description()} has been $h1 successfully.</p>

EOT;
?>
<p>Please <a href="/admin/index.php">Click here</a> to return to the admin page or
<a href="/admin/mlists.php">here</a> to go back to the previous page.</p>
</body>
</html>
