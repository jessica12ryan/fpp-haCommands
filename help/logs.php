<h3>Logs &mdash; Plugin Log Viewer</h3>
<p>View the plugin&rsquo;s log file to debug connection issues and command execution. This tab only appears when FPP <b>UI Level</b> is set to at least <b>Advanced (1)</b>.</p>

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
<p>Click <b>&#8635; Refresh</b> to re-fetch the last 50 lines via <code>api/plugin/fpp-haCommands/logs</code>. The table is limited to 50 entries and shown newest-first. Hover highlights rows.</p>

<h4>How to Use</h4>
<ul>
    <li>After a playlist command fails, check here for <span style="color:#dc3545">ERROR</span> entries.</li>
    <li>After <b>Test Connection</b> or <b>Update Entities</b>, check for success/failure messages.</li>
    <li>If log is empty, the plugin may not have run yet &mdash; trigger a command to generate entries.</li>
</ul>
