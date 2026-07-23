<?php
/**
 * #############################################################
 * ## Home Assistant Commands Plugin for FPP (fpp-haCommands) ##
 * ## Author: jessica12ryan                                   ##
 * ## URL: https://github.com/jessica12ryan/fpp-haCommands    ##
 * #############################################################
 * ## api.php                                                 ##
 * #############################################################
 */

define('HAC_PLUGIN_DIR', __DIR__);
define('HAC_SETTINGS_INI', HAC_PLUGIN_DIR . '/config/plugin.fpp-haCommands');
define('HAC_SETTINGS_JSON', HAC_PLUGIN_DIR . '/config/ha_settings.json');
define('HAC_CACHE_FILE', HAC_PLUGIN_DIR . '/config/entities_cache.json');
define('HAC_DESCRIPTIONS_FILE', HAC_PLUGIN_DIR . '/commands/descriptions.json');

function hacGetSettings() {
    if (file_exists(HAC_SETTINGS_INI)) {
        $s = parse_ini_file(HAC_SETTINGS_INI);
        return ['ha_url' => $s['ha_url'] ?? '', 'ha_token' => $s['ha_token'] ?? ''];
    }
    if (file_exists(HAC_SETTINGS_JSON)) {
        return json_decode(file_get_contents(HAC_SETTINGS_JSON), true) ?: ['ha_url' => '', 'ha_token' => ''];
    }
    return ['ha_url' => '', 'ha_token' => ''];
}

function hacCallHA($method, $endpoint, $data = null) {
    $settings = hacGetSettings();
    if (empty($settings['ha_url']) || empty($settings['ha_token'])) {
        return ['success' => false, 'error' => 'HA not configured'];
    }

    $url = rtrim($settings['ha_url'], '/') . $endpoint;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $settings['ha_token'],
        'Content-Type: application/json'
    ]);

    if ($method === 'POST' && $data !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['success' => false, 'error' => 'Curl error: ' . $error];
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        return ['success' => false, 'error' => 'HTTP ' . $httpCode . ': ' . substr($response, 0, 500)];
    }

    $decoded = json_decode($response, true);
    if ($decoded === null && $response) {
        return ['success' => false, 'error' => 'Invalid JSON response from HA'];
    }

    return ['success' => true, 'data' => $decoded];
}

function hacGetEntitiesFromCache($domain = null) {
    if (!file_exists(HAC_CACHE_FILE)) {
        return [];
    }
    $cache = json_decode(file_get_contents(HAC_CACHE_FILE), true);
    $entitiesByDomain = $cache['entities_by_domain'] ?? [];

    if ($domain === null || $domain === 'all') {
        $result = [];
        foreach ($entitiesByDomain as $d => $entities) {
            foreach ($entities as $eid) {
                $result[] = $eid;
            }
        }
        sort($result);
        return $result;
    }

    $entities = $entitiesByDomain[$domain] ?? [];
    sort($entities);
    return $entities;
}

function hacGetCachedDomains() {
    if (!file_exists(HAC_CACHE_FILE)) {
        return [];
    }
    $cache = json_decode(file_get_contents(HAC_CACHE_FILE), true);
    $domains = array_keys($cache['entities_by_domain'] ?? []);
    sort($domains);
    return $domains;
}

