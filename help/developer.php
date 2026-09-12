<h3>Developer &mdash; Advanced Tools</h3>
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
