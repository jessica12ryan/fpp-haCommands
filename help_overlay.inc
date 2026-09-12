<?php
/**
 * #############################################################
 * ## Home Assistant Commands Plugin for FPP (fpp-haCommands) ##
 * ## Author: jessica12ryan                                   ##
 * ## URL: https://github.com/jessica12ryan/fpp-haCommands    ##
 * #############################################################
 * ## help_overlay.inc                                        ##
 * ## Shared F1 help overlay shown on every plugin page.      ##
 * ## Included via footer.inc so it appears site-wide.        ##
 * #############################################################
 */

$hacHelpPage = $_GET['page'] ?? '';
if (empty($hacHelpPage)) {
    // Fallback: try to infer from request URI (e.g. direct access without ?page=)
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (preg_match('/page=([^&]+)/', $uri, $m)) {
        $hacHelpPage = $m[1];
    }
}
$hacHelpPage = basename($hacHelpPage);
if (empty($hacHelpPage) || $hacHelpPage === 'plugin.php' || $hacHelpPage === 'fpp-haCommands') {
    $hacHelpPage = 'status.php';
}

$hacHelpDocs = [
    'status.php' => [
        'title' => 'Status &mdash; Help',
        'body' => '
            <p>This page gives you an at-a-glance overview of the plugin&rsquo;s current state.</p>
            <h4>Connection</h4>
            <ul>
                <li><b>&#9679; Configured</b> (green) &mdash; HA URL and token are saved. Click the URL to verify it.</li>
                <li><b>&#9679; Not configured</b> (red) &mdash; Go to <b>Config</b> to enter your HA URL and Long-Lived Access Token.</li>
            </ul>
            <h4>Cache &amp; Commands</h4>
            <ul>
                <li><b>Cache updated</b> &mdash; Timestamp of the last successful <b>Update Entities</b> run (from <code>config/entities_cache.json</code>).</li>
                <li><b>Entities cached</b> &mdash; Total entities discovered via <code>/api/states</code>.</li>
                <li><b>Domains discovered</b> &mdash; Unique HA domains (light, switch, cover, etc.).</li>
                <li><b>Commands (with entities)</b> &mdash; Count of generated commands that have an entity dropdown, out of total commands.</li>
                <li><b>HA Command Presets</b> &mdash; Number of FPP Command Presets that use an <code>HA - </code> command (from <code>config/commandPresets.json</code>).</li>
            </ul>
            <h4>Generated Commands Table</h4>
            <p>Lists every <code>HA - domain.service</code> command that has entities. Sorted alphabetically. Use this to confirm entities were discovered correctly after <b>Config &rarr; Update Entities</b>.</p>
            <h4>Typical Workflow</h4>
            <ol>
                <li>If status shows <b>Not configured</b>, go to <b>Config</b> and save settings.</li>
                <li>Click <b>Update Entities</b> on the Config page to refresh this data.</li>
                <li>After FPPD restarts, return here to verify counts increased.</li>
                <li>Use the commands in <b>Playlists &rarr; Command</b> entries or <b>Command Presets</b> for xLights.</li>
            </ol>
            <h4>Troubleshooting</h4>
            <ul>
                <li>Counts are zero? Check <b>Logs</b> tab and run <b>Test Connection</b> on the Config page.</li>
                <li>Token shows <b>Yes</b> but connection fails &mdash; token may be expired; regenerate in HA at <code>Profile &rarr; Long-Lived Access Tokens</code>.</li>
            </ul>
        ',
    ],
    'config.php' => [
        'title' => 'Config &mdash; Help',
        'body' => '
            <p>Configure the connection to Home Assistant and generate FPP commands from your HA entities.</p>
            <h4>HA URL</h4>
            <p>Full URL to your Home Assistant instance including protocol and port, e.g. <code>http://homeassistant.local:8123</code> or <code>http://192.168.1.100:8123</code>. Trailing slash is removed automatically.</p>
            <h4>Long-Lived Access Token</h4>
            <p>Generate at <b>HA &rarr; Profile &rarr; Long-Lived Access Tokens</b> (<code>http://HA:8123/profile</code>). Paste here; it is stored in <code>config/plugin.fpp-haCommands</code> (not in browser storage). Field is masked as a password.</p>
            <h4>Buttons</h4>
            <ul>
                <li><b>Save Settings</b> &mdash; Writes HA URL and token to disk. Do this before testing or updating.</li>
                <li><b>Test Connection</b> &mdash; Calls <code>api/plugin/fpp-haCommands/test</code> which does <code>GET /api/</code> with your token. Shows HA version on success or a friendly error (401 = bad token, 404 = wrong URL, timeout = unreachable).</li>
                <li><b>Update Entities</b> &mdash; Fetches <code>/api/states</code> and <code>/api/services</code>, builds <code>config/entities_cache.json</code> and <code>commands/descriptions.json</code>, then sets the FPP restart flag so FPPD reloads commands. Shows a summary like &ldquo;42 entities across 8 domains, 15 commands generated&rdquo;. Requires confirmation.</li>
            </ul>
            <h4>Status Indicators</h4>
            <ul>
                <li><b>Token configured: Yes/No</b> &mdash; Whether a token is saved.</li>
                <li><b>Commands generated</b> &mdash; Number of command definitions currently on disk.</li>
                <li><b>Cached entities</b> &mdash; Entities from last update.</li>
            </ul>
            <h4>xLights Setup</h4>
            <p>Optional section for frame-accurate sequence triggers. Shows count of presets using <code>HA - </code> commands and a button to <b>Set Command Presets</b> (<code>commandPresets.php</code>). Create a preset per HA action (e.g. &ldquo;Porch Lights On&rdquo; &rarr; <code>HA - light.turn_on</code> + <code>light.porch</code>), then reference the preset name in xLights&rsquo; <b>FPP Commands</b> timing track.</p>
            <h4>Important Notes</h4>
            <ul>
                <li>After <b>Update Entities</b>, FPPD prompts for restart; refresh the page after restart for commands to appear in the playlist editor.</li>
                <li>Commands appear in playlists as <b>HA - domain.service</b> with an entity dropdown.</li>
                <li>Re-run <b>Update Entities</b> whenever you add/remove HA entities.</li>
            </ul>
        ',
    ],
    'logs.php' => [
        'title' => 'Logs &mdash; Help',
        'body' => '
            <p>View the plugin&rsquo;s log file to debug connection issues and command execution.</p>
            <h4>Log File</h4>
            <p>Path shown at the top: <code>/home/fpp/media/logs/plugin-fpp-haCommands.log</code> (or <code>$LOGDIR</code> if set). Also accessible via SSH: <code>tail -20 /home/fpp/media/logs/plugin-fpp-haCommands.log</code></p>
            <h4>Table Columns</h4>
            <ul>
                <li><b>Date/Time</b> &mdash; Timestamp <code>YYYY-MM-DD HH:MM:SS</code>.</li>
                <li><b>Level</b> &mdash; <span style="color:#198754"><b>SUCCESS</b></span> (green), <span style="color:#dc3545"><b>ERROR</b></span> (red), <span style="color:#fd7e14"><b>WARNING</b></span> (orange), <b>INFO</b> (default). Derived from message prefix or HTTP 4xx/5xx.</li>
                <li><b>Source</b> &mdash; Which component logged it: <code>api</code>, <code>call_action</code>, etc.</li>
                <li><b>Message</b> &mdash; Details (e.g. &ldquo;POST light.turn_on entity=light.porch&rdquo;, &ldquo;SUCCESS: light.turn_on called&rdquo;, or curl/HTTP errors).</li>
            </ul>
            <h4>Refresh Button</h4>
            <p>Click <b>&#8635; Refresh</b> to re-fetch the last 50 lines via <code>api/plugin/fpp-haCommands/logs</code>. The table is limited to 50 entries and shown newest-first.</p>
            <h4>How to Use</h4>
            <ul>
                <li>After a playlist command fails, check here for <span style="color:#dc3545">ERROR</span> entries.</li>
                <li>After <b>Test Connection</b> or <b>Update Entities</b>, check for success/failure messages.</li>
                <li>Filter mentally by Level color; hover highlights rows.</li>
                <li>If log is empty, the plugin may not have run yet &mdash; trigger a command to generate entries.</li>
            </ul>
            <h4>Visibility</h4>
            <p>This tab only appears when FPP <b>UI Level</b> is set to at least <b>Advanced (1)</b> in FPP settings.</p>
        ',
    ],
    'help.php' => [
        'title' => 'Help &mdash; Usage Guide',
        'body' => '
            <p>You are on the full <b>Help &amp; Usage Guide</b>. This F1 popup is a quick reference; the page itself contains the complete documentation.</p>
            <h4>What This Plugin Does</h4>
            <p>Turns every Home Assistant action (<code>light.turn_on</code>, <code>switch.turn_off</code>, <code>scene.turn_on</code>, etc.) into an FPP command usable <b>between sequences</b> (playlists) or <b>inside a sequence</b> (xLights FPP Commands track at exact frames).</p>
            <h4>Quick Start (also on page)</h4>
            <ol>
                <li>Generate token in HA: <b>Profile &rarr; Long-Lived Access Tokens</b></li>
                <li>Configure on <b>Config</b> page: enter HA URL + token &rarr; <b>Save Settings</b></li>
                <li><b>Test Connection</b> &mdash; verify HA is reachable</li>
                <li><b>Update Entities</b> &mdash; discover entities and generate commands</li>
                <li>Use commands in playlists or sequences</li>
            </ol>
            <h4>Two Usage Modes</h4>
            <ul>
                <li><b>Playlists (between sequences):</b> Add a <b>Command</b> entry in the playlist editor, pick <code>HA - domain.action</code>, choose entity and parameters. Fires when playlist reaches that step.</li>
                <li><b>Inside a Sequence (frame-accurate):</b> 1) Create a <b>Command Preset</b> for the HA action, 2) Add an <b>FPP Commands</b> timing track in xLights, 3) Place markers with the preset name at the desired time, 4) Render/export and play on FPP &mdash; preset fires at the correct frame.</li>
            </ul>
            <h4>Command Parameters</h4>
            <p>Extra fields vary by domain (e.g. light: brightness/RGB/temp, cover: position, climate: temperature/HVAC, etc.). All commands have an <b>Extra JSON</b> field for advanced data like <code>{"rgb_color":[255,0,0],"effect":"colorloop"}</code>. See the table on the page for the full list.</p>
            <h4>HA - Get State</h4>
            <p>Special command <code>HA - Get State</code> fetches an entity&rsquo;s current state and writes it to the log &mdash; useful for debugging.</p>
            <h4>Troubleshooting Shortcuts</h4>
            <ul>
                <li>Commands not in playlist? &rarr; Click <b>Update Entities</b> and restart FPPD, check token expiry.</li>
                <li>Command fails at runtime? &rarr; Check <b>Logs</b> or <code>tail -20 /home/fpp/media/logs/plugin-fpp-haCommands.log</code>.</li>
                <li>Added/removed HA entities? &rarr; Re-run <b>Update Entities</b> on Config.</li>
            </ul>
        ',
    ],
    'about.php' => [
        'title' => 'About &mdash; Help',
        'body' => '
            <p>Learn what the plugin does, how it works, and where to get more info.</p>
            <h4>Features</h4>
            <ul>
                <li>Auto-discovers all HA entities and actions &mdash; no manual setup.</li>
                <li>Generates individual FPP commands for every HA action.</li>
                <li>Dynamic entity dropdowns in the playlist editor.</li>
                <li>Domain-specific parameters (brightness, color, temperature, position, etc.) plus Extra JSON.</li>
                <li><code>HA - Get State</code> for debugging.</li>
                <li>Works with FPP 8.x, 9.x, and 10.x.</li>
            </ul>
            <h4>How It Works</h4>
            <ol>
                <li>You save HA URL + token on the <b>Config</b> page.</li>
                <li>Plugin fetches <code>/api/states</code> (entities) and <code>/api/services</code> (actions).</li>
                <li>For each action a definition is written to <code>commands/descriptions.json</code>.</li>
                <li>FPP exposes them in the playlist editor with entity dropdowns.</li>
                <li>At runtime <code>call_action.php</code> POSTs to HA&rsquo;s REST API (<code>/api/services/&lt;domain&gt;/&lt;service&gt;</code>).</li>
            </ol>
            <h4>Frame-Accurate Sequences</h4>
            <p>In addition to playlist commands, use xLights&rsquo; <b>FPP Commands</b> track + FPP <b>Command Presets</b> to trigger HA at exact frames. See the <b>Help</b> page for step-by-step instructions.</p>
            <h4>Links (on page)</h4>
            <ul>
                <li><b>GitHub Repository</b> &mdash; README, install guide.</li>
                <li><b>Issue Tracker</b> &mdash; bugs and feature requests.</li>
                <li><b>xLights Gems Video Demo</b> &mdash; visual walkthrough.</li>
            </ul>
            <h4>Plugin Info</h4>
            <p>Author: <b>jessica12ryan</b> &bull; License: <b>MIT</b> &bull; This plugin is independent and not affiliated with Falcon Christmas (FPP) or Nabu Casa (Home Assistant).</p>
        ',
    ],
    'developer.php' => [
        'title' => 'Developer &mdash; Help',
        'body' => '
            <p>Advanced tools for maintaining and troubleshooting the plugin. This tab only appears when FPP <b>UI Level</b> is set to <b>Developer (3)</b>.</p>
            <h4>Updates</h4>
            <ul>
                <li><b>&#8635; Check for Updates</b> &mdash; Calls <code>api/plugin/fpp-haCommands/check-updates</code> which compares local <code>git rev-parse HEAD</code> vs <code>git ls-remote origin main</code>. Shows local/remote 7-char SHAs. If behind, button becomes <b>Install Updates</b>.</li>
                <li><b>Install Updates</b> &mdash; Backs up <code>config/ha_settings.json</code>, <code>config/plugin.fpp-haCommands</code>, <code>config/entities_cache.json</code>, <code>commands/descriptions.json</code> to <code>/tmp</code>, does <code>git fetch/checkout/clean/reset --hard origin/main</code>, restores backups, fixes permissions, updates <code>pluginInfo.json</code> SHAs, and sets the FPP restart flag.</li>
            </ul>
            <h4>Plugin Management</h4>
            <ul>
                <li><b>&#9888; Reinstall Plugin</b> &mdash; Same git reset flow as update but without version check; preserves config. Use to repair permissions or corrupted files.</li>
                <li><b>&#9888; Uninstall Plugin</b> &mdash; Recursively deletes the plugin directory and sets restart flag; redirects to <code>plugins.php?tab=available</code>. <b>All config lost.</b></li>
            </ul>
            <h4>Reset</h4>
            <ul>
                <li><b>&#9888; Reset Cached Entities</b> &mdash; Deletes <code>config/entities_cache.json</code> and <code>commands/descriptions.json</code> only; preserves HA URL/token. Prompts restart. Use when entities are stale.</li>
                <li><b>&#9888; Reset Everything</b> &mdash; Clears URL/token to empty, deletes cache and descriptions; <b>HA connection must be reconfigured.</b> Also sets restart flag.</li>
            </ul>
            <h4>Safety</h4>
            <p>All destructive actions show a confirmation modal. Buttons disable while the API call is in flight. On success the page reloads (or redirects for uninstall). On error an alert modal shows the message.</p>
            <h4>When to Use</h4>
            <ul>
                <li>Update when GitHub shows new commits.</li>
                <li>Reinstall if <code>Update Entities</code> fails with permission errors.</li>
                <li>Reset cache after major HA changes instead of full Update.</li>
                <li>Reset everything to start over or before handing off the device.</li>
            </ul>
        ',
    ],
];

