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
* _Restart webserver_
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

* http://blog.kmp.or.at/2013/06/apache-2-2-on-debian-wheezy-w-php-fpm-fastcgi-apc-and-a-kind-of-suexec/
* https://alexcabal.com/installing-apache-mod_fastcgi-php-fpm-on-ubuntu-server-maverick/
* and some gist: https://gist.github.com/diemuzi/3849349

**Nginx will work seamlessly with PHP-FPM**. Just make a regular install of Nginx.

## Password

RZ Deployer uses `openssl` to generate and encrypt passwords. Be sure they are correcty setup on your unix server.

# Configuration

Parameter                | Default value                | Description  
-------------------------|------------------------------| ------------
`webserver_root` 	     | /var/www/vhosts              | System folder in which virtual host home folder will be created
`webserver_group` 	     | www-data                     | Owner of Apache or Nginx processes (User and Group)
`vhosts_path` 	         | /etc/apache2/sites-available | Webserver virtual host file repository
`vhosts_enabled_path`    | /etc/apache2/sites-enabled   | Activated virtual host symlinks folder
`phpfpm_enabled` 	     | *false* or *true* for nginx  | Use php-fpm with apache2 (forced *true* with Nginx)
`phpfpm_pools_path` 	 | /etc/php5/fpm/pool.d         | PHP-FPM pools repository folder
`phpmyadmin_install`     | false                        | Insert a special location in your nginx vhost for phpmyadmin (false by default, you'd prefer using a unique URL for every vhosts with HTTPS connexion)
`mpm_itk`                | *false*                      | A quick and dirty way to enable *per-user* apache processes. No need for php-fpm but it’s not the same performances and security.
`webserver_type` 	     | *apache2*  or *nginx*        | Webserver engine (*Nginx recommanded*)
`use_rzcms` 	         | false                        | Clone RZ-CMS and configure virtual hosts to enable RZ-CMS on your website
`use_index_entrypoint`   | false                        | No implemented yet
`sender_email` 	         | sender@test.com              | Sender email address for notifications
`notification_email` 	 | test@test.com                | Final email address to receive summary
`mysql_host` 	         | localhost                    | MySQL server host address
`mysql_user` 	         | root                         | MySQL super-user name
`mysql_password` 	     | ************                 | MySQL super-user password
`allowssh_group` 	     | sshusers                     | Additional unix group for your virtual host user

# Files

RZ Deployer will generate the following file tree in your webserver root : 

* (/var/www/vhosts)**/www.yourdomain.com** *[www-data:user:0750]*
    * **/htdocs** *[user:user:0755]*
        * index.php (with phpinfo(); method)
    * **/log** *[user:root:0770]*
        * access.log
        * error.log
        * fpm-error.log
    * php5-fpm.sock *[root:root:0666]*
    * **/private** *[user:user:0755]*
        * **backups**
        * **git**
    * **/.ssh** *[user:user:0700]*
