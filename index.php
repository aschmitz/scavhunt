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

$title = 'Current Challenge';
$tab = 'current';
$noHeader = true;
include 'header.php';

if (!$challengeId) {
	// If there's no challenge any more, redirect to the scoreboard.
	header('Location: /scoreboard.php');
	die();
} else {
	include 'header.php';
}

// Load the current challenge
$challenge = getChallengeData($challengeId);

?>
<div style="height: 80px;"></div>
<div class="clear"></div>
<div class="grid_6">
	<div style="padding-left: 10px;">
		<span class="bigText">#<?=$challengeNumber?></span><span class="gray">/<?=$challengeTotal?></span><br />
		<span class="serif normalText"><?=h($challenge['title']);?></span><br />
		<span class="serif gray smallText"><?=$challenge['points']?> points</span>
	</div>
	<a href="play.php">
		<img src="/images/play.png" alt="Play" width="348" height="149" />
	</a>
</div>
<div class="grid_6">
	<img src="/challenges/<?=$challengeId?>/teaser.png" alt="Teaser" width="460" />
</div>
<div class="clear"></div>
<div style="height: 130px;"></div>
<div class="clear"></div>
<?
include 'footer.php';
?>
