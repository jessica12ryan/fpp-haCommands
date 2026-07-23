<?php
/**
 * #############################################################
 * ## Home Assistant Commands Plugin for FPP (fpp-haCommands) ##
 * ## Author: jessica12ryan                                   ##
 * ## URL: https://github.com/jessica12ryan/fpp-haCommands    ##
 * #############################################################
 * ## status.php                                              ##
 * #############################################################
 */
$pluginDir = __DIR__;
$iniFile = $pluginDir . '/config/plugin.fpp-haCommands';
$oldJsonFile = $pluginDir . '/config/ha_settings.json';
$cacheFile = $pluginDir . '/config/entities_cache.json';
$descriptionsFile = $pluginDir . '/commands/descriptions.json';

$haUrl = '';
$haToken = '';
$hasSettings = false;
if (file_exists($iniFile)) {
    $s = parse_ini_file($iniFile);
    $haUrl = $s['ha_url'] ?? '';
    $haToken = $s['ha_token'] ?? '';
    $hasSettings = !empty($haUrl) && !empty($haToken);
} elseif (file_exists($oldJsonFile)) {
    $s = json_decode(file_get_contents($oldJsonFile), true);
    $haUrl = $s['ha_url'] ?? '';
    $haToken = $s['ha_token'] ?? '';
    $hasSettings = !empty($haUrl) && !empty($haToken);
}

$cacheStats = ['updated_at' => 'Never', 'entities' => 0, 'domains' => 0];
if (file_exists($cacheFile)) {
    $c = json_decode(file_get_contents($cacheFile), true);
    $cacheStats['updated_at'] = $c['updated_at'] ?? 'Never';
    $cacheStats['entities'] = count($c['entities'] ?? []);
    $cacheStats['domains'] = count($c['entities_by_domain'] ?? []);
}

$cmdCount = 0;
$entityCmdCount = 0;
if (file_exists($descriptionsFile)) {
    $cmds = json_decode(file_get_contents($descriptionsFile), true);
    $cmdCount = count($cmds ?? []);
    foreach ($cmds as $cmd) {
        foreach ($cmd['args'] as $arg) {
            if (($arg['name'] ?? '') === 'entity_id' && !empty($arg['contents'])) {
                $entityCmdCount++;
                break;
            }
        }
    }
}
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

<div style="margin:0 auto;">
    <fieldset class="border p-3">
        <legend>HA Commands Status</legend>
        <div class="p-3">
            <table>
                <tr>
                    <td style="padding: 4px;"><b>Connection:</b></td>
                    <td style="padding: 4px;">
                        <?php if ($hasSettings): ?>
                            <span class="text-success">&#9679; Configured</span>
                        <?php else: ?>
                            <span class="text-danger">&#9679; Not configured</span>
                            &nbsp;<a href="plugin.php?plugin=fpp-haCommands&page=config.php">Configure now</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ($hasSettings): ?>
                <tr>
                    <td style="padding: 4px;"><b>HA URL:</b></td>
                    <td style="padding: 4px;"><?php echo htmlspecialchars($haUrl); ?></td>
                </tr>
                <tr>
                    <td style="padding: 4px;"><b>Cache updated:</b></td>
                    <td style="padding: 4px;"><?php echo htmlspecialchars($cacheStats['updated_at']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 4px;"><b>Entities cached:</b></td>
                    <td style="padding: 4px;"><?php echo $cacheStats['entities']; ?></td>
                </tr>
                <tr>
                    <td style="padding: 4px;"><b>Domains discovered:</b></td>
                    <td style="padding: 4px;"><?php echo $cacheStats['domains']; ?></td>
                </tr>
                <tr>
                    <td style="padding: 4px;"><b>Commands (with entities):</b></td>
                    <td style="padding: 4px;"><?php echo $entityCmdCount; ?> / <?php echo $cmdCount; ?> total</td>
                </tr>
                <?php endif; ?>
            </table>

            <?php if ($cmdCount > 0): ?>
            <hr>
            <p><b>Generated Commands:</b></p>
            <div style="max-height: 300px; overflow-y: auto;">
                <table class="fppTable" style="width: auto;">
                    <thead>
                        <tr><th>Command Name</th><th>Entity Dropdown</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $cmds = json_decode(file_get_contents($descriptionsFile), true);
                        usort($cmds, function($a, $b) {
                            return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
                        });
                        foreach ($cmds as $cmd):
                            $name = htmlspecialchars($cmd['name']);
                            $hasEntityDropdown = false;
                            $entityDomain = '';
                            foreach ($cmd['args'] as $arg) {
                                if (($arg['name'] ?? '') === 'entity_id' && !empty($arg['contents'])) {
                                    $hasEntityDropdown = true;
                                    $entityDomain = count($arg['contents']) . ' entities';
                                }
                            }
                            if (!$hasEntityDropdown) continue;
                        ?>
                        <tr>
                            <td><?php echo $name; ?></td>
                            <td><?php echo $hasEntityDropdown ? htmlspecialchars($entityDomain) : '<span class="text-secondary">No entity</span>'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </fieldset>
</div>
