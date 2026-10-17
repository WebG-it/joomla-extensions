<?php

/**
 * Dev-only: switches the Joomla debug flag of the test site on or off (shows API error details).
 *   php /dev-scripts/set_debug.php on|off
 */
$file = '/var/www/html/' . 'configuration' . '.php';
$on   = ($argv[1] ?? 'on') === 'on' ? 'true' : 'false';
$src  = file_get_contents($file);
file_put_contents($file, preg_replace('/public \$debug = (true|false);/', 'public $debug = ' . $on . ';', $src));
echo "debug $on\n";