function hacGetServiceFieldsForDomain($domain, $service = null) {
    $commonFields = [
        'light' => [
            'brightness_pct' => ['description' => 'Brightness %', 'type' => 'int', 'optional' => true],
            'rgb_color' => ['description' => 'RGB Color (r,g,b)', 'type' => 'string', 'optional' => true],
            'color_temp' => ['description' => 'Color Temp (mireds)', 'type' => 'int', 'optional' => true],
        ],
        'cover' => [
            'position_pct' => ['description' => 'Position %', 'type' => 'int', 'optional' => true, 'min' => 0, 'max' => 100],
        ],
        'climate' => [
            'temperature' => ['description' => 'Temperature', 'type' => 'float', 'optional' => true],
            'hvac_mode' => ['description' => 'HVAC Mode', 'type' => 'string', 'optional' => true],
            'target_temp_low' => ['description' => 'Target Temp Low', 'type' => 'float', 'optional' => true],
            'target_temp_high' => ['description' => 'Target Temp High', 'type' => 'float', 'optional' => true],
        ],
        'fan' => [
            'speed' => ['description' => 'Speed', 'type' => 'string', 'optional' => true],
            'direction' => ['description' => 'Direction', 'type' => 'string', 'optional' => true],
        ],
        'media_player' => [
            'volume_level' => ['description' => 'Volume (0.0-1.0)', 'type' => 'float', 'optional' => true, 'min' => 0, 'max' => 1],
            'source' => ['description' => 'Source', 'type' => 'string', 'optional' => true],
        ],
        'vacuum' => [
            'command' => ['description' => 'Command', 'type' => 'string', 'optional' => true],
        ],
        'water_heater' => [
            'temperature' => ['description' => 'Temperature', 'type' => 'float', 'optional' => true],
        ],
        'humidifier' => [
            'humidity' => ['description' => 'Humidity', 'type' => 'int', 'optional' => true, 'min' => 0, 'max' => 100],
        ],
        'scene' => [
            'transition' => ['description' => 'Transition (seconds)', 'type' => 'float', 'optional' => true],
        ],
        'alarm_control_panel' => [
            'code' => ['description' => 'Code', 'type' => 'string', 'optional' => true],
        ],
        'lock' => [
            'code' => ['description' => 'Code', 'type' => 'string', 'optional' => true],
        ],
        'number' => [
            'value' => ['description' => 'Value', 'type' => 'float', 'optional' => true],
        ],
        'input_number' => [
            'value' => ['description' => 'Value', 'type' => 'float', 'optional' => true],
        ],
        'select' => [
            'option' => ['description' => 'Option', 'type' => 'string', 'optional' => true],
        ],
        'input_select' => [
            'option' => ['description' => 'Option', 'type' => 'string', 'optional' => true],
        ],
        'time' => [
            'time' => ['description' => 'Time (HH:MM:SS)', 'type' => 'string', 'optional' => true],
        ],
        'timer' => [
            'duration' => ['description' => 'Duration (HH:MM:SS or seconds)', 'type' => 'string', 'optional' => true],
        ],
        'siren' => [
            'tone' => ['description' => 'Tone', 'type' => 'string', 'optional' => true],
            'volume_level' => ['description' => 'Volume (0.0-1.0)', 'type' => 'float', 'optional' => true, 'min' => 0, 'max' => 1],
            'duration' => ['description' => 'Duration (seconds)', 'type' => 'int', 'optional' => true],
        ],
        'notify' => [
            'message' => ['description' => 'Message', 'type' => 'string', 'optional' => true],
            'title' => ['description' => 'Title', 'type' => 'string', 'optional' => true],
        ],
        'tts' => [
            'message' => ['description' => 'Message', 'type' => 'string', 'optional' => true],
            'language' => ['description' => 'Language', 'type' => 'string', 'optional' => true],
        ],
        'remote' => [
            'command' => ['description' => 'Command', 'type' => 'string', 'optional' => true],
            'device' => ['description' => 'Device', 'type' => 'string', 'optional' => true],
            'num_repeats' => ['description' => 'Repeat Count', 'type' => 'int', 'optional' => true],
        ],
    ];

    $noExtraFields = [
        'scene.turn_off',
        'scene.toggle',
        'scene.reload',
        'scene.create',
        'scene.delete',
        'alarm_control_panel.alarm_trigger',
        'number.increment',
        'number.decrement',
        'input_number.increment',
        'input_number.decrement',
        'select.first',
        'select.last',
        'select.previous',
        'select.next',
        'input_select.select_first',
        'input_select.select_last',
        'input_select.next',
        'input_select.previous',
        'input_select.set_options',
        'input_select.add_options',
        'input_select.remove_option',
        'siren.turn_off',
        'siren.toggle',
        'remote.learn_command',
        'remote.delete_command',
        'timer.pause',
        'timer.cancel',
        'timer.finish',
    ];

    if ($service !== null && in_array($domain . '.' . $service, $noExtraFields, true)) {
        return [];
    }

    return $commonFields[$domain] ?? [];
}

