#!/bin/bash
# Installs a third-party extension for the compatibility tests from a folder of
# installed files mounted in /vendor (docker-compose.compat.yml), with Joomla's
# "Discover" installer: same manifests, install SQL and install scripts as a
# normal installation. Dev-only tool, never part of the plugin package.
#   docker compose ... exec web bash /dev-scripts/install_vendor.sh forseo-7.2.0.2824
set -e
cd /var/www/html
NAME="${1:?usage: install_vendor.sh <folder in /vendor>}"
SRC="/vendor/$NAME"
[ -d "$SRC" ] || { echo "!! $SRC not found" >&2; exit 1; }

echo "== Copying $NAME"
cp -a "$SRC"/. /var/www/html/
chown -R www-data:www-data /var/www/html

echo "== Discover install"
su -s /bin/bash www-data -c "php cli/joomla.php extension:discover -n"
su -s /bin/bash www-data -c "php cli/joomla.php extension:discover:install -n"
