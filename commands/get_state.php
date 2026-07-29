#!/usr/bin/php

#############################################################
## Home Assistant Commands Plugin for FPP (fpp-haCommands) ##
## Author: jessica12ryan                                   ##
## URL: https://github.com/jessica12ryan/fpp-haCommands    ##
#############################################################
## get_state.php                                           ##
#############################################################

<?php
$pluginDir = dirname(__DIR__);
$iniFile = $pluginDir . '/config/plugin.fpp-haCommands';
$oldJsonFile = $pluginDir . '/config/ha_settings.json';
$logDir = getenv('LOGDIR') ?: '/home/fpp/media/logs';
$logFile = $logDir . '/plugin-fpp-haCommands.log';

function hacLog($msg) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . ' fpp-haCommands get_state: ' . $msg . "\n", FILE_APPEND | LOCK_EX);
}

$haUrl = '';
$haToken = '';
if (file_exists($iniFile)) {
    $s = parse_ini_file($iniFile);
    $haUrl = rtrim($s['ha_url'] ?? '', '/');
    $haToken = $s['ha_token'] ?? '';
} elseif (file_exists($oldJsonFile)) {
    $s = json_decode(file_get_contents($oldJsonFile), true);
    $haUrl = rtrim($s['ha_url'] ?? '', '/');
    $haToken = $s['ha_token'] ?? '';
}

if (empty($haUrl) || empty($haToken)) {
    $msg = "HA URL or Token not configured.\n";
    hacLog($msg);
    fwrite(STDERR, "HA Commands: " . $msg);
    exit(1);
}

$entityId = $argv[1] ?? '';
if (empty($entityId)) {
    $msg = "Missing entity_id argument.\n";
    hacLog($msg);
    fwrite(STDERR, "HA Commands: " . $msg);
    exit(1);
}

hacLog("GET state entity=$entityId");

$url = $haUrl . '/api/states/' . urlencode($entityId);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $haToken]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    $msg = "Curl error - " . $error . "\n";
    hacLog($msg);
    fwrite(STDERR, "HA Commands: " . $msg);
    exit(1);
}

if ($httpCode === 200) {
    $state = json_decode($response, true);
    $stateVal = $state['state'] ?? 'unknown';
    hacLog("SUCCESS: Got state for $entityId: $stateVal");
    echo "State: " . $stateVal . "\n";
    if (!empty($state['attributes'])) {
        foreach ($state['attributes'] as $key => $value) {
            if (is_scalar($value)) {
                echo $key . ": " . $value . "\n";
            }
        }
    }
    exit(0);
} elseif ($httpCode === 404) {
    $msg = "Entity '$entityId' not found in Home Assistant.\n";
    hacLog($msg);
    fwrite(STDERR, "HA Commands: " . $msg);
    exit(1);
} else {
    $msg = "HTTP " . $httpCode . " - " . substr($response, 0, 500) . "\n";
    hacLog($msg);
    fwrite(STDERR, "HA Commands: " . $msg);
    exit(1);
}