function hacHasEntityId($serviceFields) {
    return isset($serviceFields['entity_id']);
}

function hacHasTargetEntity($serviceInfo) {
    $entity = $serviceInfo['target']['entity'] ?? null;
    if ($entity === null) return false;
    if (is_array($entity) && isset($entity['domain'])) return true;
    if (is_array($entity) && isset($entity[0]['domain'])) return true;
    return false;
}

function hacGetEntityDomain($serviceFields, $domain) {
    if (isset($serviceFields['entity_id']['selector']['entity']['domain'])) {
        $d = $serviceFields['entity_id']['selector']['entity']['domain'];
        if (is_array($d)) {
            return $d;
        }
        return (string)$d;
    }
    return $domain;
}

function hacGetTargetDomains($serviceInfo) {
    $entity = $serviceInfo['target']['entity'] ?? null;
    if ($entity === null) return null;

    if (is_array($entity) && isset($entity['domain'])) {
        $d = $entity['domain'];
        return is_array($d) ? $d : [$d];
    }

    if (is_array($entity) && isset($entity[0]['domain'])) {
        $d = $entity[0]['domain'];
        return is_array($d) ? $d : [$d];
    }

    return null;
}

function hacBuildEntityList($entityDomain, $entitiesByDomain) {
    if (is_array($entityDomain)) {
        $entityList = [];
        foreach ($entityDomain as $ed) {
            $ed = (string)$ed;
            $domainEntities = $entitiesByDomain[$ed] ?? [];
            $entityList = array_merge($entityList, $domainEntities);
        }
        sort($entityList);
        return $entityList;
    }
    $entities = $entitiesByDomain[$entityDomain] ?? [];
    sort($entities);
    return $entities;
}

function hacGenerateDescriptions($services, $entitiesByDomain = []) {
    $commands = [];

    foreach ($services as $serviceDef) {
        $domain = $serviceDef['domain'] ?? '';
        foreach ($serviceDef['services'] as $serviceName => $serviceInfo) {
            $fields = $serviceInfo['fields'] ?? [];

            $args = [];
            $args[] = ['name' => 'domain', 'description' => 'Domain', 'default' => $domain, 'type' => 'string', 'contents' => [$domain]];
            $args[] = ['name' => 'service', 'description' => 'Service', 'default' => $serviceName, 'type' => 'string', 'contents' => [$serviceName]];

            $hasEntity = false;
            $entityList = [];

            if (hacHasEntityId($fields)) {
                $entityDomain = hacGetEntityDomain($fields, $domain);
                $entityList = hacBuildEntityList($entityDomain, $entitiesByDomain);
                $hasEntity = true;
            } elseif (hacHasTargetEntity($serviceInfo)) {
                $targetDomains = hacGetTargetDomains($serviceInfo);
                if ($targetDomains !== null) {
                    $entityList = hacBuildEntityList($targetDomains, $entitiesByDomain);
                    $hasEntity = true;
                }
            }

            if ($hasEntity) {
                $args[] = [
                    'name' => 'entity_id',
                    'description' => 'Entity',
                    'type' => 'string',
                    'contents' => $entityList
                ];
            }

            $extraFields = hacGetServiceFieldsForDomain($domain, $serviceName);
            foreach ($extraFields as $fieldName => $fieldDef) {
                $args[] = array_merge(['name' => $fieldName], $fieldDef);
            }

            $args[] = [
                'name' => 'service_data',
                'description' => 'Extra JSON',
                'type' => 'string',
                'optional' => true
            ];

            $commands[] = [
                'name' => 'HA - ' . $domain . '.' . $serviceName,
                'script' => 'call_action.php',
                'args' => $args
            ];
        }
    }

    return $commands;
}

