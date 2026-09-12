<h3>About &mdash; Plugin Information</h3>
<p>Bridges <b>Home Assistant</b> and <b>Falcon Player (FPP)</b>, turning every HA action into an FPP command you can use in playlists, scheduler, GPIO, and xLights sequences.</p>

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
