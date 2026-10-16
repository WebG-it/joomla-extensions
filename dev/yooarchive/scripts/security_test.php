<?php

/**
 * Dev-only: checks the permission rules of the automation (1.1.0) through the API.
 *   docker compose exec web php /dev-scripts/security_test.php
 *
 * Creates (once) a "manager" test user in the Manager group, allowed to use the API,
 * then archives articles as that user with and without Create permission on the
 * archive category. Prints catid/state after each step.
 */

const API = 'http://localhost/api/index.php/v1';

$pass = (string) getenv('DEV_ADMIN_PASSWORD');
$db   = new mysqli('db', 'joomla', (string) getenv('DEV_DB_PASSWORD'), 'joomla');

function api(string $method, string $path, ?array $body, string $auth): array
{
    $ch = curl_init(API . $path);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/vnd.api+json', 'Content-Type: application/json'],
        CURLOPT_USERPWD        => $auth,
    ]);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code >= 300) {
        echo "  HTTP $code " . substr((string) $raw, 0, 200) . "\n";
    }

    return json_decode((string) $raw, true) ?: [];
}

$admin   = 'admin:' . $pass;
$manager = 'manager:' . $pass;
$cat     = static fn (string $alias) => (int) $db->query("SELECT id FROM jdev_categories WHERE alias='$alias' AND extension='com_content'")->fetch_row()[0];
$row     = static fn (int $id) => $db->query("SELECT catid, state FROM jdev_content WHERE id=$id")->fetch_assoc();
$venduti = $cat('venduti');
$immob   = $cat('immobili');

if (!$db->query("SELECT id FROM jdev_users WHERE username='manager'")->num_rows) {
    api('POST', '/users', ['name' => 'Test Manager', 'username' => 'manager', 'email' => 'manager@example.test', 'password' => $pass, 'password2' => $pass, 'groups' => [6], 'block' => 0], $admin);
}

// Allow the Manager group (6) to log in to the API on this test site.
$rules = json_decode($db->query("SELECT rules FROM jdev_assets WHERE id=1")->fetch_row()[0], true);
$rules['core.login.api'][6] = 1;
$db->query("UPDATE jdev_assets SET rules='" . $db->real_escape_string(json_encode($rules)) . "' WHERE id=1");

$setCreate = static function (?int $value) use ($db, $venduti) {
    $r = json_decode($db->query("SELECT rules FROM jdev_assets WHERE name='com_content.category.$venduti'")->fetch_row()[0] ?: '{}', true) ?: [];
    if ($value === null) {
        unset($r['core.create'][6]);
    } else {
        $r['core.create'][6] = $value;
    }
    $db->query("UPDATE jdev_assets SET rules='" . $db->real_escape_string(json_encode($r ?: new stdClass())) . "' WHERE name='com_content.category.$venduti'");
};
$setParam = static function (string $key, $value) use ($db) {
    $db->query("UPDATE jdev_extensions SET params=JSON_SET(params,'$.$key',$value) WHERE element='yooarchive'");
};

$new = static function (string $label) use ($immob, $admin) {
    $alias = 'sec-' . strtolower($label) . '-' . date('His');
    return (int) (api('POST', '/content/articles', ['title' => "Security test $label", 'alias' => $alias, 'catid' => $immob, 'articletext' => '<p>x</p>', 'state' => 1, 'language' => '*'], $admin)['data']['id'] ?? 0);
};

$setParam('automation', 1);
$setParam('target_catid', $venduti);

echo "1) manager, Create DENIED on archive category, require_create ON\n";
$setCreate(0);
$setParam('require_create', 1);
$a = $new('A');
api('PATCH', "/content/articles/$a", ['state' => 2], $manager);
echo "   article $a: " . json_encode($row($a)) . "  expected state 2, catid $immob (not moved)\n";

echo "2) manager, Create DENIED, require_create OFF\n";
$setParam('require_create', 0);
$b = $new('B');
api('PATCH', "/content/articles/$b", ['state' => 2], $manager);
echo "   article $b: " . json_encode($row($b)) . "  expected state 2, catid $venduti (moved)\n";

echo "3) manager, Create ALLOWED, require_create ON\n";
$setCreate(null);
$setParam('require_create', 1);
$c = $new('C');
api('PATCH', "/content/articles/$c", ['state' => 2], $manager);
echo "   article $c: " . json_encode($row($c)) . "  expected state 2, catid $venduti (moved: Manager inherits Create)\n";

echo "4) archive category set to a missing id (99999)\n";
$setParam('target_catid', 99999);
$d = $new('D');
api('PATCH', "/content/articles/$d", ['state' => 2], $admin);
echo "   article $d: " . json_encode($row($d)) . "  expected state 2, catid $immob (automation paused)\n";
$setParam('target_catid', $venduti);
