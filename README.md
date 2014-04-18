tamuhvz
=======
INSTALLATION INSTRUCTIONS

System Requirements:
PHP: 5.2+
MySQL: 4+
Webserver: Apache 2.2 with mod_rewrite and mod_ssl
An SSL certificate (self-signed is fine)

Please notify Ryan if you encounter any issues with installation
when following the instructions below

1.  Review above system requirements
2.  Download and extract source code from http://skizzerz.net/bzr/tamuhvz/
3.  Delete the file called "lock" from the mybb/install directory
4.  Install MyBB, with mybb_ as the table prefix (go to yoursite/mybb/install)
    Your admin username MUST be two words, e.g. "Firstname Lastname"
5.  Import schema.sql into the same database (DO THIS AFTER YOU INSTALL MyBB)
6.  Copy settings.sample to settings.php and adjust to your site
7.  Go to your site and register for an account. Verify that you are able to
    see both the tabs "Admin" and "Developer" on the top row. The firstname
    and lastname you enter when registering MUST be the same as the admin
    username you specified when installing MyBB!
8.  DO NOT VISIT THE FORUM AFTER REGISTERING FOR AN ACCOUNT UNTIL YOU
    COMPLETE THESE NEXT FEW STEPS OR THINGS MAY BREAK
9.  Go to http://skizzerz.net/scripts/string.php and type your MyBB forum
    password into the "Encode" box and submit
10. Copy the result and execute the following query:
    UPDATE users SET forum_id=1,forum_pw="PASTE RESULT HERE" WHERE uin=1
11. You should now be able to visit the forums and verify it logs you in
    Some of the info is off on the forum display page, the next step fixes that
12. Log into your Admin CP on the forums (click the "Admin CP" link), and
    go to the "Tools & Maintenance" heading. Select the "Recount & Rebuild"
    option, then hit the "Go" button next to each one of the items there.
13. Add the following cron jobs (adjust paths as necessary)
    * * * * * /path/to/php /path/to/tamuhvz/emailqueue.php > /dev/null 2>&1
    0 * * * * /path/to/php /path/to/tamuhvz/tracker.php > /dev/null 2>&1
