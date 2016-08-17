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
   $peopledict = get_alias_dict($people);
   $roles = Role::get_roles_list($peopledict);
   $mailings = Mailing::get_mailings_list();
}
catch  (Messerr $e)  {
   $mess = "Could not open databse - $e";
   include '../php/wrongentry.php';
   exit(0);
}

$temp_outfile = "/tmp/aliasrewrite";
$Alias_dest = "/srv/britgo.org/configa/aliases";

if (!($outfile = fopen($temp_outfile, 'x')))  {
   $mess = "output file exists";
   include '../php/wrongentry.php';
   exit(0);
}

$nextmsg = <<<EOT
# System Alias file
# Standard aliases

mailer-daemon: postmaster
postmaster: web-master
nobody: root
hostmaster: root
usenet: root
news: root
www: root
ftp: root
abuse: root
noc: root
security: root

# Main aliases


EOT;

fwrite($outfile, $nextmsg);

//  OK so now let's have the main aliases

foreach ($people as $pers) {
   $ma = strtolower($pers->Mainalias);
   $nextmsg = <<<EOT
# {$pers->text_name()}
$ma: {$pers->Email}

EOT;
   fwrite($outfile, $nextmsg);
}

$nextmsg = <<<EOT

# Other aliases


EOT;

fwrite($outfile, $nextmsg);

foreach ($people as $pers) {
   $alts = $pers->get_alt_aliases();
   $ma = strtolower($pers->Mainalias);
   foreach ($alts as $alt)  {
      $lalt = strtolower($alt);
      $nextmsg = <<<EOT
$lalt: $ma

EOT;
      fwrite($outfile, $nextmsg);
   }
}

$nextmsg = <<<EOT

# Roles


EOT;
fwrite($outfile, $nextmsg);

foreach ($roles as $role)  {
   $ln = strtolower($role->Rolename);
   $la = strtolower($role->Aliasname);
   $nextmsg = <<<EOT
$ln: $la

EOT;
   fwrite($outfile, $nextmsg);
}

$nextmsg = <<<EOT

# Mailing lists


EOT;
fwrite($outfile, $nextmsg);

foreach ($mailings as $ml)  {
   $ln = strtolower($ml->Name);
   $destlist = array();
   foreach ($ml->get_role_membs() as $rm)
      array_push($destlist, strtolower($rm));
   foreach ($ml->get_name_membs($peopledict) as $p)
      array_push($destlist, strtolower($p->Mainalias));
   if  (count($destlist) == 0)
      continue;
   $l = join(', ', $destlist);
   $nextmsg = <<<EOT
# {$ml->Description}
$ln: $l

EOT;
   fwrite($outfile, $nextmsg);
}

fclose($outfile);

try  {
   $pid = pcntl_fork();
   if  ($pid < 0)
      throw new Messerr("Cannot fork");
   if  ($pid == 0)  {
      pcntl_exec("/srv/britgo.org/public/cgi-bin/cpalias", array($temp_outfile, $Alias_dest));
      exit(255);
   }
   if  (pcntl_waitpid($pid, $status) < 0)
      throw new Messerr("Run proc failed");
   if  (!pcntl_wifexited($status))
      throw new Messerr("Cp util crashed");
   if  (pcntl_wexitstatus($status) != 0)
      throw new Messerr("Cp program failed");
}
catch  (Messerr $e)  {
   unlink($temp_outfile);
   $mess = $e->getMessage();
   include "../php/wrongentry.php";
   exit(0);        
}
finally  {
    unlink($temp_outfile);
}
$Title = "Alias file regenerated OK";
include '../php/head.php';
?>
<body>
<h1>Alias file regenerated OK</h1>
<p>The alias file for the email system has been updated OK.</p>
<p>Please <a href="/admin/index.php">Click here</a> to return to the admin page.</p>
</body>
</html>
