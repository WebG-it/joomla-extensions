#!/bin/bash
# One-time setup of the local test site. Run from dev/yooarchive:
#   docker compose exec web bash /dev-scripts/setup.sh
# Idempotent: steps already done are skipped.
set -e
cd /var/www/html

ADMIN_USER=admin
ADMIN_PASS="${DEV_ADMIN_PASSWORD:?DEV_ADMIN_PASSWORD missing, see .env.example}"
DB_PASS="${DEV_DB_PASSWORD:?DEV_DB_PASSWORD missing, see .env.example}"
DB_PREFIX=jdev_
SQL() { php /dev-scripts/sql.php "$1"; }

if [ ! -f configuration.php ]; then
    echo "== Installing Joomla"
    php installation/joomla.php install \
        --site-name="YOOarchive Dev" \
        --admin-user="Dev Admin" \
        --admin-username="$ADMIN_USER" \
        --admin-password="$ADMIN_PASS" \
        --admin-email="admin@example.test" \
        --db-type=mysqli --db-host=db --db-user=joomla --db-pass="$DB_PASS" \
        --db-name=joomla --db-prefix="$DB_PREFIX" --db-encryption=0 -n
    rm -rf installation
    cp htaccess.txt .htaccess
    php cli/joomla.php config:set sef_rewrite=true live_site="$DEV_SITE_URL" debug=false error_reporting=maximum log_path=/var/www/html/administrator/logs -n || true
    chown -R www-data:www-data /var/www/html
fi

if [ ! -d templates/yootheme ]; then
    echo "== Installing YOOtheme Pro from /yootheme"
    PKG=$(ls /yootheme/*.zip 2>/dev/null | head -1 || true)
    if [ -n "$PKG" ]; then
        php cli/joomla.php extension:install --path="$PKG" -n
    elif [ -d /yootheme/templates/yootheme ]; then
        for d in templates/yootheme plugins/system/yootheme plugins/fields/location plugins/fields/mediafile \
                 modules/mod_yootheme_builder administrator/modules/mod_yootheme_link; do
            if [ -d "/yootheme/$d" ]; then mkdir -p "$(dirname "$d")"; cp -a "/yootheme/$d" "$d"; fi
        done
        chown -R www-data:www-data templates plugins modules administrator/modules
        php cli/joomla.php extension:discover -n
        php cli/joomla.php extension:discover:install -n
    else
        echo "!! No YOOtheme Pro found in /yootheme (see README.md)"; exit 1
    fi
    SQL "UPDATE ${DB_PREFIX}extensions SET enabled=1 WHERE element IN ('yootheme','location','mediafile','mod_yootheme_builder','mod_yootheme_link')"
    SQL "UPDATE ${DB_PREFIX}template_styles SET home='0' WHERE client_id=0"
    SQL "UPDATE ${DB_PREFIX}template_styles SET home='1' WHERE client_id=0 AND template='yootheme' ORDER BY id LIMIT 1"
fi

if [ ! -d administrator/language/it-IT ]; then
    echo "== Installing the official Italian language pack (to test the it-IT strings)"
    curl -sL -o tmp/it-IT.zip "https://downloads.joomla.org/language-packs/translations-joomla6/downloads/joomla6-italian/6-2-0-1/it-it_joomla_lang_full_6-2-0v1-zip?format=zip"
    chown www-data tmp/it-IT.zip
    su -s /bin/bash www-data -c "php cli/joomla.php extension:install --path=/var/www/html/tmp/it-IT.zip -n" || true
fi

echo "== Disabling guided tours autostart (it redirects automated backend tests)"
SQL "UPDATE ${DB_PREFIX}extensions SET enabled=0 WHERE folder='system' AND element='guidedtours'"

echo "== Enabling Web Services basic auth (local seeding only)"
SQL "UPDATE ${DB_PREFIX}extensions SET enabled=1 WHERE folder IN ('api-authentication','webservices') AND element IN ('basic','content','menus','plugins','installer')"

echo "== Done. Site: $DEV_SITE_URL  Admin: $DEV_SITE_URL/administrator ($ADMIN_USER)"
