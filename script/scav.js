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

function $(id) { return document.getElementById(id); }
var nextChallenge;

function init() {
	// quit if this function has already been called
	if (arguments.callee.done) return;
	// flag this function so we don't do the same thing twice
	arguments.callee.done = true;
	
	nextChallenge = $('countdown').innerHTML;
	if (nextChallenge != '') {
		nextChallenge = new Date(nextChallenge);
		updateTimer();
		setInterval("updateTimer()", 1000);
	}
}

function updateTimer() {
	now = new Date();
	diff = new Date(nextChallenge - now - timeOffset);
	diff = diff.valueOf() / 1000;
	if (diff < 0) { diff = 0; }
	$('countdown').innerHTML = toTwo(diff / (60*60)) + ':' +
		toTwo((diff % (60*60)) / 60) + ':' + toTwo(diff % 60);
}

function toTwo(num) {
	num = Math.floor(num);
	if (num < 10) { return '0'+num; }
	return num;
}

function confirmSubmit() {
	return confirm("You can only submit your answers once for this question! Continue submitting these answers?");
}

// From http://dean.edwards.name/weblog/2005/09/busted/
if (document.addEventListener) { document.addEventListener("DOMContentLoaded", init, false); }
/*@cc_on @*/
/*@if (@_win32)
   document.write("<script defer src=\"/script/ie_onload.js\"><"+"/script>");
/*@end @*/
window.onload = init;
