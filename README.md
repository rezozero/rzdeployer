# RZ Deployer

A simple PHP command-line tool to create virtual hosts and create essential webserver files.

We recommand using **Nginx** instead of *Apache* as it's easier to setup multi-users virtual hosts.

## What does it do

* Create a system user for each virtual host and generate a random password
* Create a home folder into your webserver root path (ex: /var/www/vhosts)
* Create a MySQL user and database
* Create a PHP FPM socket file into your user home
* Create a virtual host config file (apache2 or nginx) in your sites-available
* Create a symlink into your site-enabled folder
* Restart webserver
* Notify by email
* Clone and install a RZ-CMS instance (optional)

## Input

Before processing, RZ Deployer will ask for:

* A valid server name (ex: www.mywebsite.com)
* A username

MySQL username and database name will be named after the system user. 
MySQL username could be truncated is too long, but it will be displayed in final notification email.
Virtual hosts files and home folder will named after your server name.

# How to use it

* Clone current repository and `cd rzdeployer`
* Install *Composer* : `curl -sS https://getcomposer.org/installer | php`
* Run `php composer.phar install` to install dependencies and create the *autoloader*
* Copy `conf/config.default.apache2.json` or `conf/config.default.nginx.json` to `conf/config.json`
* Edit your own configuration
* Be sure to have at least PHP 5.4 installed in CLI mode.
* Run `sudo php app.php` (RZ Deployer must be run as super-user)
* Follow instructions
* If you chose to install RZCMS, you must have access to private REZO ZERO Git repository (a password will be requested)

## Apache and PHP-FPM

We always ensure that *Unix user* and *PHP user* can read/write the same files without messing your file permissions. It's why we work with PHP-FPM, creating a different pool for each user so that PHP will run as your *user*, not *www-data*. To use Apache with PHP-FPM you can read these useful articles : 

* http://www.janoszen.com/2013/04/29/setting-up-apache-with-php-fpm/ 
* https://alexcabal.com/installing-apache-mod_fastcgi-php-fpm-on-ubuntu-server-maverick/
* and some gist: https://gist.github.com/diemuzi/3849349

**Nginx will work seamlessly with PHP-FPM**. Just make a regular install of Nginx.

## Password

RZ Deployer uses `openssl` to generate and encrypt passwords. Be sure they are correcty setup on your unix server.