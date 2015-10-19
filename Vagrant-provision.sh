#!/usr/bin/env bash

function comment {
	echo -e "\e[31m $1 \e[0m"
}

comment "Create swapfile"
fallocate -l 2G /swapfile; mkswap /swapfile; swapon /swapfile
echo '/swapfile   none    swap    sw    0   0' >> /etc/fstab

sysctl vm.swappiness=10;         echo 'vm.swappiness = 10'         >> /etc/sysctl.conf
sysctl vm.vfs_cache_pressure=50; echo 'vm.vfs_cache_pressure = 50' >> /etc/sysctl.conf

comment "Installing packages"
yum -y install epel-release
yum -y install whois vim htop curl dos2unix gcc git mariadb-server mariadb nginx \
php-fpm php-mysqlnd php-cli php-pear php-pgsql php-apcu php-gd php-imap \
php-mcrypt php-xdebug php-memcached php-redis php-mbstring memcached

comment "Enabling services"
systemctl start  mariadb.service
systemctl enable mariadb.service
systemctl start  nginx.service
systemctl enable nginx.service

comment "Setting autostart"
chkconfig --levels 235 mariadb on
chkconfig --levels 235 nginx   on
chkconfig --levels 235 php-fpm on

comment "Creating symlink /vagrant/upload -> /var/www"
rmdir /var/www
ln -s /var/www /vagrant/upload
