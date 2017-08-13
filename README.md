# RZ Deployer

A simple PHP command-line tool to create virtual hosts and create essential webserver files.

We recommand using **Nginx** instead of *Apache* as it's easier to setup multi-users virtual hosts.

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/ca07203e-3d8e-4a88-9864-7dbd25e8f18e/mini.png)](https://insight.sensiolabs.com/projects/ca07203e-3d8e-4a88-9864-7dbd25e8f18e)

## What does it do

* Create a system user for each virtual host and generate a random password
* Create a home folder into your webserver root path (ex: /var/www/vhosts)
* Create a MySQL user and database
* Create a virtual host config file (apache2 or nginx) in your sites-available
* Create a symlink into your site-enabled folder

## Input

Before processing, RZ Deployer will ask for:

* A valid server name (ex: www.mywebsite.com)
* A username

MySQL username and database name will be named after the system user.
MySQL username could be truncated if it's too long, but it will be displayed in final notification email.
Virtual hosts files and home folder will named after your server name.

## How to use it

* Clone current repository and `cd rzdeployer`
* Install *Composer* : `curl -sS https://getcomposer.org/installer | php`
* Run `php composer.phar install` to install dependencies and create the *autoloader*
* Copy `conf/config.homebrew.yml` or `conf/config.default.yml` to `conf/config.yml`
* Edit your own configuration
* Be sure to have at least PHP 5.6 installed in CLI mode.
* Run `bin/deployer` (RZ Deployer may be run as super-user when creating users)
* Follow instructions

### Apache and PHP-FPM

We always ensure that *Unix user* and *PHP user* can read/write the same files without messing your file permissions. It's why we work with PHP-FPM, creating a different pool for each user so that PHP will run as your *user*, not *www-data*. To use Apache with PHP-FPM you can read these useful articles :

* http://blog.kmp.or.at/2013/06/apache-2-2-on-debian-wheezy-w-php-fpm-fastcgi-apc-and-a-kind-of-suexec/
* https://alexcabal.com/installing-apache-mod_fastcgi-php-fpm-on-ubuntu-server-maverick/
* and some gist: https://gist.github.com/diemuzi/3849349

**Nginx will work seamlessly with PHP-FPM**. Just make a regular install of Nginx.

### Password

RZ Deployer uses `openssl` to generate and encrypt passwords. Be sure they are correcty setup on your unix server.

## Configuration

Check `conf/config.default.yml`.

## Files

RZ Deployer will generate the following file tree in your webserver root :

* (/var/www/vhosts)**/www.yourdomain.com** *[www-data:user:0750]*
    * **/htdocs** *[user:user:0755]*
        * index.php (with phpinfo(); method)
    * **/log** *[user:root:0770]*
        * access.log
        * error.log
        * fpm-error.log
    * **/private** *[user:user:0755]*
        * **/backups**
        * **/git**
        * **/dkim** *[user:user:0700]*
    * **/.ssh** *[user:user:0700]*


## Logs

Nginx, Apache and PHP-fpm logs will be generated into each virtual host log folder.
Do not forget to update your `logrotate.d` script, for example:

* In `/etc/logrotate.d/nginx`

<pre><code>
# After existing content…
/var/www/vhosts/*/log/*.nginx.log {
    daily
    missingok
    rotate 10
    size 100M
    su www-data www-data
    compress
    delaycompress
    notifempty
    create 0664 root root
    su root root
    sharedscripts
    prerotate
        if [ -d /etc/logrotate.d/httpd-prerotate ]; then \
            run-parts /etc/logrotate.d/httpd-prerotate; \
        fi; \
    endscript
    postrotate
        [ ! -f /var/run/nginx.pid ] || kill -USR1 `cat /var/run/nginx.pid`
    endscript
}

</code></pre>

* In `/etc/logrotate.d/php5-fpm`

<pre><code>
# After existing content…
/var/www/vhosts/*/log/fpm-php.log {
    weekly
    rotate 12
    missingok
    size 100M
    notifempty
    compress
    delaycompress
    postrotate
        invoke-rc.d php5-fpm reopen-logs > /dev/null
    endscript
}
</code></pre>
