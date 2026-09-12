<h3>Config &mdash; Home Assistant Connection</h3>
<p>Configure the connection to Home Assistant and generate FPP commands from your HA entities.</p>

<h4>HA URL</h4>
<p>Full URL to your Home Assistant instance including protocol and port, e.g. <code>http://homeassistant.local:8123</code> or <code>http://192.168.1.100:8123</code>. Trailing slash is removed automatically. Stored in <code>config/plugin.fpp-haCommands</code>.</p>

<h4>Long-Lived Access Token</h4>
<p>Generate at <b>HA &rarr; Profile &rarr; Long-Lived Access Tokens</b> (<code>http://HA:8123/profile</code>). Paste here; it is stored alongside the URL and never in browser storage. Field is masked as a password.</p>

<h4>Buttons</h4>
<ul>
    <li><b>Save Settings</b> &mdash; Writes HA URL and token to disk via <code>POST</code> to this page (<code>action=save_settings</code>). Do this before testing or updating.</li>
    <li><b>Test Connection</b> &mdash; Calls <code>api/plugin/fpp-haCommands/test</code> which does <code>GET /api/</code> with your token. Shows HA version on success or a friendly error (401 = bad token, 404 = wrong URL, timeout = unreachable) via <code>$.jGrowl</code>.</li>
    <li><b>Update Entities</b> &mdash; Fetches <code>/api/states</code> and <code>/api/services</code>, builds <code>config/entities_cache.json</code> and <code>commands/descriptions.json</code>, then sets the FPP restart flag so FPPD reloads commands. Shows a summary like &ldquo;42 entities across 8 domains, 15 commands generated&rdquo;. Requires confirmation and disables the button while running.</li>
</ul>

<h4>Status Indicators</h4>
<ul>
    <li><b>Token configured: Yes/No</b> &mdash; Whether a token is saved on disk.</li>
    <li><b>Commands generated</b> &mdash; Number of command definitions currently in <code>commands/descriptions.json</code>.</li>
    <li><b>Cached entities</b> &mdash; Entities from last update (<code>entities_cache.json</code>).</li>
</ul>

<h4>xLights Setup</h4>
<p>Optional section for frame-accurate sequence triggers. Shows count of presets using <code>HA - </code> commands (scanned from <code>config/commandPresets.json</code>) and a button to <b>Set Command Presets</b> (<code>commandPresets.php</code>). Create a preset per HA action (e.g. &ldquo;Porch Lights On&rdquo; &rarr; <code>HA - light.turn_on</code> + <code>light.porch</code>), then reference the preset name in xLights&rsquo; <b>FPP Commands</b> timing track.</p>

<h4>Important Notes</h4>
<ul>
    <li>After <b>Update Entities</b>, FPPD prompts for restart; refresh the page after restart for commands to appear in the playlist editor.</li>
    <li>Commands appear in playlists as <b>HA - domain.service</b> with an entity dropdown.</li>
    <li>Re-run <b>Update Entities</b> whenever you add/remove HA entities in Home Assistant.</li>
</ul>
