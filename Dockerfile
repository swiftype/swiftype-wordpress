# Use the latest available CentOS 6 image
FROM centos:centos6

run rpm -Uh --quiet http://pkgs.repoforge.org/rpmforge-release/rpmforge-release-0.5.2-2.el6.rf.x86_64.rpm || :

run curl -s http://yum.swiftype.net/swiftype.repo > /etc/yum.repos.d/swiftype.repo

run yum -y update

run yum -y install libyaml ok-ruby-2.0 rubygems make gcc gcc-c++ kernel-devel libxml2 libxml2-devel libxslt libxslt-devel openssl-devel 

run yum -y install Percona-Server-server-55 git which tar wget bzip2 libcurl-devel libjpeg-devel libpng-devel libmcrypt-devel readline-devel libtidy-devel php-xml php-pear php-mysql sendmail

ENV HOME /php
WORKDIR /php

# install phpenv
RUN curl https://raw.githubusercontent.com/CHH/phpenv/master/bin/phpenv-install.sh | bash
RUN echo 'export PATH="/php/.phpenv/bin:$PATH"' >> /etc/bashrc
RUN mkdir -p /php/.phpenv/plugins; \
    cd /php/.phpenv/plugins; \
        git clone https://github.com/CHH/php-build.git

RUN echo 'eval "$(phpenv init -)"' >> /etc/bashrc

ENV PATH /php/.phpenv/shims:/php/.phpenv/bin:$PATH

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/bin/composer
ENV PATH /php/.composer/vendor/bin:$PATH

RUN phpenv install 5.3.28
RUN phpenv install 5.4.23 
RUN phpenv install 5.5.7

RUN composer global require 'phpunit/phpunit=3.7.*'

ADD ./ /data

#RUN /data/scripts/ci_build.sh
