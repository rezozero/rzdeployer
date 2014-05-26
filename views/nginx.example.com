# ------------------------------------------
# {{ hostname }} virtual host
# Automatically generated with RZ Deployer
# on {{ datetime|date('Y/m/d H:i') }}
# ------------------------------------------
server {
	listen   80; ## listen for ipv4; this line is default and implied
	#listen   [::]:80 default_server ipv6only=on; ## listen for ipv6

	root {{ rootPath }}/htdocs;
	index index.html index.htm index.php;

	# Make site accessible from http://{{ hostname }}/
    server_name {{ hostname }};

    # Specify a character set
    charset utf-8;

    access_log {{ rootPath }}/log/access.log;
	error_log {{ rootPath }}/log/error.log;

	location / {

		try_files $uri $uri/ /index.html /index.php;

		{% if rzcms_install %}
		# RZ CMS redirect rule
		if ( $uri !~ ^/(index\.php|rz-temp|documents|templates|vendor|robots\.txt|favicon\.ico) ){
	        rewrite ^ /index.php last;
	    }
	    {% endif %}
	}

	# Don't log robots.txt or favicon.ico files
    location = /favicon.ico { log_not_found off; access_log off; }
    location = /robots.txt  { access_log off; log_not_found off; }

	# pass the PHP scripts to FastCGI server 
	# listening on {{ rootPath }}/php5-fpm.sock
	location ~ \.php$ {

		fastcgi_split_path_info ^(.+\.php)(/.+)$;
		fastcgi_pass unix:{{ rootPath }}/php5-fpm.sock;
		
		fastcgi_index index.php;
		include fastcgi_params;
		fastcgi_param   SERVER_PORT 80;
	}

	# deny access to .htaccess files, if Apache's document root
	# concurs with nginx's one
	#
	location ~ /\.ht {
		deny all;
	}

	{% if phpmyadmin_install %}
	# PHPMYADMIN
	location /phpmyadmin {
       root /usr/share/;
       index index.php index.html index.htm;

       location ~ ^/phpmyadmin/(.+\.php)$ {
            try_files $uri =404;
            root /usr/share/;
            # Use default pool
            fastcgi_pass unix:/var/run/php5-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include /etc/nginx/fastcgi_params;
       }
       location ~* ^/phpmyadmin/(.+\.(jpg|jpeg|gif|css|png|js|ico|html|xml|txt))$ {
               root /usr/share/;
       }
    }
    location /phpMyAdmin {
           rewrite ^/* /phpmyadmin last;
    }
    {% endif %}
}
