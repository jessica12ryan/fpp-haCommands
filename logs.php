<?php
$pluginDir = __DIR__;
$logDir = getenv('LOGDIR') ?: '/home/fpp/media/logs';
$logFile = $logDir . '/plugin-fpp-haCommands.log';

$uiLevel = (int)($settings['uiLevel'] ?? 0);
$showLogsTab = $uiLevel >= 1;
$showDevTab = $uiLevel >= 3;
?>
<style>
.tab-bar { display: flex; flex-wrap: wrap; gap: 0; margin-bottom: 12px; border-bottom: 2px solid var(--bs-border-color, #dee2e6); }
.tab-bar a { display: block; padding: 8px 18px; text-decoration: none; color: var(--bs-body-color, #495057); background: var(--bs-tertiary-bg, #f8f9fa); border: 1px solid var(--bs-border-color, #dee2e6); border-bottom: none; border-radius: 4px 4px 0 0; margin-bottom: -2px; margin-right: 3px; font-size: 14px; }
.tab-bar a.active { background: var(--bs-body-bg, #fff); color: var(--bs-body-color, #212529); border-color: var(--bs-border-color, #dee2e6); border-bottom-color: var(--bs-body-bg, #fff); font-weight: 600; }
.tab-bar a:hover:not(.active) { background: var(--bs-secondary-bg, #e9ecef); }
.log-table { width: 100%; border-collapse: collapse; font-family: 'Courier New', monospace; font-size: 12px; }
.log-table th { text-align: left; padding: 6px 8px; border-bottom: 2px solid var(--bs-border-color, #dee2e6); white-space: nowrap; }
.log-table td { padding: 4px 8px; border-bottom: 1px solid var(--bs-border-color, #dee2e6); vertical-align: top; }
.log-table tr:hover { background: var(--bs-tertiary-bg, #f8f9fa); }
.log-info { color: var(--bs-body-color, #212529); }
.log-success { color: #198754; }
.log-error { color: #dc3545; }
.log-warning { color: #fd7e14; }
</style>

<?php include __DIR__ . '/tabs.inc'; ?>

<div style="margin:0 auto;">
    <fieldset class="border p-3">
        <legend>Plugin Log</legend>
        <div class="p-3">
            <p>
                <b>Log file:</b> <code><?php echo htmlspecialchars($logFile); ?></code>
                &nbsp;&nbsp;
                <input type="button" class="buttons" value="&#8635; Refresh" onclick="haLogs.refresh();">
            </p>
            <div id="log_container" style="max-height: 600px; overflow-y: auto; border: 1px solid var(--bs-border-color, #dee2e6); border-radius: 4px;">
                <table class="log-table">
                    <thead>
                        <tr>
                            <th style="width: 160px;">Date/Time</th>
                            <th style="width: 60px;">Level</th>
                            <th style="width: 130px;">Source</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody id="log_body">
                        <tr><td colspan="4" style="text-align:center;padding:20px;color:var(--bs-secondary-color, #6c757d);">Loading logs...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </fieldset>
</div>

<script>
var haLogs = {
    refresh: function() {
        $('#log_body').html('<tr><td colspan="4" style="text-align:center;padding:20px;color:var(--bs-secondary-color, #6c757d);">Loading logs...</td></tr>');
        $.ajax({
            url: 'api/plugin/fpp-haCommands/logs',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success && data.entries) {
                    if (data.entries.length === 0) {
                        $('#log_body').html('<tr><td colspan="4" style="text-align:center;padding:20px;color:var(--bs-secondary-color, #6c757d);">No log entries found.</td></tr>');
                    } else {
                        var html = '';
                        for (var i = 0; i < data.entries.length; i++) {
                            var e = data.entries[i];
                            var cls = 'log-info';
                            if (e.level === 'SUCCESS') cls = 'log-success';
                            else if (e.level === 'ERROR') cls = 'log-error';
                            else if (e.level === 'WARNING') cls = 'log-warning';
                            html += '<tr class="' + cls + '">' +
                                '<td style="white-space:nowrap;">' + escHtml(e.timestamp) + '</td>' +
                                '<td><b>' + escHtml(e.level) + '</b></td>' +
                                '<td>' + escHtml(e.source) + '</td>' +
                                '<td>' + escHtml(e.message) + '</td>' +
                                '</tr>';
                        }
                        $('#log_body').html(html);
                    }
                } else {
                    $('#log_body').html('<tr><td colspan="4" style="text-align:center;padding:20px;color:#dc3545;">Error loading logs: ' + (data.error || 'Unknown error') + '</td></tr>');
                }
            },
            error: function(xhr) {
                var msg = 'Could not reach the plugin API.';
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.error) msg = resp.error;
                } catch(e) {}
                $('#log_body').html('<tr><td colspan="4" style="text-align:center;padding:20px;color:#dc3545;">' + msg + '</td></tr>');
            }
        });
    }
};

function escHtml(s) {
    if (s == null) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

$(document).ready(function() {
    haLogs.refresh();
});
</script>

<?php include __DIR__ . '/footer.inc'; ?>
