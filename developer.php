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
.btn-warning { background: #e67e22; color: #fff; border: 1px solid #e67e22; padding: 10px 28px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; box-sizing: border-box; white-space: nowrap; display: inline-block; }
.btn-warning:hover { background: #d35400; }
.btn-warning:disabled { opacity: 0.6; cursor: not-allowed; }
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center; }
.modal-box { background: #fff; color: #333; border-radius: 8px; padding: 24px 32px; max-width: 420px; width: 90%; box-shadow: 0 4px 20px rgba(0,0,0,0.3); text-align: center; }
.modal-message { font-size: 15px; line-height: 1.5; margin-bottom: 20px; }
.modal-actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
.modal-btn { padding: 10px 24px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; border: none; }
.modal-btn-primary { background: #dc3545; color: #fff; }
.modal-btn-primary:hover { background: #bb2d3b; }
.modal-btn-warning { background: #e67e22; color: #fff; }
.modal-btn-warning:hover { background: #d35400; }
.modal-btn-default { background: #6c757d; color: #fff; }
.modal-btn-default:hover { background: #5c636a; }
.modal-btn-info { background: #0d6efd; color: #fff; }
.modal-btn-info:hover { background: #0b5ed7; }
.btn-info { background: #0d6efd; color: #fff; border: 1px solid #0d6efd; padding: 10px 28px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; box-sizing: border-box; white-space: nowrap; display: inline-block; }
.btn-info:hover { background: #0b5ed7; }
.btn-info:disabled { opacity: 0.6; cursor: not-allowed; }
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

            <h3 style="color:#0d6efd;">Updates</h3>
            <p>
                Check whether the plugin is up to date with the latest version on GitHub.
            </p>
            <div style="display:flex; gap:10px; align-items:start;">
                <div>
                    <button type="button" class="btn-info" id="check_updates_btn" onclick="haDev.checkUpdates();">&#8635; Check for Updates</button>
                </div>
                <div id="update_result"></div>
            </div>

            <hr style="margin:20px 0;">

            <h3 style="color:#e67e22;">Plugin Management</h3>
            <p>
                Reinstall the plugin to apply file updates, or uninstall it from the system.
            </p>
            <div style="display:flex; gap:10px;">
                <button type="button" class="btn-warning" id="reinstall_btn" onclick="haDev.reinstall();">&#9888; Reinstall Plugin</button>
                <button type="button" class="btn-danger" id="uninstall_btn" onclick="haDev.uninstall();">&#9888; Uninstall Plugin</button>
            </div>

            <hr style="margin:20px 0;">

            <h3 style="color:#dc3545;">Reset</h3>
            <p>
                Reset cached entities and generated commands, or perform a full factory reset that also
                clears the HA URL and Long-Lived Access Token. FPPD will be prompted to restart
                after either action.
            </p>
            <div style="display:flex; gap:10px;">
                <button type="button" class="btn-warning" id="reset_cache_btn" onclick="haDev.resetCache();">&#9888; Reset Cached Entities</button>
                <button type="button" class="btn-danger" id="reset_btn" onclick="haDev.reset();">&#9888; Reset Everything</button>
            </div>
        </div>
    </fieldset>
</div>

<div id="modal_overlay" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-message" id="modal_message"></div>
        <div class="modal-actions" id="modal_actions"></div>
    </div>
</div>

<script>
var haDev = {
    showModal: function(message, buttons) {
        $('#modal_message').html(message);
        var $actions = $('#modal_actions').empty();
        $.each(buttons, function(i, btn) {
            $actions.append(
                $('<button>', {
                    text: btn.label,
                    class: 'modal-btn ' + (btn['class'] || 'modal-btn-default'),
                    click: function() {
                        if (btn.onClick) btn.onClick();
                        else haDev.hideModal();
                    }
                })
            );
        });
        $('#modal_overlay').show();
    },
    hideModal: function() {
        $('#modal_overlay').hide();
    },
    showConfirm: function(message, onConfirm, confirmClass) {
        haDev.showModal(message, [
            { label: 'Cancel', 'class': 'modal-btn-default', onClick: haDev.hideModal },
            { label: 'Confirm', 'class': confirmClass || 'modal-btn-primary', onClick: function() { haDev.hideModal(); if (onConfirm) onConfirm(); } }
        ]);
    },
    showAlert: function(message) {
        haDev.showModal(message, [
            { label: 'OK', 'class': 'modal-btn-primary' }
        ]);
    },
    resetCache: function() {
        haDev.showConfirm('This will clear all cached entities and generated commands. FPPD will be prompted to restart. The HA URL and token will be preserved. Are you sure?', function() {
            $('#reset_cache_btn').prop('disabled', true);
            $.ajax({
                url: 'api/plugin/fpp-haCommands/reset-cache',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                success: function() {
                    location.reload();
                },
                error: function(xhr) {
                    var msg = 'Could not reach the plugin API.';
                    try {
                        var resp = JSON.parse(xhr.responseText);
                        if (resp.error) msg = resp.error;
                    } catch(e) {}
                    $('#reset_cache_btn').prop('disabled', false);
                    haDev.showAlert(msg);
                }
            });
        }, 'modal-btn-primary');
    },
    reset: function() {
        haDev.showConfirm('This will clear all configuration, cached entities, and generated commands. The HA URL and token will not be preserved. FPPD will be prompted to restart. Are you sure?', function() {
            $('#reset_btn').prop('disabled', true);
            $.ajax({
                url: 'api/plugin/fpp-haCommands/reset',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                success: function() {
                    location.reload();
                },
                error: function(xhr) {
                    var msg = 'Could not reach the plugin API.';
                    try {
                        var resp = JSON.parse(xhr.responseText);
                        if (resp.error) msg = resp.error;
                    } catch(e) {}
                    $('#reset_btn').prop('disabled', false);
                    haDev.showAlert(msg);
                }
            });
        }, 'modal-btn-primary');
    },
    checkUpdates: function() {
        $('#check_updates_btn').html('&#8635; Check for Updates').prop('disabled', true).attr('onclick', 'haDev.checkUpdates();');
        $('#update_result').html('<span style="color:#6c757d;">Checking...</span>');
        $.ajax({
            url: 'api/plugin/fpp-haCommands/check-updates',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.updateAvailable) {
                    $('#check_updates_btn').html('&#8635; Install Updates').prop('disabled', false).attr('onclick', 'haDev.installUpdates();');
                    $('#update_result').html('<span style="color:#e67e22;font-weight:600;">&#9888; Update available!</span><br><span style="color:#6c757d;font-size:13px;">Local: ' + data.localSha + '<br>Remote: ' + data.remoteSha + '<br><a href="https://github.com/jessica12ryan/fpp-haCommands" target="_blank">View on GitHub</a></span>');
                } else {
                    $('#check_updates_btn').html('&#8635; Check for Updates').prop('disabled', false).attr('onclick', 'haDev.checkUpdates();');
                    $('#update_result').html('<span style="color:#28a745;font-weight:600;">&#10003; Plugin is up to date</span><br><span style="color:#6c757d;font-size:13px;">' + data.localSha + '</span>');
                }
            },
            error: function(xhr) {
                $('#check_updates_btn').html('&#8635; Check for Updates').prop('disabled', false).attr('onclick', 'haDev.checkUpdates();');
                var msg = 'Could not reach the plugin API.';
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.error) msg = resp.error;
                } catch(e) {}
                $('#update_result').html('<span style="color:#dc3545;">' + msg + '</span>');
            }
        });
    },
    installUpdates: function() {
        haDev.showConfirm('This will update the plugin to the latest version from GitHub. Your configuration will be preserved. Are you sure?', function() {
            $('#check_updates_btn').prop('disabled', true);
            $.ajax({
                url: 'api/plugin/fpp-haCommands/update',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                success: function() {
                    location.reload();
                },
                error: function(xhr) {
                    var msg = 'Could not reach the plugin API.';
                    try {
                        var resp = JSON.parse(xhr.responseText);
                        if (resp.error) msg = resp.error;
                    } catch(e) {}
                    $('#check_updates_btn').prop('disabled', false);
                    haDev.showAlert(msg);
                }
            });
        }, 'modal-btn-info');
    },
    reinstall: function() {
        haDev.showConfirm('This will reinstall the plugin. Your configuration will be preserved. Are you sure?', function() {
            $('#reinstall_btn').prop('disabled', true);
            $.ajax({
                url: 'api/plugin/fpp-haCommands/reinstall',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                success: function() {
                    location.reload();
                },
                error: function(xhr) {
                    var msg = 'Could not reach the plugin API.';
                    try {
                        var resp = JSON.parse(xhr.responseText);
                        if (resp.error) msg = resp.error;
                    } catch(e) {}
                    $('#reinstall_btn').prop('disabled', false);
                    haDev.showAlert(msg);
                }
            });
        }, 'modal-btn-warning');
    },
    uninstall: function() {
        haDev.showConfirm('This will completely remove the plugin and all its files. Configuration will be lost. FPPD will be prompted to restart. Are you sure?', function() {
            $('#uninstall_btn').prop('disabled', true);
            $.ajax({
                url: 'api/plugin/fpp-haCommands/uninstall',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                success: function() {
                    window.location.href = 'plugins.php?tab=available';
                },
                error: function(xhr) {
                    var msg = 'Could not reach the plugin API.';
                    try {
                        var resp = JSON.parse(xhr.responseText);
                        if (resp.error) msg = resp.error;
                    } catch(e) {}
                    $('#uninstall_btn').prop('disabled', false);
                    haDev.showAlert(msg);
                }
            });
        }, 'modal-btn-primary');
    }
};

$(document).on('click', '#modal_overlay', function(e) {
    if (e.target === this) haDev.hideModal();
});
</script>
