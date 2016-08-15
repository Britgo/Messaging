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

if (!isset($_POST['alias']))  {
   $ness = "Not from form???";
   include '../php/wrongentry.php';
   exit(0);
}
$malias = $_POST['alias'];
$email = $_POST['email'];
$disp = isset($_POST['dispok']);
$gender = $_POST['gender'];

try {
   opendb();
   $mypers = new Person($malias, "", true);
   $mypers->fetchdetsfromalias();
   $mypers->Email = $email;
   $mypers->Gender = $gender;
   $mypers->Display = $disp;
   $mypers->update();
   $Genpw = false;
   $Newpw = false;
   if  ($mypers->is_admin())  {
      $npw = $_POST['passw1'];
      $cpw = $mypers->get_passwd();
      if ($npw != $cpw)  {
         $Newpw = true;
         if  ($npw == "")  {
            $npw = generate_password();
            $Genpw = true;
         }
         $mypers->reset_password($npw);
      }
   }
}
catch (Messerr $e)  {
   $mess = "Update error " . $e->getMessage();
   include '../php/wrongentry.php';
   exit(0);
}
if  ($Newpw)  {
   $fh = popen("REPLYTO=please_do_not_reply@britgo.org mail -s 'Your message system password was reset' $email", "w");
   fwrite($fh, "Dear {$mypers->text_name()}\n\n");
   if ($Genpw)
      fwrite($fh, "Please note that your password has been automatically generated and set to\n\n");
   else
      fwrite($fh, "Please note that your password has been set to\n\n");
   $message = <<<EOT
      $npw

If you do not like that you can reset that as often as you like until you log out.

EOT;
   fwrite($fh, $message);
   pclose($fh);
}
$Title = "Mailing entry updated OK";
include '../php/head.php';
?>
<body>
<h1>Mail entry updated OK</h1>
<?php
print <<<EOT
<p>The mail entry for {$mypers->display_name()} has been updated successfully.</p>

EOT;
?>
<p>Please <a href="/admin/index.php">Click here</a> to return to the admin page or
<a href="/admin/people.php">here</a> to go back to the previous page.</p>
</body>
</html>
