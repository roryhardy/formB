All code is provided AS-IS. This code is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
- Rory Cronin-Hardy 2011


The supplied file (FormBackend.php) is all you need to include to utilize all available features.  For details you can either read the comments in the code or the supplied Class Synopsis (Documentation.pdf) for details and usage.  The folder example is a supplied and working form utilizing the FormB and Line classes.

config.inc IS REQUIRED by FormBackend.php.  Be sure to have it available or this system will not function!

Adding 

<FilesMatch "\.inc$">
	Order allow,deny
	Deny from all
</FilesMatch>

to your .htaccess file is recommended if you use .inc for PHP includes.
If a .inc file is requested, all of the code will be revealed as plain text (which is usually very undisirable).

See Default.sql for a proposed table structure for use with the IP-Checker methods!