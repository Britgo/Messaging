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
include 'php/opendb.php';
include 'php/person.php';
include 'php/role.php';
include 'php/mailing.php';

if (!isset($_POST['from']) || !isset($_POST['email']) || !isset($_POST['recip']))  {
   $mess = 'Form fields missing';
   include 'php/wrongentry.php';
   exit(0);
}

include 'php/sumcheck.php';

$from = $_POST['from'];
$semail = $_POST['email'];
$recip = $_POST['recip'];
$subj = $_POST['subject'];
$mess = $_POST['mess'];
$copyme = isset($_POST['sendme']);

try {
   opendb();
}
catch (Messerr $e) {
   $mess = "Open database: " . $e->getMessage();
   include 'php/wrongentry.php';
   exit(0);
}

$rpieces = split(':', $recip, 2);
if (count($rpieces) != 2)  {
   $mess = "Cannot parse $recip";
   include 'php/wrongentry.php';
   exit(0);
}

$rtype = $rpieces[0];
$rto = $rpieces[1];

switch  ($rtype)  {
   default:
      $mess = "Unknown recipient type $rtype";
      include 'php/wrongentry.php';
      exit(0);
   case  'Pers':
      $capac = "";
      $dest = $rto;
      try  {
         $pto = new Person($rto);
         $sending_to = $pto->fetchdetsfromname()->Email;
         break;
      }
      catch  (Messerr $e)  {
         $mess = "Error finding person - $e->getMessage()";
         include 'php/wrongentry.php';
         exit(0);
      }
   case  'Roles':
      $capac = "In your capacity as BGA $rto";
      $dest = "The BGA $rto";
      try  {
         $roleto = new Role($rto);
         $roleto->fatchalias()->fetchperson();
         $sending_to = $roleto->Email;       
      }
      catch  (Messerr $e)  {
         $mess = "Error finding role - $e->getMessage()";
         include 'php/wrongentry.php';
         exit(0);
      }
   case  'Mailings':
      $sending_to = "$rto@britgo.org";
      try  {
         $mto = new Mailing($rto);
         $mto->fetchdescr();
         $capac = "As you are a member of the {$mto->text_description()}";
         $dest = "The BGA $mto->text_description()}";
      }
      catch  (Messerr $e)  {
         $mess = "Error finding mailing list - $e->getMessage()";
         include 'php/wrongentry.php';
         exit(0);
      }
      break;
}

//  Send the message

$fh = popen("REPLYTO='$semail' mail -s '$subj' $sending_to", "w");
$message = <<<EOT
$from (email $semail) has sent you a message using the online message system.

EOT;
fwrite($fh, $message);
if  (strlen($capac) != 0)  {
   $message = <<<EOT

This message was sent to you $capac.


EOT;
   fwrite($fh, $message);
}

$message = <<<EOT
The text of the message reads:

$mess

EOT;
fwrite($th, $message);
pclose($fh);

if ($copyme)  {
   $fh = popen("REPLYTO=please_do_not_reply@britgo.org mail -s 'Copy: $subj' $semail", "w");
   $message = <<<EOT
You sent the following message to $dest

The subject was $subj.

The text of your message reads:

$mess

EOT;
   fwrite($fh, $message);
   pclose($fh);
}
$Title = "Message sent";
include 'php/head.php';
$qdest = htmlspecialchars($dest);
?>
<body>
<h1>Message sent</h1>
<p>Your message to
<?php print " $qdest "; ?>
was sent.</p>
<?php
if ($copyme)
   print <<<EOT
<p>A copy has been sent back to you.</p>

EOT;
?>
<p>Please <a href="javascript:close_window();">click here</a> to close this window.</p>
</body>
</html>