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

function generate_password() {
	$passw = "";
	$poss = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	$lp = strlen($poss) - 1;
	for ($i = 0; $i < 10; $i++)
		$passw = $passw . $poss[rand(0,$lp)];
	return  $passw;
}

if (!isset($_POST['email']) || !isset($_POST['passw1']))  {
   $ness = "Not from form???";
   include '../php/wrongentry.php';
   exit(0);
}

$email = $_POST['email'];
$disp = isset($_POST['dispok']);
$gender = $_POST['gender'];
$npw = $_POST['passw1'];

$Generated = false;
if ($npw == "")  {
   $Generated = true;
   $npw = generate_password();
}

$naliases = array();
for ($n = 0;  $n < 12; $n++)  {
   $v = $_POST["alias$n"];
   if  ($v != "")  {
      $lv = strtolower($v);
      $naliases[$lv] = $v;
   }
}

try {
   opendb();
   $mypers = new Person($userid, "", true);
   $mypers->fetchdetsfromalias();
   $opw = $mypers->get_passwd();
   $mypers->Email = $email;
   $mypers->Display = $disp;
   $mypers->Gender = $gender;
   $mypers->update();
   if ($npw != $opw)
      $mypers->reset_password($npw);
   $current_aliases = $mypers->get_alt_aliases();
   if (count(array_diff($current_aliases, $naliases)) != 0  || count(array_diff($naliases, $current_aliases)) != 0)
      $mypers->replace_aliases($naliases);
}
catch (Messerr $e)  {
   $mess = "Update error " . $e->getMessage();
   include '../php/wrongentry.php';
   exit(0);
}

if  ($Generated)  {
   $fh = popen("REPLYTO=please_do_not_reply@britgo.org mail -s 'Your message system password was reset' $email", "w");
   $message = <<<EOT
Please note that a password on the message system was generated and set to $npw

If you do not like that you can reset that as often as you like until you log out.

EOT;
   fwrite($fh, $message);
   pclose($fh);
}

$Title = "User Preferences updated OK";
include '../php/head.php';
?>
<body>
<h1>User profile updated OK</h1>
<p>Your user profile has been updated OK.</p>
<?php
if ($Generated) {
   print <<<EOT
<p>A new password has been generated and sent to you by email.</p>

EOT;
}
?>
<p>Please <a href="/admin/index.php">Click here</a> to return to the admin page.</p>
</body>
</html>
