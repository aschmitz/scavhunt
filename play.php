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
$title = 'Challenge';
$tab = 'current';
$noHeader = true;
include 'header.php';

// Contest organizer: Change these lines (to "true" or "false") if you want, but don't comment them out.
$showGender = false;
$showGrade = false;

if (!$challengeId) {
	// If there's no challenge any more, redirect to the scoreboard.
	header('Location: /scoreboard.php');
	die();
}

// Load the current challenge
$challenge = getChallengeData($challengeId);

// Load the user's old answers.
$answerFile = $dataDir.'/users/'.$user['id'].'_answers.dat';
if (is_file($answerFile)) {
	$answers = unserialize(file_get_contents($answerFile));
} else {
	$answers = Array();
}

if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
	session_start();
	
	if ($_REQUEST['answers_id'] != $challengeId) {
		$_SESSION['error'] = 'Sorry, it looks like the time to submit your answers for that challenge has ended.';
		header('Location: /play.php', true, 303);
		die();
	}
	
	if (isset($answers[$challengeId])) {
		if ($challenge['questions'][0]['type'] != 'random_old_answer') {
			$_SESSION['error'] = 'Sorry, it looks you\'ve already submitted answers to this question, you can\'t submit answers more than once. Check back when we drop the next challenge to see your score.';
			header('Location: /play.php', true, 303);
			die();
		} else {
			// This is a repeatable question
			$answers[$challengeId][] = $_REQUEST['answers'];
		}
	} else {
		if ($challenge['questions'][0]['type'] != 'random_old_answer') {
			// Put in the user's [only] answers.
			$answers[$challengeId] = $_REQUEST['answers'];
		} else {
			// This is a repeatable question
			$answers[$challengeId][] = $_REQUEST['answers'];
		}
	}
	
	// Uploaded files
	if (is_array($_FILES['answers']['name'])) {
		// Structure is $_FILES['answers']['name'][number]
		foreach($_FILES['answers']['error'] as $fileNumber => $fileError) {
			if ($fileError == UPLOAD_ERR_OK) {
				// Check for a proper file type
				$origName = $_FILES['answers']['name'][$fileNumber];
				$fileType = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
				$allowedTypes = $challenge['questions'][$fileNumber]['types'];
				if (!in_array($fileType, $allowedTypes)) {
					$_SESSION['error'] = 'Sorry, we can\'t take that uploaded file. ' .
					'We\'re looking for files with one of these types: ' .
					implode(', ', $allowedTypes).'.';
					header('Location: /play.php', true, 303);
					die();
				}
				
				// So the file's good. Save it.
				$inFile = $_FILES['answers']['tmp_name'][$fileNumber];
				$outFile = $dataDir.'/uploads/'.$challengeId.'_'.$user['id'].'_'.$fileNumber.'.'.$fileType;
				if (!move_uploaded_file($inFile, $outFile)) {
					$_SESSION['error'] = 'Sorry, there was an error copying your uploaded file. Please try again later.';
					header('Location: /play.php', true, 303);
					die();
				}
				
				// Mark that there is, in fact, a file.
				$answers[$challengeId][$fileNumber] = 'file:'.$fileType;
			} elseif ($fileError == UPLOAD_ERR_NO_FILE) {
				// Do nothing, they decided to not upload a file.
			} else {
				$_SESSION['error'] = 'Sorry, there was an error with your uploaded file. Please try again later.';
				header('Location: /play.php', true, 303);
				die();
			}
		}
	}
	
	// Let the user know their answers have been saved
	if ($challenge['questions'][0]['type'] != 'random_old_answer') {
		$_SESSION['flash'] = 'Your answers have been saved. Check back when we drop the next challenge to see your score.';
	} else {
		$_SESSION['flash'] = 'Your answer has been saved. Check back when we drop the next challenge to see your score, or answer more below.';
	}
	
	$answers[$challengeId.'_submit'] = date('r');
	
	// Save the user's answers.
	file_put_contents($answerFile, serialize($answers));
	
	// Redirect back (303 status = no re-post on refresh).
	header('Location: /play.php', true, 303);
	die();
}

