<?php
/**
 * #############################################################
 * ## Home Assistant Commands Plugin for FPP (fpp-haCommands) ##
 * ## Author: jessica12ryan                                   ##
 * ## URL: https://github.com/jessica12ryan/fpp-haCommands    ##
 * #############################################################
 * ## config.php                                              ##
 * #############################################################
 */
$pluginDir = __DIR__;
$settingsFile = $pluginDir . '/config/ha_settings.json';
$cacheFile = $pluginDir . '/config/entities_cache.json';
$descriptionsFile = $pluginDir . '/commands/descriptions.json';

$haUrl = '';
$haToken = '';
$settingsSaved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_settings') {
        $haUrl = rtrim($_POST['ha_url'] ?? '', '/');
        $haToken = $_POST['ha_token'] ?? '';
        file_put_contents($settingsFile, json_encode([
            'ha_url' => $haUrl,
            'ha_token' => $haToken
        ], JSON_PRETTY_PRINT));
        $settingsSaved = true;
    }
}

if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true);
    $haUrl = $settings['ha_url'] ?? '';
    $haToken = $settings['ha_token'] ?? '';
}

$entityCount = 0;
$domainCount = 0;
$cmdCount = 0;
if (file_exists($cacheFile)) {
    $cache = json_decode(file_get_contents($cacheFile), true);
    $entityCount = count($cache['entities'] ?? []);
    $domainCount = count($cache['domains'] ?? []);
}
if (file_exists($descriptionsFile)) {
    $cmds = json_decode(file_get_contents($descriptionsFile), true);
    $cmdCount = count($cmds ?? []);
}
?>
<style>
@media only screen and (max-width: 480px) {
    fieldset { padding: 5px !important; }
    table { width: 100%; table-layout: fixed; word-wrap: break-word; }
    td { display: block; width: 100% !important; box-sizing: border-box; }
    input[type="text"], input[type="password"] { width: 100% !important; box-sizing: border-box; }
    input.buttons { width: 100%; margin-bottom: 4px; box-sizing: border-box; }
}
</style>
<script>
var haCommands = {
    settingsFile: '<?php echo $settingsFile; ?>',

    testConnection: function() {
        var url = $('#ha_url').val().replace(/\/+$/, '');
        var token = $('#ha_token').val();

        if (!url || !token) {
            $.jGrowl('Please enter both the HA URL and your access token.', { themeState: 'error' });
            return;
        }

        $('#test_result').html('<span class="text-warning">Testing connection...</span>');

        $.ajax({
            url: 'api/plugin/fpp-haCommands/test',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ ha_url: url, ha_token: token }),
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    $('#test_result').html('<span class="text-success">Connected successfully! HA version: ' + (data.version || 'unknown') + '</span>');
                } else {
                    var msg = (data.error || 'Unknown error');
                    $('#test_result').html('<span class="text-danger">' + msg + '</span>');
                }
            },
            error: function(xhr) {
                var msg = 'Could not reach the plugin API. Check the FPP web server logs.';
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.error) msg = resp.error;
                } catch(e) {}
                $('#test_result').html('<span class="text-danger">' + msg + '</span>');
            }
        });
    },

    updateEntities: function() {
        if (!confirm('This will fetch all entities and actions from Home Assistant and regenerate FPP commands. Continue?')) {
            return;
        }

        $('#update_result').html('<span class="text-warning">Updating entities and regenerating commands...</span>');
        $('#update_btn').prop('disabled', true);

        var url = $('#ha_url').val().replace(/\/+$/, '');
        var token = $('#ha_token').val();

        if (!url || !token) {
            $('#update_result').html('<span class="text-danger">Please enter your HA URL and access token, then try again.</span>');
            $('#update_btn').prop('disabled', false);
            return;
        }

        $.ajax({
            url: 'api/plugin/fpp-haCommands/refresh',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ ha_url: url, ha_token: token }),
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    $('#update_result').html('<span class="text-success">' + data.message + '</span>');
                    if (data.stats) {
                        $('#stats').html(
                            '<b>Entities:</b> ' + data.stats.entities +
                            ' | <b>Domains:</b> ' + data.stats.domains +
                            ' | <b>Commands generated:</b> ' + data.stats.commands
                        );
                    }
                    location.reload();
                } else {
                    var msg = (data.error || 'Unknown error');
                    $('#update_result').html('<span class="text-danger">' + msg + '</span>');
                }
            },
            error: function(xhr) {
                var msg = 'Could not reach the plugin API. Check the FPP web server logs.';
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.error) msg = resp.error;
                } catch(e) {}
                $('#update_result').html('<span class="text-danger">' + msg + '</span>');
            },
            complete: function() {
                $('#update_btn').prop('disabled', false);
            }
        });
    },

    saveSettings: function() {
        var url = $('#ha_url').val().replace(/\/+$/, '');
        var token = $('#ha_token').val();

        if (!url || !token) {
            $.jGrowl('Please enter both HA URL and Token', { themeState: 'error' });
            return;
        }

        $('#save_result').html('<span class="text-warning">Saving...</span>');

        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                action: 'save_settings',
                ha_url: url,
                ha_token: token
            },
            success: function() {
                $('#save_result').html('<span class="text-success">Settings saved!</span>');
                $.jGrowl('HA settings saved successfully.', { themeState: 'success' });
            },
            error: function() {
                $('#save_result').html('<span class="text-danger">Failed to save settings.</span>');
            }
        });
    }
};

