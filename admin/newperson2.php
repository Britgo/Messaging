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
include '../php/genpasswd.php';

if (!isset($_POST['name']) || !isset($_POST['malias']))  {
   $ness = "Not from form???";
   include '../php/wrongentry.php';
   exit(0);
}
$name = $_POST['name'];
$malias = $_POST['malias'];
$email = $_POST['email'];
$disp = isset($_POST['dispok']);
$gender = $_POST['gender'];

$naliases = array();
for ($n = 0;  $n < 12; $n++)  {
   $v = $_POST["alias$n"];
   if  ($v != "")  {
      $lv = strtolower($v);
      $naliases[$lv] = $v;
   }
}

if (isset($naliases[strtolower($malias)]))
   unset($naliases[strtolower($malias)]);

try {
   opendb();
   $mypers = new Person($name);
   $mypers->Mainalias = $malias;
   $mypers->Email = $email;
   $mypers->Gender = $gender;
   $mypers->Display = $disp;
   $mypers->create();
   $mypers->replace_aliases($naliases);
}
catch (Messerr $e)  {
   $mess = "Update error " . $e->getMessage();
   include '../php/wrongentry.php';
   exit(0);
}
$Title = "Mailing entry created OK";
include '../php/head.php';
?>
<body>
<h1>Mail entry created OK</h1>
<?php
print <<<EOT
<p>The mail entry for {$mypers->display_name()} has been created successfully First={$mypers->First} Last={$mypers->Last}.</p>

EOT;
?>
<p>Please <a href="/admin/index.php">Click here</a> to return to the admin page or
<a href="javascript:window.location = document.referrer;">here</a> to go back to the previous page.</p>
</body>
</html>
