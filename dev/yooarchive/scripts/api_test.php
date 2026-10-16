<?php

/**
 * Dev-only: exercises the automation through the Joomla Web Services API.
 *   docker compose exec web php /dev-scripts/api_test.php
 * Creates articles "API test ..." and prints catid/state after each step.
 */

const API  = 'http://localhost/api/index.php/v1';
const AUTH = 'admin:joomla-dev-admin';

function api(string $method, string $path, ?array $body = null): array
{
    $ch = curl_init(API . $path);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/vnd.api+json', 'Content-Type: application/json'],
        CURLOPT_USERPWD        => AUTH,
    ]);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code >= 300) {
        echo "  HTTP $code " . substr((string) $raw, 0, 300) . "\n";
    }

    return json_decode((string) $raw, true) ?: [];
}

$db  = new mysqli('db', 'joomla', 'joomla-dev', 'joomla');
$row = static fn (int $id) => $db->query("SELECT catid, state FROM jdev_content WHERE id=$id")->fetch_assoc();
$cat = static fn (string $alias) => (int) $db->query("SELECT id FROM jdev_categories WHERE alias='$alias' AND extension='com_content'")->fetch_row()[0];

$immobili = $cat('immobili');
$venduti  = $cat('venduti');
$suffix   = date('His');

$a = (int) (api('POST', '/content/articles', ['title' => "API test A $suffix", 'alias' => "api-test-a-$suffix", 'catid' => $immobili, 'articletext' => '<p>A</p>', 'state' => 1, 'language' => '*'])['data']['id'] ?? 0);
echo "A created published in Immobili: " . json_encode($row($a)) . "\n";
api('PATCH', "/content/articles/$a", ['state' => 2]);
echo "A after PATCH state=2: " . json_encode($row($a)) . "  (expected catid $venduti, state 2 when automation is on)\n";

$b = (int) (api('POST', '/content/articles', ['title' => "API test B $suffix", 'alias' => "api-test-b-$suffix", 'catid' => $venduti, 'articletext' => '<p>B</p>', 'state' => 1, 'language' => '*'])['data']['id'] ?? 0);
echo "B created published directly in Venduti: " . json_encode($row($b)) . "  (expected state 2 when automation is on)\n";
