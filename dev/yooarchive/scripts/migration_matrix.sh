#!/bin/bash
# Dev-only: migration matrix yooarchive 1.2.1 -> 2.0.0 + autoarchive 1.0.0, run from the host.
#   P=6 bash dev/yooarchive/scripts/migration_matrix.sh      (site container autoarch-j<P>-web-1)
# Needs the zips in the container's /dist (build/out): yooarchive 1.2.1 and 2.0.0, autoarchive 1.0.0.
C=autoarch-j$P-web-1
dcx() { MSYS_NO_PATHCONV=1 timeout 120 docker exec -w /var/www/html $C "$@"; }
inst() { dcx bash -c "cp /dist/$1 tmp/ && chown www-data tmp/$1 && su -s /bin/bash www-data -c 'php cli/joomla.php extension:install --path=/var/www/html/tmp/$1 -n' 2>&1 | grep -E 'OK|ERROR|WARNING|NOTE|automation|Archive' | cut -c1-110"; }
sql() { dcx php /dev-scripts/sql.php "$1"; }
state() { sql "SELECT element,enabled,JSON_EXTRACT(manifest_cache,'$.version') v,JSON_EXTRACT(params,'$.automation') auto FROM jdev_extensions WHERE element IN ('yooarchive','autoarchive') ORDER BY element"; }
clean() { dcx bash /dev-scripts/uninstall_ext.sh plugin yooarchive system >/dev/null 2>&1; dcx bash /dev-scripts/uninstall_ext.sh plugin autoarchive system >/dev/null 2>&1; }
behave() { # archive via API, move via API, Batch
  IDS=$(dcx php /dev-scripts/mk_published.php immobili 3); set -- $IDS
  dcx php /dev-scripts/api_state.php $1 state 2; dcx php /dev-scripts/api_state.php $2 catid 9; dcx php /dev-scripts/batch_ui.php 9 $3 | tail -1; }
S() { echo; echo "##### $*"; }
Y121=plg_system_yooarchive-1.2.1.zip; Y2=plg_system_yooarchive-2.0.0.zip; A1=plg_system_autoarchive-1.0.0.zip

S "S1 automation ON, Auto Archive first, then 2.0.0"; clean; inst $Y121; dcx php /dev-scripts/automation_on.php venduti | cut -c1-40
inst $A1; state; inst $Y2; state; behave
S "S2 automation ON, 2.0.0 first (refused), then Auto Archive, then 2.0.0"; clean; inst $Y121; dcx php /dev-scripts/automation_on.php venduti >/dev/null
inst $Y2; state; inst $A1; inst $Y2; state; behave
S "S3 automation OFF (configured, then off): 2.0.0 then Auto Archive"; clean; inst $Y121; dcx php /dev-scripts/automation_on.php venduti >/dev/null; dcx php /dev-scripts/automation_on.php off >/dev/null
inst $Y2; inst $A1; state
S "S4 Auto Archive alone, no Archive Layouts"; clean; inst $A1; state; dcx php /dev-scripts/automation_on.php venduti autoarchive | cut -c1-40; behave
S "S5 old ON, Auto Archive installed first with its automation OFF: 2.0.0 refused"; clean; inst $A1; inst $Y121; dcx php /dev-scripts/automation_on.php venduti >/dev/null; inst $Y2; state
S "S6 old ON, Auto Archive installed but disabled: refused; enabled and ON: accepted"; dcx php /dev-scripts/automation_on.php venduti autoarchive >/dev/null; sql "UPDATE jdev_extensions SET enabled=0 WHERE element='autoarchive'" >/dev/null; inst $Y2; state
sql "UPDATE jdev_extensions SET enabled=1 WHERE element='autoarchive'" >/dev/null; inst $Y2; state
S "S7 old ON but no category: update passes (nothing to carry over)"; clean; inst $Y121; sql "UPDATE jdev_extensions SET params=JSON_SET(params,'$.automation',1,'$.target_catid',0) WHERE element='yooarchive'" >/dev/null; inst $Y2; state
S "S8 old ON but no category, then Auto Archive installed: message asks for the category"; clean; inst $Y121; sql "UPDATE jdev_extensions SET params=JSON_SET(params,'$.automation',1,'$.target_catid',0) WHERE element='yooarchive'" >/dev/null; inst $A1; state
S "php error log"; dcx bash -c 'test -f administrator/logs/php-error.log && tail -3 administrator/logs/php-error.log || echo "(no php-error.log)"'
