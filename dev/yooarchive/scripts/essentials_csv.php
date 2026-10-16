<?php

/**
 * Dev-only helper for the compatibility tests: creates a small CSV file and an
 * Essentials for YOOtheme Pro "CSV" source pointing to it (the same entry the
 * Essentials panel of the customizer writes in the custom_data of its plugin row).
 * Usage: php /dev-scripts/essentials_csv.php
 */

$root = '/var/www/html';
@mkdir($root . '/images/compat', 0775, true);
file_put_contents($root . '/images/compat/comuni.csv', "Comune,Provincia,Abitanti\nTorino,TO,848000\nMilano,MI,1371000\nRoma,RM,2750000\n");
chown($root . '/images/compat/comuni.csv', 'www-data');

$db   = new mysqli('db', 'joomla', (string) getenv('DEV_DB_PASSWORD'), 'joomla');
$row  = $db->query("SELECT extension_id, custom_data FROM jdev_extensions WHERE type='plugin' AND folder='system' AND element='yooessentials'")->fetch_assoc();
$data = json_decode($row['custom_data'] ?: '{}', true) ?: [];

$data['source']['sources'] = array_values(array_filter(
    $data['source']['sources'] ?? [],
    static fn ($s) => ($s['id'] ?? '') !== 'a1b2c3'
));
$data['source']['sources'][] = ['id' => 'a1b2c3', 'provider' => 'csv', 'name' => 'Comuni CSV', 'file' => 'images/compat/comuni.csv'];

$stmt = $db->prepare('UPDATE jdev_extensions SET custom_data = ? WHERE extension_id = ?');
$json = json_encode($data, JSON_UNESCAPED_SLASHES);
$stmt->bind_param('si', $json, $row['extension_id']);
$stmt->execute();
echo "Essentials CSV source a1b2c3 saved\n";
