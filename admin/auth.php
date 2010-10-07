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

// This sets up the allowed users for the administration page.
$auth = Array();

// (Add multiple lines of this form to allow multiple users to access the
//   administration page.)
$auth['username'] = 'password';
$auth['anotheruser'] = 'supersecret';

// Don't cache any administration pages.
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

if (!isset($_SERVER['PHP_AUTH_USER']) ||
	!isset($auth[$_SERVER['PHP_AUTH_USER']]) ||
	$auth[$_SERVER['PHP_AUTH_USER']] != $_SERVER['PHP_AUTH_PW']) {
    header('WWW-Authenticate: Basic realm="SLVSCAV Administration"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Sorry, you must log in.';
    exit;
}

// Load all users
require_once(dirname(__FILE__).'/../lib/login.php');
$lines = preg_split('/[\r\n]+/mis', file_get_contents($dataDir.'/schoolid.csv'), 0, PREG_SPLIT_NO_EMPTY);
array_shift($lines); // Ignore the first line.
$users = Array();
foreach($lines as $line) {
	$parts = explode(',', trim($line));
	if (isUser($parts[1])) {
		$nameBits = explode(' ', $parts[4]);
		$user = Array('gender' => $parts[0],
			'id' => $parts[1],
			'grade' => $parts[2],
			'last' => $parts[3],
			'first' => $nameBits[0],
			'middle' => $nameBits[1],
			'file' => userFile($parts[1]));
		if (is_file($dataDir.'/users/'.$user['id'].'_answers.dat')) {
			$user['answers'] = unserialize(file_get_contents($dataDir.'/users/'.$user['id'].'_answers.dat'));
		}
		$users[] = $user;
	}
}

// Load the scores
if (is_file($dataDir.'/scores.dat')) {
	$scores = unserialize(file_get_contents($dataDir.'/scores.dat'));
} else {
	$scores = Array();
}

function saveScores() {
	global $dataDir, $scores;
	file_put_contents($dataDir.'/scores.dat', serialize($scores));
}

?>
