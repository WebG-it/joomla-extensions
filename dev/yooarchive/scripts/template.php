<?php

/**
 * Dev-only helper to create, change or delete YOOtheme templates of the local test
 * site, so the assignment rules can be tested quickly. Everything it does can also
 * be done by hand in the customizer.
 *
 * php /dev-scripts/template.php set <id> <type> <name> <query-json> [layout.json] [status]
 * php /dev-scripts/template.php del <id>
 * php /dev-scripts/template.php list
 */

$db   = new mysqli('db', 'joomla', (string) getenv('DEV_DB_PASSWORD'), 'joomla');
$row  = $db->query("SELECT extension_id, custom_data FROM jdev_extensions WHERE type='plugin' AND folder='system' AND element='yootheme'")->fetch_assoc();
$data = json_decode($row['custom_data'] ?: '{}', true);
$cmd  = $argv[1] ?? 'list';

if ($cmd === 'set') {
    [, , $id, $type, $name, $query] = $argv;
    $tpl = $data['templates'][$id] ?? [];
    $tpl['type']  = $type;
    $tpl['name']  = $name;
    $tpl['query'] = json_decode($query, true) ?: [];

    if (!empty($argv[6])) {
        $tpl['layout'] = json_decode(file_get_contents($argv[6]), true, 512, JSON_THROW_ON_ERROR);
    }

    if (isset($argv[7])) {
        $tpl['status'] = $argv[7];
    }

    $data['templates'][$id] = $tpl;
} elseif ($cmd === 'del') {
    unset($data['templates'][$argv[2]]);
}

if ($cmd !== 'list') {
    $stmt = $db->prepare('UPDATE jdev_extensions SET custom_data = ? WHERE extension_id = ?');
    $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $stmt->bind_param('si', $json, $row['extension_id']);
    $stmt->execute();
}

foreach ($data['templates'] ?? [] as $id => $tpl) {
    echo $id, "\t", $tpl['type'] ?? '', "\t", $tpl['name'] ?? '', "\t", json_encode($tpl['query'] ?? []), "\t", $tpl['status'] ?? '', "\n";
}
