<?php
header('Content-Type: application/json');

$domain = isset($_GET['domain']) ? $_GET['domain'] : '';
$timeout = 1;

$pingResult = pingDomain($domain, $timeout);
$whoisResult = getWhoisInfo($domain);

$response = [
    'online' => $pingResult['online'],
    'whois' => $whoisResult,
];

echo json_encode($response);

function pingDomain($domain, $timeout) {
    $file = @fsockopen($domain, 80, $errno, $errstr, $timeout);

    // Check if the connection is successful
    $online = is_resource($file);

    if ($online) {
        fclose($file);
    }

    return ['online' => $online];
}

function getWhoisInfo($domain) {
    $apiKey = 'at_gg6IpQE8A2zOHWPyaXa2BCzHhOHIW'; // Ganti dengan kunci API Anda
    $whoisUrl = "https://www.whoisxmlapi.com/whoisserver/WhoisService?apiKey={$apiKey}&domainName={$domain}&outputFormat=json";

    $response = file_get_contents($whoisUrl);

    if ($response === false) {
        return ['error' => 'Gagal mengambil informasi WHOIS.'];
    } else {
        return json_decode($response, true);
    }
}
?>
