<h3>Status &mdash; Overview</h3>
<p>This page gives you an at-a-glance overview of the plugin&rsquo;s current state.</p>

<h4>Connection</h4>
<ul>
    <li><b>&#9679; Configured</b> (green) &mdash; HA URL and token are saved. The URL is shown for verification.</li>
    <li><b>&#9679; Not configured</b> (red) &mdash; Go to <b>Config</b> to enter your HA URL and Long-Lived Access Token. A link to <code>plugin.php?plugin=fpp-haCommands&amp;page=config.php</code> is shown.</li>
</ul>

<h4>Cache &amp; Commands</h4>
<ul>
    <li><b>Cache updated</b> &mdash; Timestamp of the last successful <b>Update Entities</b> run (from <code>config/entities_cache.json</code>). Shows <code>Never</code> if never run.</li>
    <li><b>Entities cached</b> &mdash; Total entities discovered via <code>/api/states</code>.</li>
    <li><b>Domains discovered</b> &mdash; Unique HA domains (light, switch, cover, etc.) from <code>entities_by_domain</code>.</li>
    <li><b>Commands (with entities)</b> &mdash; Count of generated commands that have an entity dropdown, out of total commands from <code>commands/descriptions.json</code>.</li>
    <li><b>HA Command Presets</b> &mdash; Number of FPP Command Presets that use an <code>HA - </code> command (from <code>config/commandPresets.json</code>).</li>
</ul>

<h4>Generated Commands Table</h4>
<p>Lists every <code>HA - domain.service</code> command that has entities. Sorted alphabetically. Use this to confirm entities were discovered correctly after <b>Config &rarr; Update Entities</b>. Only commands with an entity dropdown are shown.</p>

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