function hacRefreshAll() {
    $track = [];

    set_time_limit(120);

    $track[] = 'set_time_limit';

    $settings = hacGetSettings();
    if (empty($settings['ha_url']) || empty($settings['ha_token'])) {
        return ['success' => false, 'error' => 'Home Assistant is not configured yet. Enter your HA URL and access token above, then try again.', 'track' => $track];
    }
    $track[] = 'got_settings';

    $statesResult = hacCallHA('GET', '/api/states');
    if (!$statesResult['success']) {
        $statesResult['track'] = $track;
        return $statesResult;
    }
    $states = $statesResult['data'];
    if (!is_array($states)) {
        return ['success' => false, 'error' => 'Received an unexpected response from Home Assistant when fetching entities. Try again.', 'track' => $track];
    }
    $track[] = 'got_states:' . count($states);

    $servicesResult = hacCallHA('GET', '/api/services');
    if (!$servicesResult['success']) {
        $servicesResult['track'] = $track;
        return $servicesResult;
    }
    $services = $servicesResult['data'];
    if (!is_array($services)) {
        return ['success' => false, 'error' => 'Received an unexpected response from Home Assistant when fetching actions. Try again.', 'track' => $track];
    }
    $track[] = 'got_services:' . count($services);

    $entitiesByDomain = [];
    foreach ($states as $state) {
        $eid = is_array($state) ? ($state['entity_id'] ?? '') : '';
        if (!$eid) continue;
        $parts = explode('.', $eid, 2);
        $domain = $parts[0];
        $entitiesByDomain[$domain][] = $eid;
    }
    $track[] = 'built_domains:' . count($entitiesByDomain);

    $cache = [
        'updated_at' => date('Y-m-d H:i:s'),
        'entities' => $states,
        'entities_by_domain' => $entitiesByDomain,
        'domains' => array_keys($entitiesByDomain)
    ];
    $result = @file_put_contents(HAC_CACHE_FILE, json_encode($cache, JSON_PRETTY_PRINT | JSON_INVALID_UTF8_IGNORE));
    if ($result === false) {
        return ['success' => false, 'error' => 'Could not write entity cache to ' . HAC_CACHE_FILE . '. Check file permissions.', 'track' => $track];
    }
    $track[] = 'wrote_cache';

    $commands = hacGenerateDescriptions($services, $entitiesByDomain);
    $track[] = 'generated_commands:' . count($commands);

    $json = json_encode($commands, JSON_PRETTY_PRINT | JSON_INVALID_UTF8_IGNORE);
    if ($json === false) {
        return ['success' => false, 'error' => 'Failed to process command data — try running Update Entities again.', 'track' => $track];
    }
    $track[] = 'encoded_commands:' . strlen($json);

    $result = @file_put_contents(HAC_DESCRIPTIONS_FILE, $json);
    if ($result === false) {
        return ['success' => false, 'error' => 'Could not write command descriptions to ' . HAC_DESCRIPTIONS_FILE . '. Check file permissions.', 'track' => $track];
    }
    $track[] = 'wrote_descriptions';

    $totalEntities = count($states);
    $domainCount = count($entitiesByDomain);
    $cmdCount = count($commands);

    $opts = ['http' => ['method' => 'PUT', 'header' => 'Content-Type: application/json', 'content' => '1']];
    @file_get_contents('http://localhost/api/settings/restartFlag', false, stream_context_create($opts));
    $track[] = 'restartFlag_set';

    return [
        'success' => true,
        'message' => "Updated! {$totalEntities} entities across {$domainCount} domains, {$cmdCount} commands generated.",
        'stats' => [
            'entities' => $totalEntities,
            'domains' => $domainCount,
            'commands' => $cmdCount
        ],
        'track' => $track
    ];
}

function getEndpointsfpphaCommands() {
    $result = [];

    $result[] = ['method' => 'GET', 'endpoint' => 'entities/:domain', 'callback' => 'hacEntitiesEndpoint'];
    $result[] = ['method' => 'GET', 'endpoint' => 'domains', 'callback' => 'hacDomainsEndpoint'];
    $result[] = ['method' => 'POST', 'endpoint' => 'test', 'callback' => 'hacTestEndpoint'];
    $result[] = ['method' => 'POST', 'endpoint' => 'refresh', 'callback' => 'hacRefreshEndpoint'];
    $result[] = ['method' => 'GET', 'endpoint' => 'icon', 'callback' => 'hacIconEndpoint'];

    return $result;
}

