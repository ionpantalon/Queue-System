#!/bin/bash
#Installation for resque queueing system

#==== install rails
#if ! hash "rails" >/dev/null 2>&1; then
yum install gcc-c++ patch readline readline-devel zlib zlib-devel
yum install libyaml-devel libffi-devel openssl-devel make
yum install bzip2 autoconf automake libtool bison iconv-devel sqlite-devel

#sudo gpg2 --keyserver hkp://keys.gnupg.net --recv-keys 409B6B1796C275462A1703113804BB82D39DC0E3
#\curl -sSL https://get.rvm.io | bash -s stable --ruby
#source /usr/local/rvm/scripts/rvm#

#rvm reload
#rvm install 2.2.3
#rvm use 2.2.3 --default

rpm -Uvh https://github.com/feedforce/ruby-rpm/releases/download/2.2.2/ruby-2.2.2-1.el6.x86_64.rpm
gem install bundle
gem install rails --no-ri --no-rdoc

#fi
#==== install redis
if ! hash "redis" >/dev/null 2>&1; then

  wget http://dl.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm
  wget http://rpms.famillecollet.com/enterprise/remi-release-6.rpm
  sudo rpm -Uvh remi-release-6*.rpm epel-release-6*.rpm
  sudo yum install redis –y
  sudo chkconfig --level 2345 redis on
  sudo service redis start
fi
#==== install nodejs
if ! hash "node" >/dev/null 2>&1; then
  yum install nodejs
fi

# ============ clone resque brain and php resque ===================
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/bin/composer
git clone https://github.com/chrisboulton/php-resque.git
cd php-resque
composer install
cd ..


git clone https://github.com/stitchfix/resque-brain.git
cd resque-brain
gem install bundle
bundle install

cat <<EOT>> .env
RESQUE_BRAIN_INSTANCES=www
RESQUE_BRAIN_INSTANCES_www=redis://localhost:6379
HTTP_AUTH_USERNAME=a
HTTP_AUTH_PASSWORD=a
EOT

if [ ! -f /runrails.sh ]; then
echo exec rails server -p 3001 -b 0.0.0.0 > runrails.sh
fi
