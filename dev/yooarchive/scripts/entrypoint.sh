#!/bin/bash
# Extracts the official Joomla 6.2.0 package into the site volume on first start,
# then runs Apache in the foreground.
set -e

if [ ! -f /var/www/html/index.php ]; then
    echo "Extracting Joomla 6.2.0 into /var/www/html"
    php -r '$z = new ZipArchive(); if ($z->open("/joomla.zip") !== true) { exit(1); } $z->extractTo("/var/www/html"); $z->close();'
    chown -R www-data:www-data /var/www/html
fi

# Show PHP errors in the log, never on screen.
cat > /usr/local/etc/php/conf.d/zz-dev.ini <<'INI'
display_errors=Off
log_errors=On
error_log=/var/www/html/administrator/logs/php-error.log
error_reporting=E_ALL
memory_limit=512M
upload_max_filesize=64M
post_max_size=64M
INI

exec apache2-foreground
