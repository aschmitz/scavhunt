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

$challengeId = $_REQUEST['challenge'];

if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
	foreach($_REQUEST['newScores'] as $userId => $newScore) {
		$scores[$challengeId][$userId] = $newScore;
	}
	saveScores();
}

function sortUserBySubmitDate($a, $b) {
	global $dataDir;
	$aF = $dataDir.'/users/'.$a['id'].'_answers.dat';
	$bF = $dataDir.'/users/'.$b['id'].'_answers.dat';
	
	if (!is_file($aF) || !is_file($bF)) {
		return 0;
	}
	
	$aC = @filemtime($aF);
	$bC = @filemtime($bF);
	if ($aC == $bC) {
		return 0;
	}
	return ($aC < $bC) ? -1 : 1;
}

function printNameFromId($id) {
	global $users;
	foreach($users as $user) {
		if ($user['id'] == $id) {
			return $user['first'].' '.$user['last'];
		}
	}
	return false;
}

echo '<form method="POST">';
$respondents = 0;
uasort($users, 'sortUserBySubmitDate');

foreach($users as $user) {
	// Load the user's old answers.
	$answerFile = $dataDir.'/users/'.$user['id'].'_answers.dat';
	if (is_file($answerFile)) {
		$answers = unserialize(file_get_contents($answerFile));
		if (isset($answers[$challengeId])) {
			$respondents++;
			
			echo '<strong>'.h($user['last'].', '.$user['first']).'</strong><br /><table border="1">';
			
			foreach($answers[$challengeId] as $questionNumber => $answer) {
				if (isset($answers[$challengeId.'_assigned'])) {
					// This was a multi-answerable question, currently just matching students to prior answers
					$assigned = $answers[$challengeId.'_assigned'][$questionNumber];
					
					if (is_array($answer)) { $answer = $answer[0]; }
					
					echo '<tr><th>'.printNameFromId($assigned).'</th><td>'.nl2br(h(stripslashes_if_gpc_magic_quotes($answer))).'</td></tr>';
				} else {
					// Just a standard single-shot question
					echo '<tr><th>'.chr(65 + $questionNumber).'</th><td>';
					if (substr($answer, 0, 5) == 'file:') {
						$parts = explode(':', $answer);
						$type = $parts[1];
						echo '<a target="_blank" href="getupload.php?challenge='.
							urlencode($_REQUEST['challenge']).'&user='.
							urlencode($user['id']).'&num='.
							urlencode($questionNumber).'&type='.
							urlencode($type).'">'.h($type).' file</a>';
					} else {
						echo nl2br(h(stripslashes_if_gpc_magic_quotes($answer)));
					}
					echo '</td></tr>';
				}
			}
			echo '</table>';
			echo 'Score: <input type="text" size="5" name="newScores['.$user['id'].']" value="'.$scores[$challengeId][$user['id']].'" /><br /><br />';
		}
	}
}

echo '<input type="submit" value="Save" /></form><br />('.$respondents.' respondents)';
?>
