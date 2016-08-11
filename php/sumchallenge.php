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

$r1 = rand(1,10);
$r2 = rand(1,10);
print <<<EOT
<tr>
	<td>(Anti-spam) Please answer this sum as a <b>word</b> $r1 + $r2 =</td>
	<td><input type="hidden" name="r1" value="$r1" /><input type="hidden" name="r2" value="$r2" /><input type="text" name="asp" size="20" /></td>
</tr>

EOT;
?>