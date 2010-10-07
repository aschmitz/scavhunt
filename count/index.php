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

// More timezone stuff. See header.php in the root directory for more info.
date_default_timezone_set('America/Los_Angeles');
?><html>
<head>
<script language="JavaScript">
var refTime = new Date("<?=date('F j, Y H:i:s');?>");
var nowTime = new Date();
var timeOffset = refTime.valueOf() - nowTime.valueOf();
var countTo = new Date("May 3, 2010 06:00:00");

function $(id) { return document.getElementById(id); }

function init() {
	if (arguments.callee.done) return;
	arguments.callee.done = true;
	
	updateTimer();
	setInterval("updateTimer()", 1000);
}

function updateTimer() {
	now = new Date();
	diff = new Date(countTo - now - timeOffset);
	diff = diff.valueOf() / 1000;
	if (diff < 0) { diff = 0; setTimeout(refreshPage, 1000); }
	$('countdown').innerHTML = toTwo(diff / (60*60)) + ':' +
		toTwo((diff % (60*60)) / 60) + ':' + toTwo(diff % 60);
}

function toTwo(num) {
	num = Math.floor(num);
	if (num < 10) { return '0'+num; }
	return num;
}

function refreshPage() {
	document.location = document.location;
}

// From http://dean.edwards.name/weblog/2005/09/busted/
if (document.addEventListener) { document.addEventListener("DOMContentLoaded", init, false); }
/*@cc_on @*/
/*@if (@_win32)
  document.write("<script defer src=\"/script/ie_onload.js\"><"+"/script>");
/*@end @*/
window.onload = init;
</script>

<style type="text/css">
body,html {height: 100%; margin:0; padding:0;}
#outer {height: 100%; overflow: hidden; position: relative; width: 100%;}
#outer[id] {display: table; position: static;}
#middle {position: absolute; top: 50%; width: 100%; text-align: center;}
#middle[id] {display:table-cell; vertical-align: middle; position: static;}
#inner {position: relative; top: -50%; text-align: center; width:100%;}
</style>
<title>SLVSCAV</title>
</head>
<body>
<div id="outer"><div id="middle"><div id="inner">
	<span id="countdown" style="font-weight: bold; font-size: 120pt; font-family: Arial, sans-serif; text-decoration: none; color: black;"></span>
</div></div></div>
</body>
</html>
