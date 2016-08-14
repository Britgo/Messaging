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
   $my_aliases = $mypers->get_alt_aliases();
   $not_aliases = $mypers->get_alt_aliases(true);
   $allroles = Role::get_personal_roles();            // All of them
   $allmails = Mailing::get_mailings_list();
   $allpeople = get_person_list(true);
}
catch (Messerr $e) {
   $mess = "Open database: " . $e->getMessage();
   include '../php/wrongentry.php';
   exit(0);
}

$Title = "Create new mail system entry";
include '../php/head.php';
?>
<body>
<script language="javascript" src="/webfn.js"></script>
<script language="javascript">
<?php
print "Existing_aliases = new Array();\n";
foreach (array_merge($not_aliases, $my_aliases, $allroles) as $al) {
   $lal = strtolower($al);
   print "Existing_aliases['$lal'] = 1;\n";
}
foreach ($allmails as $al)  {
   $lal = strtolower($al->Name);
   print "Existing_aliases['$lal'] = 1;\n";
}
print "Existing_people = new Array();\n";
foreach ($allpeople as $ap) {
   $ln = strtolower($ap->text_name());
   print "Existing_people[\"$ln\"] = 1;\n";
}
?>
function fillaliases() {
   var fm = document.pform;
   var nam = fm.name;
   var namv = nam.value;
   var malias = fm.malias;
   var a0 = fm.alias0;
   var a1 = fm.alias1;
   var a2 = fm.alias2;
   var re = /^([a-zA-Z]*)( ([a-zA-Z]*))?/;
   var matches = RegExp.exec(namv);
   if  (!matches)  {
      malias.value = "";
      a0.value = "";
      a1.value = "";
      a2.value = "";
   }
   var first = matches[1];
   var last = matches[3];
   malias.value = first + last.substr(0,1);
   if (last.length != 0)
      a0.value = first + '.' + last;
   else
      a0.value = first;
   a1.value = first + last;
   a2.value = first.substr(0,1) + last; 
}
function checkform()  {
   var fm = document.pform;
   var sname = String.toLowerCase(fm.name.value);
   if (!nonblank(sname))  {
      alert("No name given");
      return false;
   }
   if (!okname(sname))  {
      alert("Invalid name");
      return false;
   }
   if (Existing_people[sname])  {
      alert("Already have " + fm.name.value);
      return false;
   }
   var smalias = String.toLowerCase(fm.malias.value);
   if (!okalias(smalias)  {
      alert("Invalid main alias field");
      return false;
   }
   if (Existing_aliases[smalias)  {
      alert("Clash of alias " + fm.malias.value);
      return  false;
   }
   if (!nonblank(fm.email.value))  {
      alert("No email address specified");
      return false;
   }   
   for (var n = 0;  n < 12;  n++)  {
      var ael = fm.elements['alias' + n].value;
      if (nonblank(ael))  {
         if (!okalias(ael)) {
            alert("Bad format alias - '" + ael + "'");
            return  false;
         }
         var lael = String.toLowerCase(ael);
         if  (Existing_aliases[lael])  {
            alert(ael + ' clashes with existing alias');
            return  false;
         }
      }
   }
   
   if (fm.passw1.value != fm.passw2.value)  {
      alert("Passwords do not match");
      return  false;
   }
   
   return true;
}
function goBack() {
   window.history.back();
}
</script>
<h1>Creating a new messaging system user</h1>
<form name="pform" action="/admin/newperson2.php" method="post" enctype="application/x-www-form-urlencoded" onsubmit="javascript:return checkform();">
<table cellpadding="3" cellspacing="4">
<tr>
   <td><b>Name</b></td>
   <td><input type="text" name="name" size="30" oninput="fillaliases(event)"></td>
</tr>
<tr>
   <td><b>Main alias</b></td>
   <td><input type="text" name="malias" size="20"></td>
</tr>
<tr>
   <td><b>Email address</b></td>
   <td><input type="text" name="email" size="30"></td>
</tr>
</table>
<h2>Other aliases</h2>
<table cellpadding="3" cellspacing="5">
<?php
for ($row = 0; $row < 3;  $row++)  {
   print "<tr>\n";
   for ($col = 0;  $col < 4;  $col++)  {
      $n = $row * 4 + $col;
      print <<<EOT
   <td><input type="text" name="alias$n" size="16">

EOT;
   }
   print "</tr>\n";
}
?>
</table>
<table cellpadding="3" cellspacing="4">
<tr>
<td align="right"><input type="checkbox" name="dispok"></td>
<td>Select if your name (not email) may appear on the drop-down list.</td>
</tr>
<tr>
   <td>Gender (for proper address only)</td>
   <td><select name="gender"><option value="U">(Not given)</option><option value="M" selected="selected">Male</option><option value="F">Female</option></select></td>
</tr>
<tr>
   <td><input type="submit" name="canc" value="Cancel" onclick="goBack();"></td>
   <td><input type="submit" name="subm" value="Register"></td>
</tr>
</table>
</form>
</body>
</html>
