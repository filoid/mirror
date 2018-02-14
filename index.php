<?php
ini_set('session.save_path',realpath(dirname($_SERVER['DOCUMENT_ROOT']) . '/tmp'));
session_start();
ob_start();

$base = 'https://9anime.is';
$reff = 'https://9anime.is/';
$useragent = 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0';

$ckfile       = '/tmp/cookies-' . session_id();
$cookiedomain = str_replace('http://www.', '', $base);
$cookiedomain = str_replace('https://www.', '', $cookiedomain);
$cookiedomain = str_replace('www.', '', $cookiedomain);
$url          = $base . $_SERVER['REQUEST_URI'];
if (array_key_exists('HTTPS', $_SERVER) && $_SERVER["HTTPS"] === "on") {
    $mydomain = 'https://' . $_SERVER['HTTP_HOST'];
} else {
    $mydomain = 'http://' . $_SERVER['HTTP_HOST'];
}
$curlSession = curl_init();
$headerz[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
$headerz[] = "Accept-Encoding: gzip, deflate, br";
$headerz[] = "Accept-Language: en-US,en;q=0.8";
$headerz[] = "Connection: keep-alive";
$headerz[] = "Upgrade-Insecure-Requests: 1";
curl_setopt($curlSession, CURLOPT_URL, $url);
curl_setopt($curlSession, CURLOPT_USERAGENT, $useragent);
curl_setopt($curlSession, CURLOPT_REFERER, $reff);
curl_setopt($curlSession, CURLOPT_HTTPHEADER, $headerz);
curl_setopt($curlSession, CURLOPT_HEADER, 1);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postinfo = '';
    foreach ($_POST as $key => $value) {
        $postinfo .= $key . '=' . urlencode($value) . '&';
    }
    rtrim($postinfo, '&');
    curl_setopt($curlSession, CURLOPT_POST, 1);
    curl_setopt($curlSession, CURLOPT_POSTFIELDS, $postinfo);
}
curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curlSession, CURLOPT_TIMEOUT, 30);
curl_setopt($curlSession, CURLOPT_FAILONERROR, true);
curl_setopt($curlSession, CURLOPT_COOKIEJAR, $ckfile);
curl_setopt($curlSession, CURLOPT_COOKIEFILE, $ckfile);
foreach ($_COOKIE as $k => $v) {
    if (is_array($v)) {
        $v = serialize($v);
    }
    curl_setopt($curlSession, CURLOPT_COOKIE, "$k=$v; domain=.$cookiedomain ; path=/");
}
$response = curl_exec($curlSession);
if (curl_error($curlSession)) {
    print curl_error($curlSession);
} else {
    $response  = str_replace("HTTP/1.1 100 Continue\r\n\r\n", "", $response);
    $ar        = explode("\r\n\r\n", $response, 2);
    $header    = $ar[0];
    $body      = $ar[1];
    $header_ar = explode(chr(10), $header);
    foreach ($header_ar as $k => $v) {
        if (!preg_match('/^Transfer-Encoding/', $v)) {
            $v = str_replace($base, $mydomain, $v);
            header(trim($v));
        }
    }
    $body = str_replace($base, $mydomain, $body);
    print $body;
}
curl_close($curlSession);