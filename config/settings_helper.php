<?php
/**
 * #############################################################
 * ## Home Assistant Commands Plugin for FPP (fpp-haCommands) ##
 * ## Author: jessica12ryan                                   ##
 * ## URL: https://github.com/jessica12ryan/fpp-haCommands    ##
 * #############################################################
 * ## config/settings_helper.php                              ##
 * ## Shared persistent-settings helper.                      ##
 * ##                                                         ##
 * ## Settings (HA URL + token) and generated caches used to  ##
 * ## live ONLY inside the plugin directory, which FPP wipes  ##
 * ## on fresh clone / "reinstall all plugins" / OS upgrade.  ##
 * ## Canonical copies now live in FPP's persistent config dir ##
 * ## (<mediadir>/config, e.g. /home/fpp/media/config) which   ##
 * ## survives plugin reinstalls and is included in FPP        ##
 * ## backups -- same approach as Remote Falcon                ##
 * ## ($settings['configDirectory'] . "/plugin.<name>").      ##
 * ##                                                         ##
 * ## All readers check canonical first, then legacy runtime   ##
 * ## paths, and auto-migrate legacy -> canonical. All writers ##
 * ## write BOTH canonical and runtime so FPP command         ##
 * ## discovery (which reads commands/descriptions.json in the ##
 * ## plugin dir) keeps working immediately after reinstall.   ##
 * #############################################################
 */

