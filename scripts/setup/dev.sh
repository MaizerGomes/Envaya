apt-get -y install git-core 
apt-get -y --allow-unauthenticated install php-pear php5-cgi

pear channel-discover pear.phpunit.de
pear channel-discover pear.symfony-project.com
pear channel-discover components.ez.no
pear install phpunit/PHPUnit
