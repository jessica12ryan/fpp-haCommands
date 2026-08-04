<?php
/**
 * #############################################################
 * ## Home Assistant Commands Plugin for FPP (fpp-haCommands) ##
 * ## Author: jessica12ryan                                   ##
 * ## URL: https://github.com/jessica12ryan/fpp-haCommands    ##
 * #############################################################
 * ## tabs.inc                                                ##
 * ## Shared tab bar rendered by all plugin pages.            ##
 * ## Requires $showLogsTab and $showDevTab to be set first.  ##
 * #############################################################
 */

function hac_tab_active($page)
{
	$current = $_GET['page'] ?? basename(debug_backtrace()[0]['file']);
	return $current === $page ? 'active' : '';
}

$tabs = Array(
	Array('page' => 'status.php',    'icon' => '&#9632;', 'label' => 'Status'),
	Array('page' => 'config.php',    'icon' => '&#9881;', 'label' => 'Config'),
	Array('page' => 'logs.php',      'icon' => '&#9776;', 'label' => 'Logs',   'show' => $showLogsTab),
	Array('page' => 'help.php',      'icon' => '&#63;',   'label' => 'Help'),
	Array('page' => 'about.php',     'icon' => '&#9432;', 'label' => 'About'),
	Array('page' => 'developer.php', 'icon' => '&#9881;', 'label' => 'Developer', 'show' => $showDevTab)
);
?>

<div class="tab-bar">
<?php foreach ($tabs as $tab): ?>
<?php if (isset($tab['show']) && !$tab['show']) continue; ?>
	<a href="plugin.php?plugin=fpp-haCommands&page=<?php echo $tab['page']; ?>" class="<?php echo hac_tab_active($tab['page']); ?>"><?php echo $tab['icon']; ?> <?php echo $tab['label']; ?></a>
<?php endforeach; ?>
</div>