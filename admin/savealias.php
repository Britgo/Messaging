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
   $Title = $e->Header;
   $mess = $e->getMessage();
   include '../php/generror.php';
   exit(0);
}

$temp_outfile = "/tmp/aliasrewrite";
$Alias_dest = "/srv/britgo.org/config/aliases";

if (!($outfile = fopen($temp_outfile, 'x')))  {
   $Title = "Clash on temp file";
   $mess = "output file exists";
   include '../php/generror.php';
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

teamcaptains:   |/var/www/onlineleague/captmail.pl
unpaidcaptains: |/var/www/onlineleague/captmail.pl -u

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

$docloc = 'https://message.britgo.org/cgi-bin/copyfile' . '?from=' . urlencode($temp_outfile) . '&to=' . urlencode($Alias_dest);
header("Location: $docloc");
?>