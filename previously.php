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
$title = 'Previously';
$tab = 'previously';
$noHeader = true;
include 'header.php';

if (isset($_REQUEST['id'])) {
	for($fakeChallengeNumber = 1; $fakeChallengeNumber <= $challengeTotal; $fakeChallengeNumber++) {
		$challengeBits = explode('-', $challenges[$fakeChallengeNumber-1]);
		if ($challengeId == trim($challengeBits[0])) {
			// We reached the current challenge, which we shouldn't give answers for, so stop.
			session_start();
			$_SESSION['error'] = 'Sorry, that challenge\'s answers are not yet available.';
			header('Location: /previously.php', true, 303);
			die();
		}
	
		if ($_REQUEST['id'] == trim($challengeBits[0])) {
			$fakeId = trim($challengeBits[0]);
			$challenge = getChallengeData($fakeId);
			
			include 'header.php';
			?>
<div class="grid_12">
	<span class="serif"><?=h($challenge['title']);?></span>
</div>
<div class="clear"></div>

<div class="grid_10">
	<? if (dirname(__FILE__).'/data/answers/'.$fakeId.'.jpg') { ?>
		<span class="serif smallText"><?=h($challenge['subtitle'])?></span><br />
	<? } ?>
	<span class="serif gray smallText"><?=$challenge['points']?> points</span>
</div>
<div class="grid_2">
	<span class="bigText">#<?=$fakeChallengeNumber?></span><span class="gray">/<?=$challengeTotal?></span>
</div>
<div class="clear"></div>

<div class="grid_12" style="line-height: 200%;">
	<? if (is_file(dirname(__FILE__).'/data/answers/'.$fakeId.'.jpg')) { ?>
		<img src="/images/challenge.php?id=<?=$fakeId?>&image=answers" alt="Contest answers" /><br />
	<? } else { ?>
		<div class="smallText importantText">
			<?=h($challenge['subtitle'])?>
		</div>
	<? } ?>
	
	<?
	if (is_file(dirname(__FILE__).'/data/answers/'.$fakeId.'.txt')) {
		echo 'Answers:<br />';
		
		$answers = preg_split('/[\r\n]+/mis', file_get_contents(dirname(__FILE__).'/data/answers/'.$fakeId.'.txt'), 0, PREG_SPLIT_NO_EMPTY);
		foreach($answers as $answerNumber => $answer) {
		?>
		<img src="/images/letters/<?=chr(97 + $answerNumber)?>small.png" alt="<?=chr(65 + $answerNumber)?>" class="answerLetter" />
		<?=h($answer)?><br />
		<?
		}
	}
	?>
</div>

<div class="clear"></div>
<div style="height: 130px;"></div>
<div class="clear"></div>
			<?
			include 'footer.php';
			die();
		}
	}
}

include 'header.php';

for($challengeNumber = 1; $challengeNumber <= $challengeTotal; $challengeNumber++) {
	$challengeBits = explode('-', $challenges[$challengeNumber-1]);
	if ($challengeId == trim($challengeBits[0])) {
		// We reached the current challenge, stop here.
		break;
	}
	
	$fakeId = trim($challengeBits[0]);
	$challenge = getChallengeData($fakeId);
?>
<div style="height: 80px;"></div>
<div class="clear"></div>
<div class="grid_6">
	<div style="padding-left: 10px;">
		<span class="bigText">#<?=$challengeNumber?></span><span class="gray">/<?=$challengeTotal?></span><br />
		<span class="serif normalText"><?=h($challenge['title']);?></span><br />
		<span class="serif gray smallText"><?=$challenge['points']?> points</span>
		<div style="padding-top: 30px; font-size: 150%" class="serif">
			<a href="previously.php?id=<?=$fakeId?>">See Answers</a>
		</div>
	</div>
</div>
<div class="grid_6">
	<img src="/challenges/<?=$fakeId?>/teaser.png" alt="Teaser" width="460" />
</div>
<div class="clear"></div>
<? } ?>
<div style="height: 30px;"></div>
<div class="clear"></div>
<?
include 'footer.php';
?>