$(document).ready(function() {
    $('#ha_url').on('change', function() {
        $('#save_result').html('');
        $('#test_result').html('');
    });
    $('#ha_token').on('change', function() {
        $('#save_result').html('');
        $('#test_result').html('');
    });
});
</script>

<div style="margin:0 auto;">
    <fieldset class="border p-3">
        <legend>Home Assistant Connection</legend>
        <div class="p-3">
            <table>
                <tr>
                    <td style="padding: 4px;"><b>HA URL:</b></td>
                    <td style="padding: 4px;">
                        <input type="text" id="ha_url" size="50"
                               placeholder="http://homeassistant.local:8123"
                               value="<?php echo htmlspecialchars($haUrl); ?>">
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px;"><b>Long-Lived Access Token:</b></td>
                    <td style="padding: 4px;">
                        <input type="password" id="ha_token" size="50"
                               placeholder="Paste your HA Long-Lived Access Token"
                               value="<?php echo htmlspecialchars($haToken); ?>">
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px;"></td>
                    <td style="padding: 4px;">
                        <input type="button" class="buttons" value="Save Settings"
                               onclick="haCommands.saveSettings();">
                        <input type="button" class="buttons" value="Test Connection"
                               onclick="haCommands.testConnection();">
                        <input type="button" class="buttons" id="update_btn"
                               value="Update Entities"
                               onclick="haCommands.updateEntities();">
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <div id="save_result" style="margin-top:4px;"></div>
                        <div id="test_result" style="margin-top:4px;"></div>
                        <div id="update_result" style="margin-top:4px;"></div>
                    </td>
                </tr>
            </table>
        </div>
    </fieldset>

    <br />

    <fieldset class="border p-3">
        <legend>Current Status</legend>
        <div class="p-3">
            <table>
                <tr>
                    <td style="padding: 4px;"><b>HA URL:</b></td>
                    <td style="padding: 4px;"><?php echo $haUrl ? htmlspecialchars($haUrl) : '<span class="text-secondary">Not configured</span>'; ?></td>
                </tr>
                <tr>
                    <td style="padding: 4px;"><b>Token configured:</b></td>
                    <td style="padding: 4px;"><?php echo $haToken ? '<span class="text-success">Yes</span>' : '<span class="text-danger">No</span>'; ?></td>
                </tr>
                <tr>
                    <td style="padding: 4px;"><b>Cached entities:</b></td>
                    <td style="padding: 4px;"><?php echo $entityCount; ?></td>
                </tr>
                <tr>
                    <td style="padding: 4px;"><b>Domains discovered:</b></td>
                    <td style="padding: 4px;"><?php echo $domainCount; ?></td>
                </tr>
                <tr>
                    <td style="padding: 4px;"><b>Commands generated:</b></td>
                    <td style="padding: 4px;" id="stats"><?php echo $cmdCount; ?></td>
                </tr>
            </table>
        </div>
    </fieldset>

    <br />

    <fieldset class="border p-3">
        <legend>Important Notes</legend>
        <div class="p-3">
            <ul>
                <li>Generate a <b>Long-Lived Access Token</b> in Home Assistant at your Profile page (<i>http://HA:8123/profile</i> -> Long-Lived Access Tokens)</li>
                <li>After clicking <b>Update Entities</b>, the FPP daemon will restart to load the new commands. This is a fast restart. You may need to refresh the page for new commands to appear in the FPP command list.</li>
                <li>HA URL should include the protocol and port, e.g., <code>http://192.168.1.100:8123</code></li>
                <li>Commands appear in the playlist editor as <b>HA - domain._name</b> with a dropdown of matching entities.</li>
            </ul>
        </div>
    </fieldset>
</div>
<?php