function hacIconEndpoint() {
    $iconFile = HAC_PLUGIN_DIR . '/icon.png';
    if (!file_exists($iconFile)) {
        header('HTTP/1.0 404 Not Found');
        return json(['error' => 'Icon not found']);
    }
    $mtime = filemtime($iconFile);
    $etag = '"' . md5_file($iconFile) . '"';
    header('Content-Type: image/png');
    header('Content-Length: ' . filesize($iconFile));
    header('Cache-Control: no-cache, must-revalidate');
    header('ETag: ' . $etag);
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');

    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
        header('HTTP/1.1 304 Not Modified');
        exit;
    }
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $ifModifiedSince = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        if ($ifModifiedSince >= $mtime) {
            header('HTTP/1.1 304 Not Modified');
            exit;
        }
    }

    readfile($iconFile);
    exit;
}

function hacEntitiesEndpoint() {
    $domain = param('domain', 'all');
    $entities = hacGetEntitiesFromCache($domain);
    return json($entities);
}

function hacDomainsEndpoint() {
    $domains = hacGetCachedDomains();
    return json($domains);
}

function hacTestEndpoint() {
    $body = $_POST;
    $url = rtrim($body['ha_url'] ?? '', '/');
    $token = $body['ha_token'] ?? '';

    if (empty($url) || empty($token)) {
        return json(['success' => false, 'error' => 'Please enter both the HA URL and your access token.']);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . '/api/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        $msg = 'Could not reach Home Assistant. ';
        if (str_contains($error, 'Connection refused')) {
            $msg .= 'Connection refused — is HA running and is the URL correct?';
        } elseif (str_contains($error, 'Could not resolve host')) {
            $msg .= 'Could not resolve hostname — check the URL.';
        } elseif (str_contains($error, 'Operation timed out')) {
            $msg .= 'Connection timed out — check that HA is reachable from this device.';
        } else {
            $msg .= 'Error: ' . $error;
        }
        return json(['success' => false, 'error' => $msg]);
    }
    if ($httpCode === 401) {
        return json(['success' => false, 'error' => 'Unauthorized — check that your access token is correct.']);
    }
    if ($httpCode === 404) {
        return json(['success' => false, 'error' => 'URL not found — make sure the HA URL ends with the correct port (e.g. :8123).']);
    }
    if ($httpCode !== 200) {
        $summary = substr($response, 0, 200);
        return json(['success' => false, 'error' => "Unexpected response (HTTP {$httpCode}) from HA. {$summary}"]);
    }

    $decoded = json_decode($response, true);
    $version = $decoded['message'] ?? '';

    return json([
        'success' => true,
        'version' => $version,
        'message' => 'Connected to Home Assistant'
    ]);
}

function hacFriendlyError($msg) {
    if (str_contains($msg, 'file_put_contents') && str_contains($msg, 'Permission denied')) {
        return 'The web server cannot write to the plugin config directory. Run a plugin update from the Plugin Manager to fix permissions.';
    }
    if (str_contains($msg, 'Undefined array key') || str_contains($msg, 'Undefined index')) {
        return 'Unexpected data format from Home Assistant — try running Update Entities again.';
    }
    return $msg;
}

function hacRefreshEndpoint() {
    set_error_handler(function ($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    try {
        $body = $_POST;

        if (!empty($body['ha_url']) && !empty($body['ha_token'])) {
            $ini = "ha_url=" . rtrim($body['ha_url'], '/') . "\nha_token=" . $body['ha_token'] . "\n";
            @file_put_contents(HAC_SETTINGS_INI, $ini);
            @unlink(HAC_SETTINGS_JSON);
        }

        $result = hacRefreshAll();
        return json($result);
    } catch (Throwable $e) {
        return json([
            'success' => false,
            'error' => hacFriendlyError($e->getMessage())
        ]);
    } finally {
        restore_error_handler();
    }
}
?>
