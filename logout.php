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

// From http://us.php.net/session_destroy
// Initialize the session.
// If you are using session_name("something"), don't forget it now!
session_start();

// Unset all of the session variables.
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// And then redirect to the main page.
header('Location: /');
die();
?>
