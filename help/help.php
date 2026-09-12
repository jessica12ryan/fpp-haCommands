<h3>Help &mdash; Usage Guide</h3>
<p>This page is the full <b>Help &amp; Usage Guide</b> for the Home Assistant Commands plugin. It also appears here via <b>F1</b> so the guide is available from every tab.</p>

<h4>What This Plugin Does</h4>
<p>Turns every Home Assistant action (<code>light.turn_on</code>, <code>switch.turn_off</code>, <code>scene.turn_on</code>, etc.) into an FPP command usable <b>between sequences</b> (playlists) or <b>inside a sequence</b> (xLights FPP Commands track at exact frames).</p>

<h4>Quick Start</h4>
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
