<?php

/**
 * Dev-only: uploads an extension zip to the JED Checker "master" component exactly as its
 * upload form does (login, CSRF token, multipart POST), so the check can then be run from
 * the component screen.   php /dev-scripts/jed_upload.php /var/www/html/tmp/plg_x.zip
 * The admin password comes from DEV_ADMIN_PASSWORD in the container.
 */

$base = 'http://localhost/administrator/index.php';
$jar  = tempnam(sys_get_temp_dir(), 'cj');

function http(string $url, string $jar, ?array $post = null): string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar]);

    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }

    return (string) curl_exec($ch);
}

function token(string $html): string
{
    preg_match('/name="([0-9a-f]{32})" value="1"/', $html, $m);

    return $m[1] ?? '';
}

$html = http($base, $jar);
http($base, $jar, ['username' => 'admin', 'passwd' => getenv('DEV_ADMIN_PASSWORD'), 'option' => 'com_login', 'task' => 'login', token($html) => 1]);
$html = http($base . '?option=com_jedchecker&view=uploads', $jar);
$out  = http($base . '?option=com_jedchecker&task=uploads.upload', $jar, [
    'extension' => new CURLFile($argv[1], 'application/zip', basename($argv[1])),
    token($html) => 1,
]);
echo substr(trim(preg_replace("/\s+/", " ", strip_tags(preg_replace("/<script.*?<\/script>/s", "", $out)))), 0, 700), "
";
unlink($jar);
