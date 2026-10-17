<?php

/**
 * Dev-only: creates published test articles through the Web Services API and prints their ids.
 *   php /dev-scripts/mk_published.php <category alias> <count>
 */
$db  = new mysqli('db', 'joomla', (string) getenv('DEV_DB_PASSWORD'), 'joomla');
$cat = (int) $db->query("SELECT id FROM jdev_categories WHERE alias='" . $db->real_escape_string($argv[1] ?? 'immobili') . "' AND extension='com_content'")->fetch_row()[0];
$ids = [];

for ($i = 0; $i < (int) ($argv[2] ?? 1); $i++) {
    $s  = bin2hex(random_bytes(3));
    $ch = curl_init('http://localhost/api/index.php/v1/content/articles');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/vnd.api+json', 'Content-Type: application/json'],
        CURLOPT_USERPWD        => 'admin:' . getenv('DEV_ADMIN_PASSWORD'),
        CURLOPT_POSTFIELDS     => json_encode(['title' => "Split test $s", 'alias' => "split-test-$s", 'catid' => $cat, 'articletext' => '<p>t</p>', 'state' => 1, 'language' => '*']),
    ]);
    $ids[] = (int) (json_decode((string) curl_exec($ch), true)['data']['id'] ?? 0);
}

echo implode(' ', $ids), "\n";