$hacCurrentHelp = $hacHelpDocs[$hacHelpPage] ?? null;
if ($hacCurrentHelp === null) {
    // Generic fallback covering all pages
    $hacCurrentHelp = [
        'title' => 'HA Commands &mdash; Help',
        'body' => '
            <p>This plugin bridges <b>Home Assistant</b> and <b>Falcon Player (FPP)</b>.</p>
            <ul>
                <li><b>Status</b> &mdash; Overview of connection, cache, and generated commands.</li>
                <li><b>Config</b> &mdash; Set HA URL/token, test connection, and generate commands.</li>
                <li><b>Logs</b> &mdash; View the last 50 log entries (UI Level &ge; 1).</li>
                <li><b>Help</b> &mdash; Full usage guide for playlists and xLights sequences.</li>
                <li><b>About</b> &mdash; Features, how it works, and links.</li>
                <li><b>Developer</b> &mdash; Updates, reinstall, and reset tools (UI Level &ge; 3).</li>
            </ul>
            <p>Use the tab bar at the top to navigate. Press <kbd>F1</kbd> again or <kbd>Esc</kbd> to close this help.</p>
        ',
    ];
}

$hacHelpTitle = $hacCurrentHelp['title'];
$hacHelpBody = $hacCurrentHelp['body'];
?>

