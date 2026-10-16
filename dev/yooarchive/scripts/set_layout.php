<?php

/**
 * Dev-only helper: stores a builder layout JSON into a YOOtheme template of the
 * local test site (YOOtheme keeps its templates in the custom_data of the
 * plg_system_yootheme extension row).
 * Usage: php /dev-scripts/set_layout.php <template-id> /dev-layouts/<file>.json
 *        php /dev-scripts/set_layout.php <template-id> --print
 */

[$script, $id, $file] = $argv + [null, null, null];

if (!$id || !$file) {
    fwrite(STDERR, "Usage: php set_layout.php <template-id> <layout.json|--print>\n");
    exit(1);
}

$db   = new mysqli('db', 'joomla', 'joomla-dev', 'joomla');
$row  = $db->query("SELECT extension_id, custom_data FROM jdev_extensions WHERE type='plugin' AND folder='system' AND element='yootheme'")->fetch_assoc();
$data = json_decode($row['custom_data'] ?: '{}', true);

if (!isset($data['templates'][$id])) {
    fwrite(STDERR, "Template $id not found. Templates: " . implode(', ', array_keys($data['templates'] ?? [])) . "\n");
    exit(1);
}

if ($file === '--print') {
    echo json_encode($data['templates'][$id], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
    exit(0);
}

$data['templates'][$id]['layout'] = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
$stmt = $db->prepare('UPDATE jdev_extensions SET custom_data = ? WHERE extension_id = ?');
$json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$stmt->bind_param('si', $json, $row['extension_id']);
$stmt->execute();
echo "Layout stored in template $id\n";
