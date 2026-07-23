<?php
/**
 * #############################################################
 * ## Home Assistant Commands Plugin for FPP (fpp-haCommands) ##
 * ## Author: jessica12ryan                                   ##
 * ## URL: https://github.com/jessica12ryan/fpp-haCommands    ##
 * #############################################################
 * ## menu.inc                                                ##
 * #############################################################
 */

$hacIconUrl = 'api/plugin/fpp-haCommands/icon?v=' . filemtime(__DIR__ . '/icon.png');
$menuEntries = Array(
	Array(
		'text' => '<img src="' . $hacIconUrl . '" style="width:16px; height:16px; vertical-align:middle; margin-right:4px;"> HA Commands',
		'type' => 'content',
		'page' => 'config.php',
		'wrap' => 1
	),
	Array(
		'text' => '<img src="' . $hacIconUrl . '" style="width:16px; height:16px; vertical-align:middle; margin-right:4px;"> HA Commands',
		'type' => 'status',
		'page' => 'status.php',
		'wrap' => 1
	),
	Array(
		'text' => '<img src="' . $hacIconUrl . '" style="width:16px; height:16px; vertical-align:middle; margin-right:4px;"> HA Commands',
		'type' => 'help',
		'page' => 'about.php',
		'wrap' => 1
	)
);

foreach ($menuEntries as $entry)
{
	if ($entry['type'] != $menu)
		continue;

	if (preg_match('/^http.?:\/\//', $entry['page']))
	{
		printf("<li><a href='%s' target='_blank'>%s</a></li>\n",
			$entry['page'], $entry['text']);
	}
	else
	{
		$nopage = '';
		if (isset($entry['wrap']) && ($entry['wrap'] == 0))
			$nopage = '&nopage=1';

		printf("<li><a href='plugin.php?plugin=%s&page=%s%s'>%s</a></li>\n",
			$plugin, $entry['page'], $nopage, $entry['text']);
	}
}
?>
