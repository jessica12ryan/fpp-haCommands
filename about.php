<?php
/**
 * #############################################################
 * ## Home Assistant Commands Plugin for FPP (fpp-haCommands) ##
 * ## Author: jessica12ryan                                   ##
 * ## URL: https://github.com/jessica12ryan/fpp-haCommands    ##
 * #############################################################
 * ## about.php                                               ##
 * #############################################################
 */
?>
<style>
.tab-bar { display: flex; gap: 0; margin-bottom: 12px; border-bottom: 2px solid var(--bs-border-color, #dee2e6); }
.tab-bar a { display: block; padding: 8px 18px; text-decoration: none; color: var(--bs-body-color, #495057); background: var(--bs-tertiary-bg, #f8f9fa); border: 1px solid var(--bs-border-color, #dee2e6); border-bottom: none; border-radius: 4px 4px 0 0; margin-bottom: -2px; margin-right: 3px; font-size: 14px; }
.tab-bar a.active { background: var(--bs-body-bg, #fff); color: var(--bs-body-color, #212529); border-color: var(--bs-border-color, #dee2e6); border-bottom-color: var(--bs-body-bg, #fff); font-weight: 600; }
.tab-bar a:hover:not(.active) { background: var(--bs-secondary-bg, #e9ecef); }
</style>

<div class="tab-bar">
    <a href="plugin.php?plugin=fpp-haCommands&page=status.php" class="<?php echo basename(__FILE__) === 'status.php' ? 'active' : ''; ?>">&#9632; Status</a>
    <a href="plugin.php?plugin=fpp-haCommands&page=config.php" class="<?php echo basename(__FILE__) === 'config.php' ? 'active' : ''; ?>">&#9881; Config</a>
    <a href="plugin.php?plugin=fpp-haCommands&page=help.php" class="<?php echo basename(__FILE__) === 'help.php' ? 'active' : ''; ?>">&#63; Help</a>
    <a href="plugin.php?plugin=fpp-haCommands&page=about.php" class="<?php echo basename(__FILE__) === 'about.php' ? 'active' : ''; ?>">&#9432; About</a>
</div>

<div style="margin:0 auto;"> <br />
    <fieldset class="border p-3">
        <legend>About Home Assistant Commands Plugin</legend>
        <div class="p-3">
            <div id='credits'>
                <h3 style="margin-top:0;">Home Assistant Commands Plugin for FPP</h3>

                <p>
                    Bridges <b>Home Assistant</b> and <b>Falcon Player (FPP)</b>, turning every HA action
                    into an FPP command you can use in playlists, scheduler, GPIO's, and xLights sequences.
                </p>

                <h4>Features</h4>
                <ul>
                    <li>Auto-discovers all HA entities and actions &mdash; no manual setup</li>
                    <li>Generates individual FPP commands for every HA action (light.turn_on, switch.turn_off, scene.turn_on, etc.)</li>
                    <li>Dynamic entity dropdowns in the playlist editor &mdash; pick your entity from a list</li>
                    <li>Domain-specific parameters (brightness, color, temperature, position, speed, etc.)</li>
                    <li>Extra JSON field for advanced data</li>
                    <li>HA - Get State command for debugging</li>
                    <li>Works with FPP 8.x, 9.x, and 10.x</li>
                </ul>

                <h4>How It Works</h4>
                <ol>
                    <li>You configure your HA URL and Long-Lived Access Token</li>
                    <li>The plugin fetches <code>/api/states</code> to discover all entities and <code>/api/services</code> to discover all available actions</li>
                    <li>For each action, a command definition is written to <code>commands/descriptions.json</code></li>
                    <li>FPP reads these definitions and exposes them in the playlist editor with entity dropdowns</li>
                    <li>When a playlist runs a command, <code>call_action.php</code> makes an authenticated POST to the HA REST API</li>
                </ol>

                <h4>Frame-Accurate Sequence Commands</h4>
                <p>
                    In addition to playlist-level commands, you can trigger HA actions at exact frames
                    during a sequence using xLights' <b>FPP Commands</b> timing track combined with
                    FPP <b>Command Presets</b>. See the <a href="plugin.php?plugin=fpp-haCommands&page=help.php">Help page</a> for details.
                </p>

                <h4>Links</h4>
                <p>
                    <a href="https://github.com/jessica12ryan/fpp-haCommands" target="_blank">GitHub Repository</a><br>
                    <a href="https://github.com/jessica12ryan/fpp-haCommands/issues" target="_blank">Issue Tracker &amp; Feature Requests</a><br>
                    <a href="https://github.com/jessica12ryan/fpp-haCommands/blob/main/README.md" target="_blank">README &amp; Installation Guide</a>
                </p>

                <h4>Plugin Info</h4>
                <p>
                    Name: <b>Home Assistant Commands Plugin for FPP</b><br>
                    Author: <b>jessica12ryan</b><br>
                    License: <b>MIT</b><br>
                </p>

                <p class="text-secondary small">
                    FPP is &copy; Falcon Christmas. Home Assistant is &copy; Nabu Casa. This plugin is an independent
                    integration and is not affiliated with or endorsed by either project.
                </p>
            </div>
        </div>
    </fieldset>
</div>
