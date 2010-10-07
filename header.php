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

// Set up the timezone - pick your own from the standard tzdata list.
//  For the US, http://www.statoids.com/tus.html looks like a decent list.
date_default_timezone_set('America/Los_Angeles');

// This shows a countdown before the scavenger hunt actually launched.
if (time() < strtotime("May 3, 2010 06:00:00")) {
	include 'count/index.php';
	die();
}

// Make sure this isn't cached
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

require_once(dirname(__FILE__).'/lib/login.php');
userLogin();

if (($user != false) && isset($_SESSION['redir_to'])) {
	header('Location: '.$_SESSION['redir_to']);
	unset($_SESSION['redir_to']);
	die();
} elseif ($require_login && ($user == false)) {
	$_SESSION['redir_to'] = $_SERVER['REQUEST_URI'];
	header('Location: /login.php');
	die();
} elseif ($require_nouser && ($user != false)) {
	header('Location: /');
	die();
}

// Look through the list of challenges, find the current challenge.
$challenges = preg_split('/[\r\n]+/mis', file_get_contents(dirname(__FILE__).'/data/challenges.txt'), 0, PREG_SPLIT_NO_EMPTY);
$challengeTotal = count($challenges);
$challengeId = false;

for($challengeNumber = 1; $challengeNumber <= $challengeTotal; $challengeNumber++) {
	$challengeBits = explode('-', $challenges[$challengeNumber-1]);
	if (time() < strtotime(trim($challengeBits[1]))) {
		// If this challenge hasn't passed, it must be the current challenge.
		$challengeId = trim($challengeBits[0]);
		$challengeEnd = trim($challengeBits[1]);
		break;
	} else {
		$lastChallengeEnd = trim($challengeBits[1]);
	}
}

if ($noHeader) {
	$noHeader = false;
	return;
}
?>
<html>
<head>
	<title><?=htmlentities($title)?> - Scavenger Hunt</title>
	<link rel="stylesheet" href="/css/reset.css" />
	<link rel="stylesheet" href="/css/960.css" />
	<link rel="stylesheet" href="/css/style.css?v=6" />
	<script language="JavaScript">
		var refTime = new Date("<?=date('F j, Y H:i:s');?>");
		var nowTime = new Date();
		var timeOffset = refTime.valueOf() - nowTime.valueOf();
	</script>
	<script language="JavaScript" src="/script/scav.js?v=4"></script>
</head>
<body>
<div class="container_12 header">
	<div class="grid_8">
		<a href="/"><img src="/images/scavhunt_logo.jpg" /></a><br />
		<div class="tagline">a scavenger hunt at your high school *</div>
	</div>
	<div class="grid_4 countdown_container">
		<div id="countdown"><?=$challengeEnd?></div>
	</div>
	<div class="clear"></div>
</div>
<div class="menu">
<div class="container_12">
<? if ($challengeId) { ?>
	<a class="grid_2 item<?=($tab == 'current') ? ' selected' : '' ?>" href="/">
		current
	</a>
	<a class="grid_2 item<?=($tab == 'previously') ? ' selected' : '' ?>" href="/previously.php">
		previously
	</a>
	<a class="grid_2 item<?=($tab == 'scoreboard') ? ' selected' : '' ?>" href="/scoreboard.php">
		scoreboard
	</a>
	<a class="grid_2 item<?=($tab == 'contact') ? ' selected' : '' ?>" href="/contact.php">
		contact
	</a>
	<div class="grid_4 name">
		<? if ($user) { ?>
			<?=htmlentities(strtolower($user['first'].' '.$user['last'])) ?>
			<a href="/logout.php" class="smallText">[logout]</a>
		<? } ?>
	</div>
<? } else { ?>
	<a class="grid_2 item<?=($tab == 'previously') ? ' selected' : '' ?>" href="/previously.php">
		previously
	</a>
	<a class="grid_2 item<?=($tab == 'scoreboard') ? ' selected' : '' ?>" href="/scoreboard.php">
		scoreboard
	</a>
	<a class="grid_2 item<?=($tab == 'contact') ? ' selected' : '' ?>" href="/contact.php">
		contact
	</a>
	<div class="grid_2">
		&nbsp;
	</div>
	<div class="grid_4 name">
		<? if ($user) { ?>
			<?=htmlentities(strtolower($user['first'].' '.$user['last'])) ?>
			<a href="/logout.php" class="smallText">[logout]</a>
		<? } ?>
	</div>
<? } ?>
</div>
<div class="clear"></div>
</div> <!-- menu -->
<div class="body">
<div class="container_12 body">
<div style="height: 30px;"></div>
<div class="clear"></div>
<? if (isset($_SESSION['error'])) { ?>
	<div class="errorText"><?= $_SESSION['error'] ?></div>
	<div style="height: 30px;"></div>
<?	unset($_SESSION['error']);
} ?>
<? if (isset($_SESSION['flash'])) { ?>
	<div class="flashText"><?= $_SESSION['flash'] ?></div>
	<div style="height: 30px;"></div>
<?	unset($_SESSION['flash']);
} ?>
