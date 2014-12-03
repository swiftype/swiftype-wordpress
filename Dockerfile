# Use the latest available CentOS 6 image
FROM centos:centos6

run rpm -Uh --quiet http://pkgs.repoforge.org/rpmforge-release/rpmforge-release-0.5.2-2.el6.rf.x86_64.rpm || :

run curl -s http://yum.swiftype.net/swiftype.repo > /etc/yum.repos.d/swiftype.repo

run rpm -Uh --quiet http://dl.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm || :

run yum -y update

run yum -y install libyaml ok-ruby-2.0 rubygems make gcc gcc-c++ kernel-devel libxml2 libxml2-devel libxslt libxslt-devel openssl-devel 

run yum -y install Percona-Server-server-55 git which tar wget bzip2 libcurl-devel libjpeg-devel libpng-devel libmcrypt-devel readline-devel libtidy-devel php-xml php-pear php-mysql sendmail bzip2-devel libicu-devel

ENV HOME /php
WORKDIR /php

# Install phpbrew
RUN curl -L -O https://github.com/phpbrew/phpbrew/raw/master/phpbrew
RUN chmod +x phpbrew
RUN mv phpbrew /usr/bin/phpbrew
RUN phpbrew init
RUN echo "source ~/.phpbrew/bashrc" >> ~/.bashrc

# Install composer
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/bin/composer
ENV PATH /php/.composer/vendor/bin:$PATH

# Install phpunit
RUN composer global require 'phpunit/phpunit=3.7.*'

# Install php
RUN phpbrew install 5.3.28 +mysql +ctype +curl +fileinfo +ftp +gd +iconv +mbstring
RUN phpbrew install 5.4.23 +mysql +ctype +curl +fileinfo +ftp +gd +iconv +mbstring
RUN phpbrew install 5.5.7 +mysql +ctype +curl +fileinfo +ftp +gd +iconv +mbstring

ADD ./ /data