<style>
.hac-help-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.55);
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    box-sizing: border-box;
}
.hac-help-overlay.open {
    display: flex;
}
.hac-help-box {
    background: var(--bs-body-bg, #fff);
    color: var(--bs-body-color, #212529);
    border-radius: 8px;
    max-width: 760px;
    width: 100%;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 8px 30px rgba(0,0,0,0.4);
    border: 1px solid var(--bs-border-color, #dee2e6);
}
.hac-help-header {
    padding: 14px 20px;
    border-bottom: 1px solid var(--bs-border-color, #dee2e6);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    background: var(--bs-body-bg, #fff);
    border-radius: 8px 8px 0 0;
    z-index: 1;
}
.hac-help-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}
.hac-help-header-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}
.hac-help-close {
    background: #6c757d;
    color: #fff;
    border: none;
    padding: 6px 14px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
}
.hac-help-close:hover {
    background: #5c636a;
}
.hac-help-body {
    padding: 20px;
    font-size: 14px;
    line-height: 1.6;
}
.hac-help-body h4 {
    margin: 18px 0 8px 0;
    color: #0d6efd;
    font-size: 14px;
    font-weight: 600;
}
.hac-help-body h4:first-child {
    margin-top: 0;
}
.hac-help-body p {
    margin: 8px 0;
}
.hac-help-body ul, .hac-help-body ol {
    margin: 8px 0 8px 20px;
    padding: 0;
}
.hac-help-body li {
    margin-bottom: 4px;
}
.hac-help-body code {
    background: var(--bs-tertiary-bg, #f8f9fa);
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 13px;
    border: 1px solid var(--bs-border-color, #e9ecef);
}
.hac-help-body kbd {
    background: var(--bs-tertiary-bg, #e9ecef);
    border: 1px solid var(--bs-border-color, #ccc);
    border-bottom-width: 2px;
    padding: 1px 6px;
    border-radius: 3px;
    font-family: monospace;
    font-size: 12px;
    white-space: nowrap;
}
.hac-help-footer {
    padding: 10px 20px 14px 20px;
    border-top: 1px solid var(--bs-border-color, #dee2e6);
    font-size: 12px;
    color: var(--bs-secondary-color, #6c757d);
    text-align: center;
    background: var(--bs-tertiary-bg, #f8f9fa);
    border-radius: 0 0 8px 8px;
}
.hac-help-hint {
    text-align: center;
    margin: 10px auto 0 auto;
    font-size: 12px;
    color: var(--bs-secondary-color, #6c757d);
}
.hac-help-hint kbd {
    background: var(--bs-tertiary-bg, #e9ecef);
    border: 1px solid var(--bs-border-color, #dee2e6);
    border-bottom-width: 2px;
    padding: 1px 5px;
    border-radius: 3px;
    font-family: monospace;
    font-size: 11px;
}
.hac-help-hint a {
    color: #0d6efd;
    text-decoration: none;
    font-weight: 600;
}
.hac-help-hint a:hover {
    text-decoration: underline;
}
@media (max-width: 600px) {
    .hac-help-box {
        max-height: 90vh;
    }
    .hac-help-body {
        padding: 14px;
    }
}
</style>

<div id="hac-help-overlay" class="hac-help-overlay" role="dialog" aria-modal="true" aria-labelledby="hac-help-title">
    <div class="hac-help-box" role="document">
        <div class="hac-help-header">
            <h3 id="hac-help-title"><?php echo $hacHelpTitle; ?></h3>
            <div class="hac-help-header-actions">
                <span style="font-size:11px; color:var(--bs-secondary-color,#6c757d); margin-right:8px; display:none;" class="d-none d-md-inline">Press <kbd>F1</kbd> or <kbd>Esc</kbd> to close</span>
                <button type="button" class="hac-help-close" onclick="hacHelp.hide()" aria-label="Close help">Close</button>
            </div>
        </div>
        <div class="hac-help-body">
            <?php echo $hacHelpBody; ?>
        </div>
        <div class="hac-help-footer">
            Press <kbd>F1</kbd> to toggle &bull; <kbd>Esc</kbd> to close &bull; Click outside to dismiss &bull; Current page: <code><?php echo htmlspecialchars($hacHelpPage); ?></code>
        </div>
    </div>
</div>

<div class="hac-help-hint">
    Press <kbd>F1</kbd> for Help &mdash; <a href="#" onclick="hacHelp.show(); return false;" aria-label="Show help for current page">Show Help</a>
    <span style="margin: 0 6px; opacity: 0.5;">|</span>
    <span style="font-size:11px;">Page: <?php echo htmlspecialchars($hacHelpPage); ?></span>
</div>

<script>
var hacHelp = (function() {
    var overlay = null;

    function getOverlay() {
        if (!overlay) overlay = document.getElementById('hac-help-overlay');
        return overlay;
    }

    function show() {
        var o = getOverlay();
        if (!o) return;
        o.classList.add('open');
        o.style.display = 'flex';
        // prevent background scroll
        document.body.style.overflow = 'hidden';
        // focus close button for accessibility
        var btn = o.querySelector('.hac-help-close');
        if (btn) btn.focus();
    }

    function hide() {
        var o = getOverlay();
        if (!o) return;
        o.classList.remove('open');
        o.style.display = 'none';
        document.body.style.overflow = '';
    }

    function toggle() {
        var o = getOverlay();
        if (!o) return;
        if (o.classList.contains('open')) hide();
        else show();
    }

    function isVisible() {
        var o = getOverlay();
        return o && o.classList.contains('open');
    }

    // F1 and Esc handlers
    document.addEventListener('keydown', function(e) {
        var isF1 = e.key === 'F1' || e.keyCode === 112 || e.which === 112;
        var isEsc = e.key === 'Escape' || e.key === 'Esc' || e.keyCode === 27;

        if (isF1) {
            e.preventDefault();
            e.stopPropagation();
            toggle();
            return false;
        }
        if (isEsc && isVisible()) {
            e.preventDefault();
            hide();
        }
    });

    // Click outside to close
    document.addEventListener('click', function(e) {
        var o = getOverlay();
        if (!o || !o.classList.contains('open')) return;
        if (e.target === o) {
            hide();
        }
    });

    // Expose globally
    return {
        show: show,
        hide: hide,
        toggle: toggle,
        isVisible: isVisible
    };
})();
</script>
