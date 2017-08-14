# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.network "private_network", ip: "192.168.34.10"

  config.vm.provider "virtualbox" do |v|
    v.memory = 2048
    v.cpus = 2
  end

  config.vm.provision "shell", inline: <<-SHELL
    export DEBIAN_FRONTEND=noninteractive

    sudo debconf-set-selections <<< "mysql-server-5.5 mysql-server/root_password password root"
    sudo debconf-set-selections <<< "mysql-server-5.5 mysql-server/root_password_again password root"
    sudo debconf-set-selections <<< "mysql-server-5.6 mysql-server/root_password password root"
    sudo debconf-set-selections <<< "mysql-server-5.6 mysql-server/root_password_again password root"
    sudo debconf-set-selections <<< "mysql-server-5.7 mysql-server/root_password password root"
    sudo debconf-set-selections <<< "mysql-server-5.7 mysql-server/root_password_again password root"

    LC_ALL=C.UTF-8 sudo add-apt-repository ppa:ondrej/php
    sudo apt-get update

    # For Apache 2.4
    #sudo apt-get install apache2 apache2-doc apache2-utils apache2-mpm-event
    #sudo a2enmod rewrite proxy proxy_fcgi setenvif
    #sudo a2enconf php7.1-fpm

    # For Nginx
    sudo apt-get install -y nginx

    # Others
    sudo apt-get install -y git curl zip php7.1 php7.1-opcache php7.1-curl php7.1-gd \
                            php7.1-apcu php7.1-intl php7.1-xml php7.1-fpm php7.1-zip \
                            php7.1-mysql php7.1-cli openssl mysql-server mysql-client

    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet
    rm composer-setup.php
    sudo mv composer.phar /usr/bin/composer
    sudo mkdir -p /var/www/vhosts
  SHELL
end
