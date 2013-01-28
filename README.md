NorthBoard, the board software of http://northpole.fi/.

This is the board software of Northpole.fi, now released under the GPLv3 license.

!! If you have any high importance bugfixes or suggestion, please contact the admin by mail: sopsy at northpole.fi !!


REQUIREMENTS
============

- Apache2
- PHP5 (exact version unknown. Should work with the newest versions from the most popular repositories.)
- Mysql5 server
- The following PHP libraries: mysql, curl, imagick, gd, geoip
- ImageMagick for image conversions and resizing

Optional:
- PHP-APC for upload progress (Please note that the current version of PHP-APC segfaults (=crashes) on PHP5.4+!)
- tmpfs (/dev/shm) for faster file caching, other option is tmp/ -folder in installation root.
- GifSicle for gif resizing and animated gif support
- PNGCrush and OptiPNG for png optimization
- MP4Box for mp4 streaming support

INSTALLATION
============
This guide is for Debian based distros. Needs some changes for other distros.

I'm going to assume you already have a functional web server with Apache2, PHP5 and a MySQL server running.

If you cannot install the gpac -package, you might need to add http://deb-multimedia.org/ to sources.list. See the prior link for more information and guides.

1. Install required PHP-libraries and software:
  - apt-get install php-apc php5-mysql php5-curl php5-imagick imagemagick gifsicle libjpeg-progs optipng pngcrush php5-gd gpac php5-geoip

2. Ensure the required apache2 rewrite-module is active by running the following command:
  - a2enmod rewrite

3. Activate apc.rfc1867 by adding a line "apc.rfc1867 = 1" to /etc/php5/apache2/conf.d/apc.ini
  - Even though this is optional, it is required for file upload progress to work. Don't forget to restart the web server afterwards.

4. Git clone or in any other way download the code to a web server public root

5. Create a database for the application

6. Modify inc/config.php.sample as required and rename it to inc/config.php when done.

7. Check the .htaccess file if you need to edit it (for example for SSL).

7. Open http://your.url/install/install.php in your web browser and follow the installation procedure.
  - This step inserts the SQL dump into the database and allows you to add an admin account.

8. Delete the whole install -folder!

9. Create the folder tmp/ and make it writable by the web server (for example: mkdir /path/to/installation/tmp && chown -R www-data /path/to/installation/tmp && chmod -R 744 /path/to/installation/tmp).

10. Give proper permissions for the board to create the files/ -folder and all subfolders. The easiest way is of course "chmod -R /path/to/installation 777", but this is also the least secure.

11. Open http://your.url/ and you should see the board open!

12. To add boards, you need to issue the commands to the database directly, because the administration panel is incomplete. You can use PHPMyAdmin for example.
  - First add a category into the "categories" -table. Only the "name" -column is required. Note the ID of the inserted row (most likely 1).
  - Then add a board into "boards" -table. The only required values are url (ex. "b" - without any slashes!), name (ex. "Random") and category (the id of the inserted category (ex. "1"), required for the board to show up in the menus). "Worksafe" could be set to "1" to disable the hiding when NSFW is hidden.


Included software
=================
This release also includes some other software freely available from the internet:
- The javamod player, javamod.jar (Could be this, but not really sure: http://www.javamod.de/ - if you find the correct link, please let me know!)
- FamFamFam flag icons (http://www.famfamfam.com/lab/icons/flags/)
- NiftyPlayer (http://www.varal.org/media/niftyplayer/)
- JW Player (http://www.longtailvideo.com/players/jw-flv-player/)
- jQuery plus some plugins - credits and links inside the files
- PHP-Gettext localization library (https://launchpad.net/php-gettext/)

Blinkenworld
------------
Blinkenworld, which was included before, is now moved to a separate repository: https://github.com/Sopsys/blinkenworld

Installation of it with NB is just a matter of downloading the script, the APIpath is correctly set already.
