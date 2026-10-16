<?php

/**
 * Dev-only: switches the plugin automation on (or off) with a target category alias,
 * as the plugin options screen would do.
 *   php /dev-scripts/automation_on.php venduti     (on, target "venduti")
 *   php /dev-scripts/automation_on.php off
 */

$db    = new mysqli('db', 'joomla', (string) getenv('DEV_DB_PASSWORD'), 'joomla');
$arg   = $argv[1] ?? 'venduti';
$row   = $db->query("SELECT extension_id, params FROM jdev_extensions WHERE element='yooarchive' AND folder='system'")->fetch_assoc();
$p     = json_decode($row['params'] ?: '{}', true) ?: [];

if ($arg === 'off') {
    $p['automation'] = 0;
} else {
    $cat = (int) $db->query("SELECT id FROM jdev_categories WHERE alias='" . $db->real_escape_string($arg) . "' AND extension='com_content'")->fetch_row()[0];
    $p   = array_merge($p, ['automation' => 1, 'target_catid' => $cat, 'move_on_archive' => 1, 'archive_on_move' => 1, 'force_archived' => 0, 'require_create' => 1]);
}

$json = json_encode($p);
$stmt = $db->prepare('UPDATE jdev_extensions SET params = ? WHERE extension_id = ?');
$stmt->bind_param('si', $json, $row['extension_id']);
$stmt->execute();
echo "params: $json\n";
