<?php

/**
 * Creates the "Archived Articles" menu items of the local dev site through the
 * Joomla Web Services API (all categories, "Venduti", "Venduti Milano") and two
 * category blogs ("Immobili", "Venduti") so articles get menu based URLs, as on a
 * real site (needed by the 4SEO redirect test).
 * Run from dev/yooarchive:
 *   docker compose exec web php /dev-scripts/seed_menu.php
 *
 * Dev-only tool, never part of the plugin package. Credentials from the DEV_* variables of .env.
 */

const API  = 'http://localhost/api/index.php/v1';
define('AUTH', 'admin:' . getenv('DEV_ADMIN_PASSWORD'));

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
        fwrite(STDERR, "$method $path -> $code " . substr((string) $raw, 0, 600) . "\n");
    }

    return json_decode((string) $raw, true) ?: [];
}

$db  = new mysqli('db', 'joomla', (string) getenv('DEV_DB_PASSWORD'), 'joomla');
$cid = (int) $db->query("SELECT extension_id FROM jdev_extensions WHERE element='com_content' AND type='component'")->fetch_row()[0];
$ven = (int) $db->query("SELECT id FROM jdev_categories WHERE alias='venduti' AND extension='com_content'")->fetch_row()[0];
$mil = (int) $db->query("SELECT id FROM jdev_categories WHERE alias='venduti-milano' AND extension='com_content'")->fetch_row()[0];
$imm = (int) $db->query("SELECT id FROM jdev_categories WHERE alias='immobili' AND extension='com_content'")->fetch_row()[0];

$items = [
    ['Archivio', 'archivio', 'index.php?option=com_content&view=archive', []],
    ['Venduti archiviati', 'venduti-archiviati', 'index.php?option=com_content&view=archive&catid[0]=' . $ven, [$ven]],
    ['Venduti Milano', 'venduti-milano-archiviati', 'index.php?option=com_content&view=archive&catid[0]=' . $mil, [$mil]],
    ['Immobili', 'immobili', 'index.php?option=com_content&view=category&layout=blog&id=' . $imm, null],
    ['Venduti', 'venduti', 'index.php?option=com_content&view=category&layout=blog&id=' . $ven, null],
];

foreach ($items as [$title, $alias, $link, $catids]) {
    if ($db->query("SELECT id FROM jdev_menu WHERE alias='" . $db->real_escape_string($alias) . "' AND client_id=0")->num_rows) {
        continue;
    }

    $request = $catids === null ? ['id' => (int) substr($link, strrpos($link, '=') + 1)] : ['catid' => $catids];
    $res     = api('POST', '/menus/site/items', [
        'title' => $title, 'alias' => $alias, 'menutype' => 'mainmenu', 'type' => 'component', 'link' => $link,
        'component_id' => $cid, 'published' => 1, 'language' => '*', 'parent_id' => 1, 'access' => 1,
        'request' => $request, 'params' => ['display_num' => '5', 'orderby_sec' => 'rdate', 'order_date' => 'created'],
    ]);
    echo $title . ' -> ' . ($res['data']['id'] ?? 'error') . "\n";
}
