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

// Look through the list of challenges, see if this is before the current challenge.
$challenges = preg_split('/[\r\n]+/mis', file_get_contents(dirname(__FILE__).'/../data/challenges.txt'), 0, PREG_SPLIT_NO_EMPTY);
$challengeTotal = count($challenges);
$challengeId = false;

for($challengeNumber = 1; $challengeNumber <= $challengeTotal; $challengeNumber++) {
	$challengeBits = explode('-', $challenges[$challengeNumber-1]);
	if (time() < strtotime(trim($challengeBits[1]))) {
		// If this challenge hasn't passed, it must be the current challenge.
		die('Sorry.');
	}
	
	if (($_REQUEST['image'] == 'answers') && ($_REQUEST['id'] == trim($challengeBits[0]))) {
		header('Content-type: image/jpeg');
		echo file_get_contents(dirname(__FILE__).'/../data/answers/'.trim($challengeBits[0]).'.jpg');
		die();
	}
}
?>
