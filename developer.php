<?php
$pluginDir = __DIR__;

$uiLevel = (int)($settings['uiLevel'] ?? 0);
$showLogsTab = $uiLevel >= 1;
$showDevTab = $uiLevel >= 3;
?>
<style>
.tab-bar { display: flex; gap: 0; margin-bottom: 12px; border-bottom: 2px solid var(--bs-border-color, #dee2e6); }
.tab-bar a { display: block; padding: 8px 18px; text-decoration: none; color: var(--bs-body-color, #495057); background: var(--bs-tertiary-bg, #f8f9fa); border: 1px solid var(--bs-border-color, #dee2e6); border-bottom: none; border-radius: 4px 4px 0 0; margin-bottom: -2px; margin-right: 3px; font-size: 14px; }
.tab-bar a.active { background: var(--bs-body-bg, #fff); color: var(--bs-body-color, #212529); border-color: var(--bs-border-color, #dee2e6); border-bottom-color: var(--bs-body-bg, #fff); font-weight: 600; }
.tab-bar a:hover:not(.active) { background: var(--bs-secondary-bg, #e9ecef); }
.btn-danger { background: #dc3545; color: #fff; border: 1px solid #dc3545; padding: 10px 28px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; box-sizing: border-box; white-space: nowrap; display: inline-block; }
.btn-danger:hover { background: #bb2d3b; }
.btn-danger:disabled { opacity: 0.6; cursor: not-allowed; }
</style>

<div class="tab-bar">
    <a href="plugin.php?plugin=fpp-haCommands&page=status.php" class="<?php echo basename(__FILE__) === 'status.php' ? 'active' : ''; ?>">&#9632; Status</a>
    <a href="plugin.php?plugin=fpp-haCommands&page=config.php" class="<?php echo basename(__FILE__) === 'config.php' ? 'active' : ''; ?>">&#9881; Config</a>
    <a href="plugin.php?plugin=fpp-haCommands&page=help.php" class="<?php echo basename(__FILE__) === 'help.php' ? 'active' : ''; ?>">&#63; Help</a>
    <a href="plugin.php?plugin=fpp-haCommands&page=about.php" class="<?php echo basename(__FILE__) === 'about.php' ? 'active' : ''; ?>">&#9432; About</a>
    <?php if ($showLogsTab): ?>
    <a href="plugin.php?plugin=fpp-haCommands&page=logs.php" class="<?php echo basename(__FILE__) === 'logs.php' ? 'active' : ''; ?>">&#9776; Logs</a>
    <?php endif; ?>
    <?php if ($showDevTab): ?>
    <a href="plugin.php?plugin=fpp-haCommands&page=developer.php" class="<?php echo basename(__FILE__) === 'developer.php' ? 'active' : ''; ?>">&#9881; Developer</a>
    <?php endif; ?>
</div>

<div style="margin:0 auto;">
    <fieldset class="border p-3">
        <legend>Developer Tools</legend>
        <div class="p-3">

            <h3 style="color:#dc3545;">Reset Everything</h3>
            <p>
                This will clear the HA URL and Long-Lived Access Token, delete all cached entities
                and generated commands, returning the plugin to a freshly installed state.
                FPPD will be prompted to restart.
            </p>
            <div>
                <button type="button" class="btn-danger" id="reset_btn" onclick="haDev.reset();">&#9888; Reset Everything</button>
            </div>
            <div id="reset_result"></div>

        </div>
    </fieldset>
</div>

<script>
var haDev = {
    reset: function() {
        if (!confirm('This will clear all configuration, cached entities, and generated commands. FPPD will be prompted to restart. Are you sure?')) {
            return;
        }
        if (!confirm('Are you really sure? This action cannot be undone.')) {
            return;
        }

        $('#reset_btn').prop('disabled', true);
        $('#reset_result').html('<span class="text-warning">Resetting plugin...</span>');

        $.ajax({
            url: 'api/plugin/fpp-haCommands/reset',
            type: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            success: function(data) {
                    if (data.success) {
                        $('#reset_result').html('<span class="text-success">' + (data.message || 'Reset complete.') + '</span><br><span class="text-warning">Please restart FPPD when prompted.</span>');
                } else {
                    $('#reset_result').html('<span class="text-danger">' + (data.error || 'Reset failed.') + '</span>');
                    $('#reset_btn').prop('disabled', false);
                }
            },
            error: function(xhr) {
                var msg = 'Could not reach the plugin API.';
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.error) msg = resp.error;
                } catch(e) {}
                $('#reset_result').html('<span class="text-danger">' + msg + '</span>');
                $('#reset_btn').prop('disabled', false);
            }
        });
    }
};
</script>