function pickUnusedSurvey($fromChallenge, $minPoints) {
	global $user, $challengeId, $dataDir, $answerFile, $answers, $seeking;
	
	$userToFind = false;
	
	// See if we already have a user to find.
	if (count($answers[$challengeId]) != count($answers[$challengeId.'_assigned'])) {
		// We do, just use that.
		$userToFind = $answers[$challengeId.'_assigned'][count($answers[$challengeId.'_assigned']) - 1];
	} else {
		// We need to find a random user that:
		//  1. Is not us
		//  2. Has not already been chosen by us
		//  3. Has enough points on the relevant question
		$scores = unserialize(file_get_contents($dataDir.'/scores.dat'));
		$possibleUsers = $scores[$fromChallenge];
		
		// Do we even have scores yet?
		if (!is_array($possibleUsers)) { return false; }
		
		// Remove us
		if (isset($possibleUsers[$user['id']])) {
			unset($possibleUsers[$user['id']]);
		}
		// Remove people we've already chosen
		if (is_array($answers[$challengeId.'_assigned'])) {
			foreach($answers[$challengeId.'_assigned'] as $assignedUser) {
				if (isset($possibleUsers[$assignedUser])) {
					unset($possibleUsers[$assignedUser]);
				}
			}
		}
		
		// Remove people with scores that are too low
		foreach($possibleUsers as $scoreUserId => $score) {
			if ($score < $minPoints) {
				unset($possibleUsers[$scoreUserId]);
			}
		}
		// Anything left?
		if (count($possibleUsers > 0)) {
			// Pick a random one.
			$userToFind = array_rand($possibleUsers);
		} else {
			// Nope. Oh well.
			$userToFind = false;
		}
		// Save the newly found [non-?]user
		$answers[$challengeId.'_assigned'][] = $userToFind;
		file_put_contents($answerFile, serialize($answers));
	}
	
	if ($userToFind == false) {
		return false;
	}
	
	// Load their data
	$seeking = getStudentInfo($userToFind);
	
	// Look up their answer(s) to that challenge
	$otherAnswers = unserialize(file_get_contents($dataDir.'/users/'.$userToFind.'_answers.dat'));
	return $otherAnswers[$fromChallenge];
}

include 'header.php';

?>
<div class="grid_12">
	<span class="serif"><?=h($challenge['title']);?></span>
</div>
<div class="clear"></div>

<div class="grid_10">
	<? if (is_file(dirname(__FILE__).'/challenges/'.$challengeId.'/image.jpg')) { ?>
		<span class="serif smallText"><?=h($challenge['subtitle'])?></span><br />
	<? } ?>
	<span class="serif gray smallText"><?=$challenge['points']?> points</span>
</div>
<div class="grid_2">
	<span class="bigText">#<?=$challengeNumber?></span><span class="gray">/<?=$challengeTotal?></span>
</div>
<div class="clear"></div>

