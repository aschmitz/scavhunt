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

$extensionToMime = Array();
$extensionToMime['jpg'] = 'image/jpeg';
$extensionToMime['jpeg'] = 'image/jpeg';
$extensionToMime['png'] = 'image/png';
$extensionToMime['tiff'] = 'image/tiff';

if (isset($extensionToMime[$_REQUEST['type']])) {
	$type = $extensionToMime[$_REQUEST['type']];
} else {
	$type = 'application/octet-stream';
}

header('Content-type: '.$type);

$filename = $dataDir.'/uploads/'.$_REQUEST['challenge'].'_'.
	$_REQUEST['user'].'_'.$_REQUEST['num'].'.'.$_REQUEST['type'];

echo file_get_contents($filename);

?>
