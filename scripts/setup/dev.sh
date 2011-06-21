
SETUP_DIR=$(cd `dirname $0` && pwd)
SCRIPT_DIR=`dirname $SETUP_DIR`
INSTALL_DIR=`dirname $SCRIPT_DIR`

apt-get -y install git-core 
apt-get -y install default-jre-headless
apt-get -y --allow-unauthenticated install php-pear php5-cgi

pear upgrade pear
pear channel-discover pear.phpunit.de
pear channel-discover pear.symfony-project.com
pear channel-discover components.ez.no
pear install phpunit/PHPUnit