<div class="grid_12" style="line-height: 200%;">
	<form method="POST" onsubmit="return confirmSubmit()" enctype="multipart/form-data">
	<input type="hidden" name="answers_id" value="<?=h($challengeId)?>" />
	<? if (is_file(dirname(__FILE__).'/challenges/'.$challengeId.'/image.jpg')) { ?>
		<img src="/challenges/<?=$challengeId?>/image.jpg" alt="Contest image" /><br />
	<? } else { ?>
		<div class="smallText importantText">
			<?=h($challenge['subtitle'])?>
		</div>
	<? } ?>
	<span class="serif">Your answers:
		<span class="smallText">(you <?= (count($answers[$challengeId]) > 0) ? 'have already' : 'have not yet'?> answered this challenge.)</span>
	</span><br />
	<?
	$canSubmit = false;
	foreach($challenge['questions'] as $questionNumber => $question) {
		if ($question['points'] > 0) {
			$pointsText = ' <span class="smallText serif">('.$question['points'].' points)</span>';
		} else {
			$pointsText = '';
		}
		if (!isset($answers[$challengeId])) { // If they haven't already submitted answers
			$canSubmit = true;
		if ($question['type'] == 'dropdown') { ?>
			<img src="/images/letters/<?=chr(97 + $questionNumber)?>small.png" alt="<?=chr(65 + $questionNumber)?>" class="answerLetter" />
			<select name="answers[<?=$questionNumber?>]">
			<? foreach($question['options'] as $answer) { ?>
				<option<?= ($answers[$challengeId][$questionNumber] == $answer) ? ' selected="selected"' : ''?>><?=h($answer)?></option>
			<? } ?>
			</select><?=$pointsText?>
		<? } elseif ($question['type'] == 'text') { ?>
			<img src="/images/letters/<?=chr(97 + $questionNumber)?>small.png" alt="<?=chr(65 + $questionNumber)?>" class="answerLetter" />
			<input type="text" name="answers[<?=$questionNumber?>]" /><?=$pointsText?>
		<? } elseif ($question['type'] == 'longtext') { ?>
			<img src="/images/letters/<?=chr(97 + $questionNumber)?>small.png" alt="<?=chr(65 + $questionNumber)?>" class="answerLetter" />
			<textarea name="answers[<?=$questionNumber?>]" rows="10" style="width: 940px"><?= (strtolower($question['default']) == 'none') ? '' : $question['default'] ?></textarea>
			<?=$pointsText?>
		<? } elseif ($question['type'] == 'referral') { ?>
			<br />
			<span class="smallText serif">Referral: Convince someone new to join the Scav. Write that person's name here. If that person writes your name in the same blank, you both score ten bonus points.</span><br />
			<input type="text" name="answers[<?=$questionNumber?>]" />
		<? } elseif ($question['type'] == 'file') { ?>
			<img src="/images/letters/<?=chr(97 + $questionNumber)?>small.png" alt="<?=chr(65 + $questionNumber)?>" class="answerLetter" />
			<input type="file" name="answers[<?=$questionNumber?>]" /><?=$pointsText?>
		<? } elseif ($question['type'] == 'random_old_answer') {
			// We need to pick a survey.
			$otherAnswers = pickUnusedSurvey($question['challenge'], $question['minPoints']);
			if ($otherAnswers != false) {
				$canSubmit = true;
				?><table class="smallText">
					<? if ($showGender == true) { ?>
						<tr>
							<th width="400">Gender</th>
							<td><?= (strtolower($seeking['gender']) == 'm') ? 'Male' : 'Female' ?></td>
						</tr>
					<? } ?>
					<? if ($showGrade == true) { ?>
						<tr>
							<th width="400">Grade Level</th>
							<td><?= ordinal_suffix($seeking['grade']) ?></td>
						</tr>
					<? } ?>
					<? foreach($otherAnswers as $answerIndex => $answer) { ?>
						<tr>
							<th width="400"><?=h($question['questions'][$answerIndex])?></th>
							<td width="520"><?=nl2br(h(stripslashes_if_gpc_magic_quotes($answer)))?></td>
						</tr>
					<? } ?>
				</table>
				<span class="smallText serif"><br />So, who is it?</span><br />
				<input type="text" name="answers[<?=$questionNumber?>]" />
			<? } else {
				$canSubmit = false;
				?>
				Oops, we're not quite ready. Please check back in a few hours.
			<? } ?>
		<? } ?>
		<br />
	<? } else { // If they've already submitted their answers
		if ($question['type'] == 'dropdown') { ?>
			<img src="/images/letters/<?=chr(97 + $questionNumber)?>small.png" alt="<?=chr(65 + $questionNumber)?>" class="answerLetter" />
			<?=h($answers[$challengeId][$questionNumber])?>
		<? } elseif ($question['type'] == 'text') { ?>
			<img src="/images/letters/<?=chr(97 + $questionNumber)?>small.png" alt="<?=chr(65 + $questionNumber)?>" class="answerLetter" />
			<?=h($answers[$challengeId][$questionNumber])?>
		<? } elseif ($question['type'] == 'longtext') { ?>
			<img src="/images/letters/<?=chr(97 + $questionNumber)?>small.png" alt="<?=chr(65 + $questionNumber)?>" class="answerLetter" />
			<?=h($answers[$challengeId][$questionNumber])?>
		<? } elseif ($question['type'] == 'file') { ?>
			<img src="/images/letters/<?=chr(97 + $questionNumber)?>small.png" alt="<?=chr(65 + $questionNumber)?>" class="answerLetter" />
			<?
				if (isset($answers[$challengeId][$questionNumber])) {
					$parts = explode(':', $answers[$challengeId][$questionNumber]);
					$type = $parts[1];
					echo 'You uploaded a "'.h($type).'" file.';
				} else {
					echo 'You didn\'t upload any file for this part.';
				}
			?>
		<? } elseif ($question['type'] == 'referral') { ?>
			<br />
			<span class="smallText serif">Referral: <?=h($answers[$challengeId][$questionNumber])?>
		<? } elseif ($question['type'] == 'random_old_answer') {
			// We need to pick a survey.
			$otherAnswers = pickUnusedSurvey($question['challenge'], $question['minPoints']);
			if ($otherAnswers != false) {
				$canSubmit = true;
				?><table class="smallText">
					<? foreach($otherAnswers as $answerIndex => $answer) { ?>
						<tr>
							<th width="400"><?=h($question['questions'][$answerIndex])?></th>
							<td width="520"><?=nl2br(h(stripslashes_if_gpc_magic_quotes($answer)))?></td>
						</tr>
					<? } ?>
				</table>
				<span class="smallText serif"><br />So, who is it?</span><br />
				<input type="text" name="answers[<?=$questionNumber?>]" />
				<input type="submit" value="Submit" class="serif button" />
			<? } else { ?>
				<span class="smallText">You've completed this question - there's nobody left to track down!<span>
			<? } ?>
		<? } ?>
		<br />
	<? }
	}
	if ($canSubmit) { ?>
	<input type="submit" value="Submit" class="serif button" />
	<? } ?>
	</form>
</div>

<div class="clear"></div>
<div style="height: 130px;"></div>
<div class="clear"></div>
<?
include 'footer.php';
?>
