# ------------------------------------------
# {{ hostname }} virtual host
# Automatically generated with RZ Deployer
# on {{ datetime|date('Y/m/d H:i') }}
# ------------------------------------------
<VirtualHost *:80>
	ServerName {{ hostname }}
	ServerAdmin {{ email }}
	DocumentRoot {{ rootPath }}/htdocs

	{% if mpm_itk %}
	# Apache MPM ITK enables user uid and gid for current process (Apache + mod_php)
	# But it decreases performances and can be dangerous as Apache has to use *root* privilege before
	# create a child process using to right uid and gid
	<IfModule mpm_itk_module>
		AssignUserId {{ username }} {{ username }}
	</IfModule>
	{% endif %}

	# Forbid webserver root
	<Directory "/var/www">
		Options +FollowSymLinks
		AllowOverride None
		Deny from all
	</Directory>

	<Directory {{ rootPath }}/htdocs/>
		Options -Indexes +FollowSymLinks +MultiViews
		AllowOverride None
		Order allow,deny
		Allow from all

		ErrorDocument 404 /
        IndexIgnore *

        # ------------------------------------
        # EXPIRES CACHING
        # ------------------------------------
        <IfModule mod_expires.c>
			ExpiresActive On
			ExpiresByType image/jpg "access plus 1 year"
			ExpiresByType image/jpeg "access plus 1 year"
			ExpiresByType image/gif "access plus 1 year"
			ExpiresByType image/png "access plus 1 year"
			ExpiresByType text/css "access plus 1 month"
			ExpiresByType application/pdf "access plus 1 month"
			ExpiresByType text/x-javascript "access plus 1 month"
			ExpiresByType text/javascript "access plus 1 month"
			ExpiresByType application/x-shockwave-flash "access plus 1 month"
			ExpiresByType image/x-icon "access plus 1 year"
			ExpiresDefault "access plus 2 days"
        </IfModule>


        {% if rzcms_install %}
		# --------------------
		# REWRITE ENGINE
		# --------------------
		RewriteEngine On

		# redirect additionnal domains
		# RewriteCond %{HTTP_HOST} ^(www\.)?other_domain\.fr$ [NC]
		# RewriteRule ^(.*)$ http://{{ hostname }}/$1 [R=301,L]
		# RewriteCond %{HTTP_HOST} ^(www\.)?other_domain\.org$ [NC]
		# RewriteRule ^(.*)$ http://{{ hostname }}/$1 [R=301,L]
		# RewriteCond %{HTTP_HOST} ^(www\.)?other_domain\.org$ [NC]
		# RewriteRule ^(.*)$ http://{{ hostname }}/$1 [R=301,L]

		# Redirect to www
		#RewriteCond %{HTTP_HOST} !^www\.
		#RewriteRule ^(.*)$ http://www.%{HTTP_HOST}/$1 [R=301,L]

		# ByPass for these folders
		RewriteRule ^documents - [L,NC]
		RewriteRule ^rz-temp - [L,NC]
		RewriteRule ^templates - [L,NC]

		RewriteCond %{REQUEST_FILENAME} !-d
		RewriteCond %{REQUEST_FILENAME} !-f
		RewriteRule ^(.*)$ index.php [QSA,L]
		# ------ end of RZ_CMS htaccess --------------------------------------------------
		{% endif %}
	</Directory>

	{% if rzcms_install %}
	# ------- Secure RZ_CMS folders ------------------------
    <Directory {{ rootPath }}/htdocs/rz-core/>
		<FilesMatch ".$">
			Order deny,allow
			Deny from all
			Allow from localhost
		</FilesMatch>
    </Directory>
    # ------- Secure RZ_CMS folders ------------------------
    <Directory {{ rootPath }}/htdocs/fonts/>
		<FilesMatch ".$">
			Order deny,allow
			Deny from all
			Allow from localhost
		</FilesMatch>
    </Directory>
    # ------- Secure RZ_CMS folders ------------------------
    <Directory {{ rootPath }}/htdocs/private_documents/>
		<FilesMatch ".$">
			Order deny,allow
			Deny from all
			Allow from localhost
		</FilesMatch>
    </Directory>
    {% endif %}
	
	{% if phpfpm_enabled %}
	Alias /fcgi-bin/php5-fpm /fcgi-bin-php5-fpm-{{ username }}
	FastCgiExternalServer /fcgi-bin-php5-fpm-{{ username }} -socket {{ rootPath }}/php5-fpm.sock -pass-header Authorization
	{% endif %}
	
	ErrorLog {{ rootPath }}/log/error.log
	LogLevel warn
	CustomLog {{ rootPath }}/log/access.log combined
	
</VirtualHost> 