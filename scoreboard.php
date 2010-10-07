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

$require_login = true;
$title = 'Scoreboard';
$tab = 'scoreboard';
include 'header.php';

if (filemtime($dataDir.'/scores.dat') < strtotime($lastChallengeEnd)) {
	echo '<span class="smallText serif">Sorry, we\'re still tallying scores up. The scoreboard will be available shortly.</span>';
	echo '<div class="clear"></div><div style="height: 30px;"></div><div class="clear"></div>';
	include 'footer.php';
	die();
}

$scores = unserialize(file_get_contents($dataDir.'/scores.dat'));
$scoreboard = Array();
$scoreboard['individual'] = Array();
$scoreboard['class'] = Array(
	9 => Array('name' => 'Freshmen', 'last' => 'Freshmen'),
	10 => Array('name' => 'Sophomores', 'last' => 'Sophomores'),
	11 => Array('name' => 'Juniors', 'last' => 'Juniors'),
	12 => Array('name' => 'Seniors', 'last' => 'Seniors'),
);

// assignRanks assigns a rank to each contestant in the set, as well as the
//  amount they have moved in the last round, if relevant.
function assignRanks($input) {
	uasort($input, "sortByScore"); // Use sortByScore to sort everyone.
	$oldScore = -1; // Store the last player's score
	$rankOn = 0;    // The ranking we're currently on. Incremented manually,
	                //  because there may be a tie.
	$nextRank = 1;  // The next ranking that we should use if there is no tie.
	                //  We always increment this, even in a tie, so we can have
	                //  two first places, with the next highest score being
	                //  given third place.
	
	foreach($input as $key => $value) {
		// If the score is different than the last player's, it's not a tie.
		if ($value['score'] != $oldScore) {
			$rankOn = $nextRank;
		}
		
		// Save this score to detect ties, and increment the next rank.
		$oldScore = $value['score'];
		$nextRank++;
		
		// If there was a prior round, calculate how much the player's rank
		//  changed.
		if (isset($input[$key]['rank'])) {
			// This subtraction is counterintuitive, but we say someone moved
			//  "up" (positively) if their rank decreases (from 2nd to 1st).
			$input[$key]['move'] = $input[$key]['rank'] - $rankOn;
			
			if ($input[$key]['move'] == 0) {
				// If there was no movement, remove this key.
				unset($input[$key]['move']);
			}
		}
		
		// Finally, assign the rank.
		$input[$key]['rank'] = $rankOn;
	}
	return $input;
}

// sortByScore is used as a custom comparison operator in assignRanks() to sort
//  by score, then by last name, and finally by first name.
function sortByScore($a, $b) {
	// Pull out scores
	$aC = $a['score'];
	$bC = $b['score'];
	
	// If the score is equal,
	if ($aC == $bC) {
		// try comparing last names.
		if (strcmp($a['last'], $b['last']) == 0) {
			// If they're equal, compare first names.
			return strcmp($a['first'], $b['first']);
		}
		// Last names differ, so use those for sorting.
		return strcmp($a['last'], $b['last']);
	}
	// Scores are different, so we can sort by that.
	return ($aC > $bC) ? -1 : 1;
}

for($challengeNumber = 1; $challengeNumber <= $challengeTotal; $challengeNumber++) {
	$challengeBits = explode('-', $challenges[$challengeNumber-1]);
	if ($challengeId == trim($challengeBits[0])) {
		// We reached the current challenge, stop here.
		break;
	}
	
	$challengeOn = trim($challengeBits[0]);
	
	if (isset($scores[$challengeOn])) {
		foreach($scores[$challengeOn] as $userId => $score) {
			$userInfo = getStudentInfo($userId);
			if ($userInfo['grade'] != 0) {
				$scoreboard['individual'][$userId]['name'] = $userInfo['first'].' '.$userInfo['last'];
				$scoreboard['individual'][$userId]['first'] = $userInfo['first'];
				$scoreboard['individual'][$userId]['last'] = $userInfo['last'];
				$scoreboard['individual'][$userId]['score'] += $score;
				if (isset($scoreboard['class'][$userInfo['grade']])) {
					$scoreboard['class'][$userInfo['grade']]['score'] += $score;
				}
			}
		}
	}
	
	$scoreboard['individual'] = assignRanks($scoreboard['individual']);
	$scoreboard['class'] = assignRanks($scoreboard['class']);
}

if (!$challengeId) {
	echo '<div class="grid_12"><span style="font-weight:bold">';
	echo 'Final scores:';
	echo '</span></div><div class="clear"></div>';
}

foreach($scoreboard as $scoreboardType => $scoreboardRows) {
?>
<div class="grid_6">
	<span style="font-weight:bold"><?=h($scoreboardType)?></span><br />
<? $firstRow = true;
foreach($scoreboardRows as $rowUser => $row) { ?>
	<div class="scoreboardRow<? if ($firstRow) { echo ' first'; $firstRow = false; } ?><?=($user && ($rowUser == $user['id'])) ? ' me' : '' ?>">
		<div class="grid_1 alpha">
			<? if (isset($row['move'])) {
				if ($row['move'] > 0) {
					echo '<div class="movement green">'.abs($row['move']).'</div>';
				} else {
					echo '<div class="movement red">'.abs($row['move']).'</div>';
				}
			} ?>
			<div class="rank"><?=h($row['rank'])?></div>
		</div>
		<div class="grid_3"><?=h($row['name'])?></div>
		<div class="grid_2 omega alignRight"><?=h($row['score'])?></div>
		<div class="clear"></div>
	</div>
<? } ?>
</div>
<? } ?>

<div class="clear"></div>
<div style="height: 30px;"></div>
<div class="clear"></div>
<?
include 'footer.php';
?>
