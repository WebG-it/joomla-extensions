<?php

/**
 * Seeds the local dev site with categories, custom fields and articles through the
 * Joomla Web Services API (so every save goes through the Joomla models).
 * Run from dev/yooarchive:
 *   docker compose exec web php /dev-scripts/seed.php
 *
 * Dev-only tool, never part of the plugin package. Dummy local credentials.
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
    $data = json_decode((string) $raw, true) ?: [];

    if ($code >= 300) {
        fwrite(STDERR, "$method $path -> $code " . substr((string) $raw, 0, 400) . "\n");
    }

    return $data;
}

function id(array $res): int
{
    return (int) ($res['data']['id'] ?? $res['data']['attributes']['id'] ?? 0);
}

$existing = api('GET', '/content/categories?page[limit]=100');
$cats     = [];

foreach ($existing['data'] ?? [] as $row) {
    $cats[$row['attributes']['alias']] = (int) $row['attributes']['id'];
}

foreach ([['Immobili', 'immobili', 1], ['Venduti', 'venduti', 1], ['Blog', 'blog', 1]] as [$title, $alias, $parent]) {
    if (!isset($cats[$alias])) {
        $cats[$alias] = id(api('POST', '/content/categories', ['title' => $title, 'alias' => $alias, 'published' => 1, 'language' => '*', 'parent_id' => $parent]));
    }
}

if (!isset($cats['venduti-milano'])) {
    $cats['venduti-milano'] = id(api('POST', '/content/categories', [
        'title' => 'Venduti Milano', 'alias' => 'venduti-milano', 'published' => 1, 'language' => '*', 'parent_id' => $cats['venduti'],
    ]));
}

echo 'Categories: ' . json_encode($cats) . "\n";

$fields = [];

foreach (api('GET', '/fields/content/articles')['data'] ?? [] as $row) {
    $fields[$row['attributes']['name']] = (int) $row['attributes']['id'];
}

$defs = [
    ['prezzo', 'Prezzo', 'text', []],
    ['superficie', 'Superficie mq', 'integer', ['first' => 20, 'last' => 500, 'step' => 10]],
    ['comune', 'Comune', 'list', ['options' => [
        'options0' => ['name' => 'Milano', 'value' => 'milano'],
        'options1' => ['name' => 'Como', 'value' => 'como'],
        'options2' => ['name' => 'Cantù', 'value' => 'cantu'],
    ]]],
];

foreach ($defs as [$name, $label, $type, $fieldparams]) {
    if (!isset($fields[$name])) {
        $fields[$name] = id(api('POST', '/fields/content/articles', [
            'title' => $label, 'name' => $name, 'label' => $label, 'description' => '', 'default_value' => '', 'note' => '', 'type' => $type, 'context' => 'com_content.article',
            'state' => 1, 'language' => '*', 'access' => 1, 'fieldparams' => $fieldparams, 'params' => ['display' => 0],
            'assigned_cat_ids' => [],
        ]));
    }
}

echo 'Fields: ' . json_encode($fields) . "\n";

$have = [];

foreach (api('GET', '/content/articles?page[limit]=200')['data'] ?? [] as $row) {
    $have[$row['attributes']['alias']] = true;
}

$images  = ['images/headers/blue-flower.jpg', 'images/headers/maple.jpg', 'images/headers/windows.jpg', 'images/headers/raindrops.jpg', 'images/headers/walden-pond.jpg'];
$comuni  = ['milano', 'como', 'cantu'];
$created = 0;

// 14 archived articles in Venduti (and its child), across two years and several months.
for ($i = 1; $i <= 14; $i++) {
    $alias = 'venduto-' . $i;
    $year  = $i <= 8 ? 2025 : 2026;
    $month = ($i % 6) + 1;
    $date  = sprintf('%04d-%02d-%02d 10:00:00', $year, $month, 5 + $i);
    $catid = $i % 5 === 0 ? $cats['venduti-milano'] : $cats['venduti'];

    if (isset($have[$alias])) {
        continue;
    }

    $res = api('POST', '/content/articles', [
        'title' => sprintf('Appartamento venduto %02d', $i), 'alias' => $alias, 'catid' => $catid,
        'articletext' => '<p>Scheda di prova ' . $i . ' archiviata.</p>', 'state' => 2, 'language' => '*',
        'created' => $date, 'publish_up' => $date, 'featured' => 0,
        'images' => ['image_intro' => $images[$i % 5], 'image_intro_alt' => 'Foto ' . $i],
        'prezzo' => (100 + 15 * $i) . '.000 euro', 'superficie' => 40 + 10 * $i, 'comune' => $comuni[$i % 3],
    ]);
    $created += id($res) ? 1 : 0;
}

// 3 archived Blog articles (archive page without category filter shows them too).
for ($i = 1; $i <= 3; $i++) {
    $alias = 'blog-archiviato-' . $i;

    if (isset($have[$alias])) {
        continue;
    }

    $date = sprintf('2024-%02d-10 09:00:00', $i + 2);
    $res  = api('POST', '/content/articles', [
        'title' => 'Notizia archiviata ' . $i, 'alias' => $alias, 'catid' => $cats['blog'],
        'articletext' => '<p>Notizia vecchia ' . $i . '.</p>', 'state' => 2, 'language' => '*',
        'created' => $date, 'publish_up' => $date, 'images' => ['image_intro' => $images[$i]],
    ]);
    $created += id($res) ? 1 : 0;
}

// 4 published articles in Immobili, used to test the automation.
for ($i = 1; $i <= 4; $i++) {
    $alias = 'disponibile-' . $i;

    if (isset($have[$alias])) {
        continue;
    }

    $res = api('POST', '/content/articles', [
        'title' => 'Villa disponibile ' . $i, 'alias' => $alias, 'catid' => $cats['immobili'],
        'articletext' => '<p>Immobile disponibile ' . $i . '.</p>', 'state' => 1, 'language' => '*',
        'images' => ['image_intro' => $images[$i]], 'prezzo' => (300 + $i) . '.000 euro', 'superficie' => 150, 'comune' => 'como',
    ]);
    $created += id($res) ? 1 : 0;
}

echo "Articles created: $created\n";
