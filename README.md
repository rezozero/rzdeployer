# RZ Deployer

A simple PHP command-line tool to create virtual hosts and create essential webserver files.

## What does it do

* Create a system user for each virtual host and generate a random password
* Create a home folder into your webserver root path (ex: /var/www/vhosts)
* Create a MySQL user and database
* Create a PHP FPM socket file into your user home
* Create a virtual host config file (apache2 or nginx) in your sites-available
* Create a symlink into your site-enabled folder
* Restart webserver
* Notify by email

## Input

Before processing, RZ Deployer will ask for:

* A valid server name (ex: www.mywebsite.com)
* A username

MySQL username and database name will be named after the system user. 
MySQL username could be truncated is too long, but it will be displayed in final notification email.
Virtual hosts files and home folder will named after your server name.

## How to use it

* Clone current repository
* Copy `conf/config.default.apache2.json` or `conf/config.default.nginx.json` to `conf/config.json`
* Edit your own configuration
* Be sure to have at least PHP 5.4 installed in CLI mode.
* Run `sudo php app.php` (RZ Deployer must be run as super-user)