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

$dataDir = dirname(__FILE__).'/../data';
$challengeDir = dirname(__FILE__).'/../challenges';

function h($string) {
	return htmlentities($string);
}

function userFile($id) {
	global $dataDir;
	return $dataDir.'/users/'.$id.'.dat';
}

function getStudentInfo($id) {
	global $dataDir;
	$lines = preg_split('/[\r\n]+/mis', file_get_contents($dataDir.'/schoolid.csv'), 0, PREG_SPLIT_NO_EMPTY);
	array_shift($lines); // Ignore the first line.
	foreach($lines as $line) {
		$parts = explode(',', trim($line));
		if ($parts[1] == $id) {
			$nameBits = explode(' ', $parts[4]);
			return Array('gender' => $parts[0],
				'id' => $parts[1],
				'grade' => $parts[2],
				'last' => $parts[3],
				'first' => $nameBits[0],
				'middle' => $nameBits[1],
				'file' => userFile($parts[1]));
		}
	}
	return Array();
}

function idIsSafe($id) {
	return ((strpos($id, '..') === false) && (strpos($id, '/') === false));
}

function isUser($id) {
	return (idIsSafe($id) && is_file(userFile($id)));
}

function userLogin() {
	global $user, $dataDir;
	session_start();
	
	if (isset($_REQUEST['schoolId'])) {
		$schoolId = $_REQUEST['schoolId'];
		$password = $_REQUEST['password'];
		$_SESSION['schoolId'] = $schoolId;
		$_SESSION['password'] = $password;
	} else {
		$schoolId = $_SESSION['schoolId'];
		$password = $_SESSION['password'];
	}
	
	if (!isUser($schoolId)) {
		$user = false;
		return false;
	}
	
	$userFile = $dataDir.'/users/'.$schoolId.'.dat';
	$account = unserialize(file_get_contents($userFile));
	if ($account['password'] == md5($password)) {
		$studentInfo = array_merge($account, getStudentInfo($schoolId));
		$user = $studentInfo;
	} else {
		$user = false;
	}
	return $user;
}

function getChallengeData($challengeId) {
	global $challengeDir;
	
	$dataFile = preg_split('/[\r\n]+/mis', file_get_contents($challengeDir.'/'.$challengeId.'/data.txt'), 0, PREG_SPLIT_NO_EMPTY);
	
	$data = Array();
	$data['title'] = trim(array_shift($dataFile));
	$data['subtitle'] = trim(array_shift($dataFile));
		if ($data['subtitle'] == 'none') { $data['subtitle'] = ''; }
	$data['points'] = (integer)(trim(array_shift($dataFile)));
	$data['end'] = trim(array_shift($dataFile));
	$data['questions'] = Array();
	
	while($questionType = array_shift($dataFile)) {
		$questionType = strtolower(trim($questionType));
		
		$question = Array();
		$question['type'] = $questionType;
		$question['points'] = (integer)(trim(array_shift($dataFile)));
		
		if ($questionType == 'dropdown') {
			$numOptions = (integer)(trim(array_shift($dataFile)));
			$options = Array();
			for($i = 0; $i < $numOptions; $i++) {
				$options[] = trim(array_shift($dataFile));
			}
			$question['options'] = $options;
		} elseif ($questionType == 'file') {
			$question['types'] = array_map('strtolower', array_map('trim',
				explode(',', trim(array_shift($dataFile)))
				));
		} elseif ($questionType == 'random_old_answer') {
			$question['challenge'] = trim(array_shift($dataFile));
			$question['minPoints'] = (integer)(trim(array_shift($dataFile)));
			$numQuestions = (integer)(trim(array_shift($dataFile)));
			$questions = Array();
			for($i = 0; $i < $numQuestions; $i++) {
				$questions[] = trim(array_shift($dataFile));
			}
			$question['questions'] = $questions;
		} elseif ($questionType == 'text') {
			// Do nothing - no text options
		} elseif ($questionType == 'longtext') {
			$question['default'] = trim(array_shift($dataFile));
		} elseif ($questionType == 'referral') {
			// Do nothing - no referral options
		}
		$data['questions'][] = $question;
	}
	
	return $data;
}



// From http://us2.php.net/stripslashes
function stripslashes_if_gpc_magic_quotes($string) {
	if(@get_magic_quotes_gpc()) {
		return stripslashes($string);
	} else {
		return $string;
	}
}

function ordinal_suffix($value){
	// Function written by Marcus L. Griswold (vujsa)
	// Can be found at http://www.handyphp.com
	if(substr($value, -2, 2) == 11 || substr($value, -2, 2) == 12 || substr($value, -2, 2) == 13){
		$suffix = "th";
	} else if (substr($value, -1, 1) == 1){
		$suffix = "st";
	} else if (substr($value, -1, 1) == 2){
		$suffix = "nd";
	} else if (substr($value, -1, 1) == 3){
		$suffix = "rd";
	} else {
		$suffix = "th";
	}
	return $value . $suffix;
}

?>
