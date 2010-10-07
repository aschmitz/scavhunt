<?
/*
scavhunt - An online scavenger hunt
Copyright 2010, Andy Schmitz

This program is free software: you can redistribute it and/or modify it under   
the terms of the GNU Affero General Public License as published by the Free
Software Foundation, either version 3 of the License, or (at your option) any   
later version.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS   
FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License (in the    
LICENSE file) for more details.
*/

include 'auth.php';

echo 'Registered users ('.count($users).'):<br /><br />';

function sortUserByRegDate($a, $b) {
	$aC = filectime($a['file']);
	$bC = filectime($b['file']);
	if ($aC == $bC) {
		return 0;
	}
	return ($aC < $bC) ? -1 : 1;
}

usort($users, "sortUserByRegDate");

foreach($users as $user) { ?>
<?=h($user['last'].', '.$user['first'])?><br />
<? } ?>
