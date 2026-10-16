<?php

/**
 * Dev-only: changes the state or the category of one article through the Joomla Web
 * Services API (same model save as the article editor).
 *   php /dev-scripts/api_state.php <id> state <0|1|2|-2>
 *   php /dev-scripts/api_state.php <id> catid <category id>
 */

[$s, $id, $field, $value] = $argv + [null, null, null, null];
$ch = curl_init('http://localhost/api/index.php/v1/content/articles/' . (int) $id);
curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST  => 'PATCH',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Accept: application/vnd.api+json', 'Content-Type: application/json'],
    CURLOPT_USERPWD        => 'admin:' . getenv('DEV_ADMIN_PASSWORD'),
    CURLOPT_POSTFIELDS     => json_encode([$field => (int) $value]),
]);
$raw  = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$db   = new mysqli('db', 'joomla', (string) getenv('DEV_DB_PASSWORD'), 'joomla');
$row  = $db->query('SELECT catid, state FROM jdev_content WHERE id=' . (int) $id)->fetch_assoc();
echo "HTTP $code -> article $id catid {$row['catid']} state {$row['state']}\n";
if ($code >= 300) {
    echo substr((string) $raw, 0, 300), "\n";
}
