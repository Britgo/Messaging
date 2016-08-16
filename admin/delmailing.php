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

if  (!isset($_GET['mlist']))  {
   $mess = "No mailing given";
   include '../php/wrongentry';
   exit(0);
}

$delmailing = $_GET['mlist'];

try {
   opendb();
   $subjmail = new Mailing($delmailing);
   $subjmail->fetchdescr()->delete();
}
catch (Messerr $e)  {
   $mess = "Delete error " . $e->getMessage();
   include '../php/wrongentry.php';
   exit(0);
}
$Title = "Deleted OK";
include '../php/head.php';
?>
<body onload="javascript:window.location = document.referrer;">
<h1>Deleted OK</h1>
<?php
print <<<EOT
<p>The mail entry for mailing list {$subjmail->display_name()} has been deleted successfully.</p>

EOT;
?>
<p>Please <a href="/admin/index.php">Click here</a> to return to the admin page or
<a href="/admin/mailings.php">here</a> to go back to the previous page.</p>
</body>
</html>
