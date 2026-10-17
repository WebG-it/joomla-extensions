<?php

/**
 * Dev-only: runs the Batch "move" of the article list exactly as the browser does
 * (administrator login, CSRF token, task=articles.batch) and prints catid/state after.
 *   php /dev-scripts/batch_ui.php <category id> <article id> [<article id> ...]
 * The admin password comes from DEV_ADMIN_PASSWORD in the container.
 */

$base = 'http://localhost/administrator/index.php';
$jar  = tempnam(sys_get_temp_dir(), 'cj');
$ids  = array_slice($argv, 2);
$cat  = (int) ($argv[1] ?? 0);

function http(string $method, string $url, string $jar, array $post = []): string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR      => $jar,
        CURLOPT_COOKIEFILE     => $jar,
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }

    return (string) curl_exec($ch);
}

function token(string $html): string
{
    preg_match('/name="([0-9a-f]{32})" value="1"/', $html, $m);

    return $m[1] ?? '';
}

$html = http('GET', $base, $jar);
http('POST', $base, $jar, [
    'username' => 'admin', 'passwd' => getenv('DEV_ADMIN_PASSWORD'), 'option' => 'com_login', 'task' => 'login', token($html) => 1,
]);
$html = http('GET', $base . '?option=com_content&view=articles', $jar);
$tok  = token($html);
$out  = http('POST', $base . '?option=com_content&view=articles', $jar, [
    'task'                  => 'article.batch',
    'cid'                   => $ids,
    'batch'                 => ['category_id' => $cat, 'move_copy' => 'm', 'language_id' => '', 'access' => '', 'tag' => ''],
    $tok                    => 1,
]);
preg_match_all('/<joomla-alert[^>]*>.*?<\/joomla-alert>/s', $out, $m);
echo $tok === '' ? "NO TOKEN (login failed?)\n" : '';
foreach ($m[0] as $a) {
    echo '  alert: ', trim(preg_replace('/\s+/', ' ', strip_tags($a))), "\n";
}
$db = new mysqli('db', 'joomla', (string) getenv('DEV_DB_PASSWORD'), 'joomla');
foreach ($ids as $id) {
    $r = $db->query('SELECT id, catid, state FROM jdev_content WHERE id=' . (int) $id)->fetch_assoc();
    echo "  article {$r['id']}: catid {$r['catid']} state {$r['state']}\n";
}
unlink($jar);
