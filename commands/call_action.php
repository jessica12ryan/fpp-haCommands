#!/usr/bin/php

#############################################################
## Home Assistant Commands Plugin for FPP (fpp-haCommands) ##
## Author: jessica12ryan                                   ##
## URL: https://github.com/jessica12ryan/fpp-haCommands    ##
#############################################################
## call_action.php                                         ##
#############################################################

<?php
$pluginDir = dirname(__DIR__);
$settingsFile = $pluginDir . '/config/ha_settings.json';
$logDir = getenv('LOGDIR') ?: '/home/fpp/media/logs';
$logFile = $logDir . '/plugin-fpp-haCommands.log';

function hacLog($msg) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . ' fpp-haCommands call_action: ' . $msg . "\n", FILE_APPEND | LOCK_EX);
}

if (!file_exists($settingsFile)) {
    $msg = "Settings file not found at: $settingsFile\n";
    hacLog($msg);
    fwrite(STDERR, "HA Commands: " . $msg);
    exit(1);
}

$settings = json_decode(file_get_contents($settingsFile), true);
$haUrl = rtrim($settings['ha_url'] ?? '', '/');
$haToken = $settings['ha_token'] ?? '';

if (empty($haUrl) || empty($haToken)) {
    $msg = "HA URL or Token not configured.\n";
    hacLog($msg);
    fwrite(STDERR, "HA Commands: " . $msg);
    exit(1);
}

$domain = $argv[1] ?? '';
$service = $argv[2] ?? '';
$entityId = $argv[3] ?? '';

if (empty($domain) || empty($service)) {
    $msg = "Missing required arguments (domain, service).\n";
    hacLog($msg);
    fwrite(STDERR, "HA Commands: " . $msg);
    exit(1);
}

$payload = [];
if (!empty($entityId)) {
    $payload['entity_id'] = $entityId;
}

$fieldOrderings = [
    'light' => ['brightness_pct', 'rgb_color', 'color_temp', 'service_data'],
    'cover' => ['position_pct', 'service_data'],
    'climate' => ['temperature', 'hvac_mode', 'target_temp_low', 'target_temp_high', 'service_data'],
    'fan' => ['speed', 'direction', 'service_data'],
    'media_player' => ['volume_level', 'source', 'service_data'],
    'vacuum' => ['command', 'service_data'],
    'water_heater' => ['temperature', 'service_data'],
    'humidifier' => ['humidity', 'service_data'],
    'time' => ['time', 'service_data'],
    'timer' => ['duration', 'service_data'],
    'scene' => ['transition', 'service_data'],
    'alarm_control_panel' => ['code', 'service_data'],
    'lock' => ['code', 'service_data'],
    'number' => ['value', 'service_data'],
    'input_number' => ['value', 'service_data'],
    'select' => ['option', 'service_data'],
    'input_select' => ['option', 'service_data'],
    'siren' => ['tone', 'volume_level', 'duration', 'service_data'],
    'notify' => ['message', 'title', 'service_data'],
    'tts' => ['message', 'language', 'service_data'],
    'remote' => ['command', 'device', 'num_repeats', 'service_data'],
];

$fieldNames = $fieldOrderings[$domain] ?? ['service_data'];
$extraValues = array_slice($argv, 4);
$fields = [];
foreach ($fieldNames as $i => $name) {
    $fields[$name] = $extraValues[$i] ?? '';
}

$serviceData = $fields['service_data'] ?? '';

if (!empty($fields['brightness_pct'] ?? '') && $fields['brightness_pct'] !== '0' && $fields['brightness_pct'] !== '153' && is_numeric($fields['brightness_pct'] ?? '')) {
    $payload['brightness_pct'] = (int)$fields['brightness_pct'];
}

if (!empty($fields['rgb_color'] ?? '')) {
    $parts = explode(',', $fields['rgb_color']);
    $payload['rgb_color'] = array_map('intval', $parts);
}

if (!empty($fields['color_temp'] ?? '') && $fields['color_temp'] !== '0' && $fields['color_temp'] !== '153' && is_numeric($fields['color_temp'] ?? '')) {
    $payload['color_temp'] = (int)$fields['color_temp'];
}

if (!empty($fields['position_pct'] ?? '') && is_numeric($fields['position_pct'] ?? '')) {
    $payload['position'] = (int)$fields['position_pct'];
}