if (!defined('HAC_HELPER_LOADED')) {
    define('HAC_HELPER_LOADED', true);

    if (!function_exists('hacPluginDir')) {
        function hacPluginDir($pluginDir = null) {
            if (!empty($pluginDir)) {
                return rtrim($pluginDir, '/');
            }
            // Helper lives in <plugin>/config/, so parent is plugin root.
            $dir = dirname(__FILE__);
            if (basename($dir) === 'config') {
                return dirname($dir);
            }
            return $dir;
        }
    }

    if (!function_exists('hacConfigDir')) {
        function hacConfigDir() {
            // 1) FPP web UI injects $settings['configDirectory'].
            if (isset($GLOBALS['settings']) && is_array($GLOBALS['settings']) && !empty($GLOBALS['settings']['configDirectory'])) {
                return rtrim($GLOBALS['settings']['configDirectory'], '/');
            }
            // 2) FPP exports MEDIADIR to processes it launches.
            $media = getenv('MEDIADIR');
            if (!empty($media)) {
                return rtrim($media, '/') . '/config';
            }
            // 3) Default install path.
            return '/home/fpp/media/config';
        }
    }

    if (!function_exists('hacCanonicalSettingsFile')) {
        function hacCanonicalSettingsFile() {
            return hacConfigDir() . '/plugin.fpp-haCommands';
        }
    }

    if (!function_exists('hacCanonicalCacheFile')) {
        function hacCanonicalCacheFile() {
            return hacConfigDir() . '/plugin.fpp-haCommands.entities.json';
        }
    }

    if (!function_exists('hacCanonicalCacheFileLegacy')) {
        // Previous canonical name used briefly before the rename to
        // .entities.json. Kept for migration only -- reads fall back to it
        // and promote it to the new name; writes/clears remove it.
        function hacCanonicalCacheFileLegacy() {
            return hacConfigDir() . '/plugin.fpp-haCommands.cache.json';
        }
    }

    if (!function_exists('hacCanonicalDescriptionsFile')) {
        function hacCanonicalDescriptionsFile() {
            return hacConfigDir() . '/plugin.fpp-haCommands.descriptions.json';
        }
    }

    if (!function_exists('hacRuntimeSettingsFile')) {
        function hacRuntimeSettingsFile($pluginDir = null) {
            return hacPluginDir($pluginDir) . '/config/plugin.fpp-haCommands';
        }
    }

    if (!function_exists('hacRuntimeLegacyJsonFile')) {
        function hacRuntimeLegacyJsonFile($pluginDir = null) {
            return hacPluginDir($pluginDir) . '/config/ha_settings.json';
        }
    }

    if (!function_exists('hacRuntimeCacheFile')) {
        function hacRuntimeCacheFile($pluginDir = null) {
            return hacPluginDir($pluginDir) . '/config/entities_cache.json';
        }
    }

    if (!function_exists('hacRuntimeDescriptionsFile')) {
        function hacRuntimeDescriptionsFile($pluginDir = null) {
            return hacPluginDir($pluginDir) . '/commands/descriptions.json';
        }
    }

    if (!function_exists('hacMaybeUrlDecode')) {
        function hacMaybeUrlDecode($v) {
            if (!is_string($v) || $v === '' || strpos($v, '%') === false) {
                return is_string($v) ? $v : '';
            }
            $d = @urldecode($v);
            // Only accept decode when it actually changes the value
            // (supports files written with WriteSettingToFile urlencode).
            return ($d !== false && $d !== $v) ? $d : $v;
        }
    }

    if (!function_exists('hacParseIniSettings')) {
        function hacParseIniSettings($path) {
            if (!is_string($path) || $path === '' || !@file_exists($path)) {
                return null;
            }
            $s = @parse_ini_file($path);
            if (!is_array($s)) {
                return null;
            }
            return [
                'ha_url' => hacMaybeUrlDecode(trim($s['ha_url'] ?? '')),
                'ha_token' => hacMaybeUrlDecode($s['ha_token'] ?? ''),
            ];
        }
    }

    if (!function_exists('hacParseJsonSettings')) {
        function hacParseJsonSettings($path) {
            if (!is_string($path) || $path === '' || !@file_exists($path)) {
                return null;
            }
            $raw = @file_get_contents($path);
            if ($raw === false) {
                return null;
            }
            $s = @json_decode($raw, true);
            if (!is_array($s)) {
                return null;
            }
            return [
                'ha_url' => hacMaybeUrlDecode(trim($s['ha_url'] ?? '')),
                'ha_token' => hacMaybeUrlDecode($s['ha_token'] ?? ''),
            ];
        }
    }

    if (!function_exists('hacCopyIfNeeded')) {
        // Copy $src -> $dst when $dst is missing and $src exists.
        // Returns true when $dst exists afterwards, false otherwise.
        function hacCopyIfNeeded($src, $dst) {
            if (!is_string($src) || !is_string($dst) || $src === '' || $dst === '') {
                return false;
            }
            if (@file_exists($dst)) {
                return true;
            }
            if (!@file_exists($src)) {
                return false;
            }
            $destDir = dirname($dst);
            if (!@is_dir($destDir)) {
                @mkdir($destDir, 0775, true);
            }
            if (@copy($src, $dst)) {
                return true;
            }
            return @file_exists($dst);
        }
    }

    if (!function_exists('hacGetSettings')) {
        // Canonical first, then legacy runtime paths. Migrates
        // legacy -> canonical (best effort, never fatal).
        function hacGetSettings($pluginDir = null) {
            $canonical = hacCanonicalSettingsFile();
            $runtime = hacRuntimeSettingsFile($pluginDir);
            $legacyJson = hacRuntimeLegacyJsonFile($pluginDir);

            $s = hacParseIniSettings($canonical);
            if (is_array($s)) {
                // Opportunistically restore runtime copy so CLI scripts
                // and FPP command discovery keep working after a fresh
                // clone where only the persistent copy survived.
                hacCopyIfNeeded($canonical, $runtime);
                return $s;
            }

            $s = hacParseIniSettings($runtime);
            if (is_array($s)) {
                // Migrate forward to persistent storage.
                hacCopyIfNeeded($runtime, $canonical);
                return $s;
            }

            $s = hacParseJsonSettings($legacyJson);
            if (is_array($s)) {
                hacCopyIfNeeded($legacyJson, $canonical . '.legacy-migrated.json');
                // Write migrated INI copies (best effort).
                if (!empty($s['ha_url']) || !empty($s['ha_token'])) {
                    hacSaveSettings($s['ha_url'], $s['ha_token'], $pluginDir);
                    // Re-read through canonical path for consistency.
                    $migrated = hacParseIniSettings($canonical);
                    if (is_array($migrated)) {
                        return $migrated;
                    }
                }
                return $s;
            }

            return ['ha_url' => '', 'ha_token' => ''];
        }
    }

    if (!function_exists('hacWriteIniFile')) {
        // Atomic-ish write: temp file + rename. Returns bool.
        function hacWriteIniFile($path, $haUrl, $haToken) {
            if (!is_string($path) || $path === '') {
                return false;
            }
            $dir = dirname($path);
            if (!@is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            $ini = "ha_url=" . rtrim(trim($haUrl), '/') . "\nha_token=" . $haToken . "\n";
            $tmp = $path . '.tmp.' . getmypid();
            $ok = @file_put_contents($tmp, $ini, LOCK_EX);
            if ($ok === false) {
                return false;
            }
            if (!@rename($tmp, $path)) {
                @unlink($tmp);
                return false;
            }
            return true;
        }
    }

    if (!function_exists('hacSaveSettings')) {
        // Writes BOTH canonical (persistent) and runtime copies.
        // Removes legacy JSON copies. Never throws.
        function hacSaveSettings($haUrl, $haToken, $pluginDir = null) {
            $haUrl = rtrim(trim((string)$haUrl), '/');
            $haToken = (string)$haToken;
            $canonical = hacCanonicalSettingsFile();
            $runtime = hacRuntimeSettingsFile($pluginDir);
            $legacyJson = hacRuntimeLegacyJsonFile($pluginDir);

            $okCanon = hacWriteIniFile($canonical, $haUrl, $haToken);
            $okRun = hacWriteIniFile($runtime, $haUrl, $haToken);

            // Best-effort restrictive perms on files holding the token.
            // Owned by root after install-as-root would lock out the fpp
            // web user, so only tighten when we can chown to fpp first;
            // otherwise leave the inherited (664-style) perms to avoid
            // breaking the web UI's ability to save.
            foreach ([$canonical, $runtime] as $f) {
                if (@file_exists($f)) {
                    if (@chown($f, 'fpp') && @chgrp($f, 'fpp')) {
                        @chmod($f, 0600);
                    }
                }
            }

            @unlink($legacyJson);

            return ($okCanon || $okRun);
        }
    }

    if (!function_exists('hacGetCacheFileForRead')) {
        // Returns a readable cache path, restoring runtime from
        // canonical when needed (fresh clone case). Empty string if none.
        // Supports the renamed canonical file: new .entities.json first,
        // then legacy .cache.json (auto-promoted to the new name).
        function hacGetCacheFileForRead($pluginDir = null) {
            $runtime = hacRuntimeCacheFile($pluginDir);
            if (@file_exists($runtime)) {
                return $runtime;
            }
            $canonical = hacCanonicalCacheFile();
            if (@file_exists($canonical)) {
                hacCopyIfNeeded($canonical, $runtime);
                if (@file_exists($runtime)) {
                    return $runtime;
                }
                return $canonical;
            }
            $legacy = hacCanonicalCacheFileLegacy();
            if (@file_exists($legacy)) {
                // Promote legacy canonical name to the new one (best effort).
                hacCopyIfNeeded($legacy, $canonical);
                hacCopyIfNeeded($legacy, $runtime);
                if (@file_exists($runtime)) {
                    return $runtime;
                }
                if (@file_exists($canonical)) {
                    return $canonical;
                }
                return $legacy;
            }
            return '';
        }
    }

    if (!function_exists('hacGetDescriptionsFileForRead')) {
        function hacGetDescriptionsFileForRead($pluginDir = null) {
            $runtime = hacRuntimeDescriptionsFile($pluginDir);
            if (@file_exists($runtime)) {
                return $runtime;
            }
            $canonical = hacCanonicalDescriptionsFile();
            if (@file_exists($canonical)) {
                hacCopyIfNeeded($canonical, $runtime);
                if (@file_exists($runtime)) {
                    return $runtime;
                }
                return $canonical;
            }
            return '';
        }
    }

    if (!function_exists('hacWriteCache')) {
        // Persists entity cache to BOTH runtime and canonical (new name).
        // Removes the legacy canonical name to avoid stale divergence.
        function hacWriteCache($jsonString, $pluginDir = null) {
            $runtime = hacRuntimeCacheFile($pluginDir);
            $canonical = hacCanonicalCacheFile();
            $legacy = hacCanonicalCacheFileLegacy();
            foreach ([$runtime, $canonical] as $f) {
                $d = dirname($f);
                if (!@is_dir($d)) {
                    @mkdir($d, 0775, true);
                }
            }
            $ok1 = (@file_put_contents($runtime, $jsonString, LOCK_EX) !== false);
            $ok2 = (@file_put_contents($canonical, $jsonString, LOCK_EX) !== false);
            @unlink($legacy);
            return ($ok1 || $ok2);
        }
    }

    if (!function_exists('hacWriteDescriptions')) {
        function hacWriteDescriptions($jsonString, $pluginDir = null) {
            $runtime = hacRuntimeDescriptionsFile($pluginDir);
            $canonical = hacCanonicalDescriptionsFile();
            foreach ([$runtime, $canonical] as $f) {
                $d = dirname($f);
                if (!@is_dir($d)) {
                    @mkdir($d, 0775, true);
                }
            }
            $ok1 = (@file_put_contents($runtime, $jsonString, LOCK_EX) !== false);
            $ok2 = (@file_put_contents($canonical, $jsonString, LOCK_EX) !== false);
            return ($ok1 || $ok2);
        }
    }

    if (!function_exists('hacClearCacheFiles')) {
        // Deletes cache + descriptions in ALL locations (runtime, new
        // canonical, and legacy canonical name). Returns names cleared.
        function hacClearCacheFiles($pluginDir = null) {
            $cleared = [];
            $map = [
                'entity_cache' => [hacRuntimeCacheFile($pluginDir), hacCanonicalCacheFile(), hacCanonicalCacheFileLegacy()],
                'command_descriptions' => [hacRuntimeDescriptionsFile($pluginDir), hacCanonicalDescriptionsFile()],
            ];
            foreach ($map as $name => $paths) {
                $did = false;
                foreach ($paths as $p) {
                    if (@file_exists($p) && @unlink($p)) {
                        $did = true;
                    }
                }
                if ($did) {
                    $cleared[] = $name;
                }
            }
            return $cleared;
        }
    }

    if (!function_exists('hacClearSettingsFiles')) {
        // Clears HA URL/token in ALL locations (writes empty INI so the
        // file still exists, matching previous reset behavior), and
        // removes legacy JSON. Returns names cleared.
        function hacClearSettingsFiles($pluginDir = null) {
            $cleared = [];
            $canonical = hacCanonicalSettingsFile();
            $runtime = hacRuntimeSettingsFile($pluginDir);
            $legacyJson = hacRuntimeLegacyJsonFile($pluginDir);

            $didSettings = false;
            foreach ([$canonical, $runtime] as $f) {
                $d = dirname($f);
                if (!@is_dir($d)) {
                    @mkdir($d, 0775, true);
                }
                if (@file_put_contents($f, "ha_url=\nha_token=\n", LOCK_EX) !== false) {
                    $didSettings = true;
                } else {
                    // Best effort: blank any existing values in place.
                    $s = hacParseIniSettings($f);
                    if (is_array($s) && ($s['ha_url'] !== '' || $s['ha_token'] !== '')) {
                        @unlink($f);
                    }
                }
            }
            if ($didSettings) {
                $cleared[] = 'settings';
            }
            if (@file_exists($legacyJson) && @unlink($legacyJson)) {
                $cleared[] = 'legacy_settings';
            }
            // De-duplicate while preserving order.
            $cleared = array_values(array_unique($cleared));
            return $cleared;
        }
    }
}
