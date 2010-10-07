scavhunt
========

scavhunt is an online scavenger hunt with multiple questions released at different times, based on the code from the first [SLV Scav][1]. The documentation at the moment is disappointingly minimal-to-nonexistent, but I would be happy to answer questions about it via email (at andy [dot] schmitz [at] gmail [dot] com).

Important Files
---------------

Unfortunately, configuration information is a bit spread out at the moment. A single config.php would be helpful, but hasn't been made yet.

 - `header.php` contains a timezone setting and an optional redirect to a countdown page for display before the scavenger hunt starts. It also contains a bit of text describing the hunt that you may wish to change.

 - `footer.php` has some footer text you'll almost certainly want to change.

 - `contact.php` has contact information that you should change to your own relevant contact information.

 - `admin/auth.php` contains an array of users (and associated passwords) that are allowed to access the administration pages.

 - `challenges/` is a folder that contains another folder for each challenge. In the first SLV Scav, these were randomly chosen names to avoid users guessing the location of information for the next challenge, but an image proxy script is on the todo list to eliminate that necessity.

 - `data/` is a directory for internal scavhunt data, and should not be readable from the webserver. scavhunt comes with an .htaccess file that prohibits this by default, but your server may require separate configuration.

 - `data/challenges.txt` lists the challenge IDs and the date they end. Each challenge begins immediately after the previous one ends.

 - `data/schoolid.csv` has a comma-separated list of all students in the school, including gender, school student ID number, grade (as a number), last name, and first names.

 - `data/answers/` is a directory containing a .jpg and .txt file for each challenge. The .jpg is displayed for the answers for the question, and each line of the .txt file is displayed as an answer to a question when answers are shown.

 - `data/uploads/` is where user uploads go, it should be readable and writable by the web server.

 - `data/users/` is where user information goes when users register or submit answers, it should also be readable and writable by the web server.

 - `data/scores.dat` is a serialized copy of the scores for the entire contest.

Errata / TODO
-------------

 - scavhunt has no support for online editing of questions or answers.

 - Configuration information is spread about several different files, and should be consolidated.

 - An image passthrough script should be created to pass through challenge images if and only if the image should be available (that is, isn't for a future challenge), which would ease the naming of challenges, and possibly simplify the configuration files.

 - scavhunt supports only male and female genders. This was a decision based on the limited time available and specifics of the initial school the code was written for. It should be fairly simple to add support for other identifications, but the data isn't currently used unless displaying it is enabled, and even then only in the case of identifying past users' responses.

 - scavhunt really needs better documentation and/or a setup guide.

License (AGPL v3)
-----------------

Copyright 2010, Andy Schmitz

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License (in the LICENSE file) for more details.

Because the program currently has (potentially sensitive) configuration details in files, and the administrative pages are not accessible to users, no working "Source" link is necessary on individual pages: A valid email address on the "contact" page with timely (within a week) responses to requests for source code will satisfy section 13 of the GNU Affero General Public License in my interpretation until a working "Source" feature exists in the slvscav code.


  [1]: http://blog.mrmeyer.com/?p=6749
