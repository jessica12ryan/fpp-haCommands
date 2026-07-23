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
    fwrite(STDERR, "HA Commands: Settings not configured. Configure HA connection in Content Setup -> HA Commands.\n");
    exit(1);
}

if (empty($haUrl) || empty($haToken)) {
    fwrite(STDERR, "HA Commands: HA URL or Token not configured.\n");
    exit(1);
}

$entityId = $argv[1] ?? '';
if (empty($entityId)) {
    fwrite(STDERR, "HA Commands: Missing entity_id argument.\n");
    exit(1);
}

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
    fwrite(STDERR, "HA Commands: Curl error - " . $error . "\n");
    exit(1);
}

if ($httpCode === 200) {
    $state = json_decode($response, true);
    echo "State: " . ($state['state'] ?? 'unknown') . "\n";
    if (!empty($state['attributes'])) {
        foreach ($state['attributes'] as $key => $value) {
            if (is_scalar($value)) {
                echo $key . ": " . $value . "\n";
            }
        }
    }
    exit(0);
} elseif ($httpCode === 404) {
    fwrite(STDERR, "HA Commands: Entity '" . $entityId . "' not found in Home Assistant.\n");
    exit(1);
} else {
    fwrite(STDERR, "HA Commands: HTTP " . $httpCode . " - " . substr($response, 0, 500) . "\n");
    exit(1);
}