if (!empty($fields['temperature'] ?? '') && is_numeric($fields['temperature'] ?? '')) {
    $payload['temperature'] = (float)$fields['temperature'];
}

if (!empty($fields['hvac_mode'] ?? '')) {
    $payload['hvac_mode'] = $fields['hvac_mode'];
}

if (!empty($fields['target_temp_low'] ?? '') && is_numeric($fields['target_temp_low'] ?? '')) {
    $payload['target_temp_low'] = (float)$fields['target_temp_low'];
}

if (!empty($fields['target_temp_high'] ?? '') && is_numeric($fields['target_temp_high'] ?? '')) {
    $payload['target_temp_high'] = (float)$fields['target_temp_high'];
}

if (!empty($fields['speed'] ?? '')) {
    $payload['speed'] = $fields['speed'];
}

if (!empty($fields['direction'] ?? '')) {
    $payload['direction'] = $fields['direction'];
}

if (!empty($fields['volume_level'] ?? '') && is_numeric($fields['volume_level'] ?? '')) {
    $payload['volume_level'] = (float)$fields['volume_level'];
}

if (!empty($fields['source'] ?? '')) {
    $payload['source'] = $fields['source'];
}

if (!empty($fields['humidity'] ?? '') && is_numeric($fields['humidity'] ?? '')) {
    $payload['humidity'] = (int)$fields['humidity'];
}

if (!empty($fields['transition'] ?? '') && is_numeric($fields['transition'] ?? '')) {
    $payload['transition'] = (float)$fields['transition'];
}

if (!empty($fields['code'] ?? '')) {
    $payload['code'] = $fields['code'];
}

if (!empty($fields['value'] ?? '')) {
    if (is_numeric($fields['value'])) {
        $payload['value'] = (float)$fields['value'];
    } else {
        $payload['value'] = $fields['value'];
    }
}

if (!empty($fields['time'] ?? '')) {
    $payload['time'] = $fields['time'];
}

if (!empty($fields['option'] ?? '')) {
    $payload['option'] = $fields['option'];
}

if (!empty($fields['tone'] ?? '')) {
    $payload['tone'] = $fields['tone'];
}

if (!empty($fields['duration'] ?? '')) {
    if (is_numeric($fields['duration'])) {
        $payload['duration'] = (int)$fields['duration'];
    } else {
        $payload['duration'] = $fields['duration'];
    }
}

if (!empty($fields['message'] ?? '')) {
    $payload['message'] = $fields['message'];
}

if (!empty($fields['title'] ?? '')) {
    $payload['title'] = $fields['title'];
}

if (!empty($fields['language'] ?? '')) {
    $payload['language'] = $fields['language'];
}

if (!empty($fields['device'] ?? '')) {
    $payload['device'] = $fields['device'];
}

if (!empty($fields['num_repeats'] ?? '') && is_numeric($fields['num_repeats'] ?? '')) {
    $payload['num_repeats'] = (int)$fields['num_repeats'];
}

if (!empty($serviceData)) {
    if (strpos($serviceData, '{') === 0 || strpos($serviceData, '[') === 0) {
        $extra = json_decode($serviceData, true);
        if (is_array($extra)) {
            $payload = array_merge($payload, $extra);
        }
    } elseif (strpos($serviceData, ':') !== false) {
        $parts = explode(':', $serviceData, 2);
        $payload[trim($parts[0])] = trim($parts[1]);
    }
}

$url = $haUrl . '/api/services/' . urlencode($domain) . '/' . urlencode($service);
$postData = json_encode($payload);

hacLog("POST $domain.$service entity=$entityId");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $haToken,
    'Content-Type: application/json'
]);

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

if ($httpCode >= 200 && $httpCode < 300) {
    $decoded = json_decode($response, true);
    $stateChanges = is_array($decoded) ? count($decoded) : 0;
    $entityLabel = !empty($entityId) ? " on " . $entityId : "";
    hacLog("SUCCESS: $domain.$service called$entityLabel ($stateChanges state changes)");
    echo "HA Commands: " . $domain . "." . $service . " called" . $entityLabel . " (HTTP " . $httpCode . ", " . $stateChanges . " state changes)\n";
    exit(0);
} else {
    $msg = "HTTP " . $httpCode . " - " . substr($response, 0, 500) . "\n";
    hacLog($msg);
    fwrite(STDERR, "HA Commands: " . $msg);
    exit(1);
}