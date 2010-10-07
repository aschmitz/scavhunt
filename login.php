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

require_once(dirname(__FILE__).'/lib/login.php');

unset($errorText);

if ((strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') &&
	(isset($_POST['create_age']) || isset($_POST['create_lastname']) || isset($_POST['create_password']))) {
	if ($_POST['create_lastname'] == '' && $_POST['create_password'] == '') {
		$errorText = 'Sorry, we couldn\'t log you in using that information.';
	} elseif ($_POST['create_age'] != 'over_13') {
		$errorText = 'Sorry, you must be at least 13 to play in the scavenger hunt.';
	} else {
		// They want to create an account, and are at least 13.
		if (isUser($_POST['schoolId'])) {
			// The user already exists
			$errorText = 'It looks like you already have an account. Try logging in with just your ID and password.';
		} elseif (($_POST['create_password'] = '') || ($_POST['create_lastname'] == '')) {
			// They didn't enter a last name or password
			$errorText = 'Sorry, you must fill out the new user form completely.';
		} else {
			$user = getStudentInfo($_POST['schoolId']);
			if ((count($user) == 0) || (levenshtein(strtolower($user['last']), strtolower($_POST['create_lastname'])) > 2)) {
				// The student ID wasn't found, or their last name didn't match closely enough
				$errorText = 'Sorry, we can\'t find a record of you at the school.';
			} else {
				// Create their record
				$userFileData = serialize(Array('password' => md5($_REQUEST['create_password'])));
				file_put_contents($user['file'], $userFileData);
				
				// Log them in
				session_start();
				$_SESSION['schoolId'] = $_POST['schoolId'];
				$_SESSION['password'] = $_REQUEST['create_password'];
				$_SESSION['flash'] = 'Your account was created successfully, and you\'ve been logged in.';
				
				// Bounce them so they'll get redirected to the right place
				header('Location: /login.php');
				die();
			}
		}
	}
}

$require_nouser = true;
$title = 'Login';
$tab = '';
include 'header.php';

if (!isset($errorText) && (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')) {
	$errorText = 'Sorry, we couldn\'t log you in using that information.';
}

if (isset($errorText)) {
	$errorText .= '<br />We\'re sorry you\'re having trouble. E-mail <a href="mailto:email@example.com">email@example.com</a>';
	$errorText .= ' with your full name and student ID number and we\'ll get you fixed up quickly.';
}

?>
<form action="/login.php" method="POST">
	<span class="bigText">Login</span><br />
	<? if (isset($errorText)) { ?>
		<div class="errorText"><?= $errorText ?></div>
	<? } ?>
	<span class="serif normalText">We have to make sure you go to our school</span><br />
	<span class="serif gray smallText">school id: <input type="text" name="schoolId" /></span><br />
	<br />
	<span class="serif normalText">Already have an account with the scavenger hunt?</span><br />
	<span class="serif gray smallText">password: <input type="password" name="password" /></span><br />
	<br />
	<span class="serif normalText">First time here?</span><br />
	<span class="serif gray smallText">last name: <input type="text" name="create_lastname" /></span><br />
	<span class="serif gray smallText">make up a password: <input type="password" name="create_password" /></span><br />
	<span class="serif gray smallText">I am at least 13 years old: <input type="checkbox" name="create_age" value="over_13" /></span><br />
	<br />
	<input type="submit" value="start playing" class="serif button" />
	<div class="clear"></div>
	<div style="height: 80px;"></div>
	<div class="clear"></div>
</form>
<?
include 'footer.php';
?>
