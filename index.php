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

include 'php/messerr.php';
include 'php/session.php';
include 'php/opendb.php';
include 'php/person.php';
include 'php/role.php';
include 'php/mailing.php';

try {
   opendb();
}
catch (Messerr $e) {
   $Title = $e->Header;
   $mess = $e->getMessage();
   include 'php/generror.php';
   exit(0);
}

$gperson = $grole = $gmailing = null;

if (isset($_GET['person']))  {
   try  {
      $gperson = new Person($_GET['person']);
      $gperson->fetchdetsfromname();
   }
   catch (Messerr $e)  {
      $gperson = null;
   }
}

if (!$gperson && isset($_GET['role']))  {
   try  {
      $grole = new Role($_GET['role']);
      $grole->fetchalias();
   }
   catch (Messerr $e)  {
      $grole = null;
   }
}

if (!$grole && isset($_GET['mailing']))  {
   try  {
      $gmailing = new Mailing($_GET['mailing']);    // Don't do the whole lot because we only use the name
   }
   catch (Messerr $e)  {
      $gmailing = null;
   }
}

if (!$gperson)
   $gperson = new Person('*', '*');
if  (!$grole)
   $grole = new Role('*');
if  (!$gmailing)
   $gmailing = new Mailing('*');

$Title = 'BGA Messaging Create Message';
include 'php/head.php';
?>
<body>
<script language="javascript" src="webfn.js"></script>
<script language="javascript">
function checkform()
{
   var form = document.mform;
   if  (!okname(form.from.value))  {
      alert("Invalid sender name given");
      return false;
   }
   if  (!nonblank(form.email.value)) {
      alert("No email given");
      return false;
   }
   if (form.recip.selectedIndex <= 0)  {
      alert("No recipient given");
      return  false;
   }
   if  (!nonblank(form.subject.value))  {
      alert("No subject");
      return  false;
   }
   if  (!nonblank(form.mess.value))  {
      alert("No message");
      return  false;
   }
   return  true;
}
</script>
<form name="mform" action="msgsend.php" method="post" enctype="application/x-www-form-urlencoded" onsubmit="javascript:return checkform();">
<table cellpadding="5" cellspacing="3" align="left" width="300">
<tr><td><a href="https://www.britgo.org" title="Go to BGA main site"><img src="images/gohead12.gif" width="133" height="47" alt="BGA Logo" border="0" hspace="0" vspace="0"></a></td>
<td><span class="hdr">Send a message to BGA member(s)</span></td>
<tr>
   <td><b>From</td>
   <td><?php print "<input type=\"text\" name=\"from\" size=\"30\" value=\"$username\">"; ?></td>
</tr>
<tr>
   <td><b>Email</td>
   <td><input type="email" name="email" size="30"></td>
</tr>
<tr>
   <td><b>To</b></td>
   <td><select name="recip">
   <option value="">Select recipient</option>
   <option disabled>------</option>
<?php
$roles = Role::get_roles_list();
foreach ($roles as $rl)  {
   $chk = "";
   if ($rl->is_same($grole))
      $chk = ' selected="selected"';
   print <<<EOT
   <option value="Roles:{$rl->formencode()}"$chk>{$rl->display_description()}</option>

EOT;
}
?>
<option disabled>------</option>
<?php
$people = get_person_list();        // Default doesn't include people who don't want to be here
foreach ($people as $pers) {
   $chk = "";
   if ($pers->is_same($gperson))
      $chk = ' selected="selected"';
   print <<<EOT
   <option value="Pers:{$pers->formencode()}"$chk>{$pers->display_name()}</option>

EOT;
}
?>
<option disabled>------</option>
<?php
$mailings = Mailing::get_mailings_list();
foreach ($mailings as $m)  {
   $chk = "";
   if ($m->is_same($gmailing))
      $chk = ' selected="selected"';
   print <<<EOT
   <option value="Mailings:{$m->formencode()}"$chk>{$m->display_description()}</option>

EOT;
}
?>
   </select></td>
</tr>
<tr>
   <td><b>Subject</b></td>
   <td><input type="text" name="subject" size="40"></td>
</tr>
<tr>
   <td>&nbsp;</td><td><textarea name="mess" rows="10" cols="60"></textarea></td>
</tr>
<tr>
   <td align="right"><input type="checkbox" name="sendme" value="sendme" checked="checked"></td>
   <td><b>Send me a copy of this message</b></td>
</tr>
<?php include 'php/sumchallenge.php' ?>
<tr><td align="center"><input type="submit" name="sub" value="Submit"></td>
<td align="right"><a href="admin/index.php"><img src="images/lock.png" width="18" height="26" alt="Lock"></a></td>
</tr>
</table>
</form>
</body>
</html>
