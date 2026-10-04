#!/bin/bash
set -euo pipefail

#############################################################
## Home Assistant Commands Plugin for FPP (fpp-haCommands) ##
## Author: jessica12ryan                                   ##
## URL: https://github.com/jessica12ryan/fpp-haCommands    ##
#############################################################
## Install/Update Script                                   ##
##                                                         ##
## HA URL/token and caches now persist in FPP's config dir ##
## (/home/fpp/media/config) which survives plugin          ##
## reinstalls and OS upgrades -- same approach as Remote   ##
## Falcon. The plugin dir itself is wiped on fresh clone.  ##
#############################################################

# Resolve FPP dirs the supported way when available.
: "${FPPDIR:=/opt/fpp}"
if [ -f "${FPPDIR}/scripts/common" ]; then
    # shellcheck disable=SC1091
    . "${FPPDIR}/scripts/common" || true
fi
: "${MEDIADIR:=/home/fpp/media}"

PLUGIN_DIR="/home/fpp/media/plugins/fpp-haCommands"
if [ ! -d "$PLUGIN_DIR" ] && [ -n "${MEDIADIR:-}" ]; then
    PLUGIN_DIR="${MEDIADIR}/plugins/fpp-haCommands"
fi
CONFIG_DIR="${MEDIADIR}/config"

CANON_SETTINGS="${CONFIG_DIR}/plugin.fpp-haCommands"
CANON_CACHE="${CONFIG_DIR}/plugin.fpp-haCommands.entities.json"
CANON_CACHE_LEGACY="${CONFIG_DIR}/plugin.fpp-haCommands.cache.json"
CANON_DESC="${CONFIG_DIR}/plugin.fpp-haCommands.descriptions.json"

RT_SETTINGS="${PLUGIN_DIR}/config/plugin.fpp-haCommands"
RT_LEGACY_JSON="${PLUGIN_DIR}/config/ha_settings.json"
RT_CACHE="${PLUGIN_DIR}/config/entities_cache.json"
RT_DESC="${PLUGIN_DIR}/commands/descriptions.json"

mkdir -p "${CONFIG_DIR}" "${PLUGIN_DIR}/config" "${PLUGIN_DIR}/commands" 2>/dev/null || true

# --- Migrate legacy runtime -> persistent (upgrade from old versions) ---
if [ ! -f "$CANON_SETTINGS" ] && [ -f "$RT_SETTINGS" ]; then
    cp "$RT_SETTINGS" "$CANON_SETTINGS" 2>/dev/null || true
fi
# Rename: old canonical .cache.json -> new .entities.json (existing users
# who picked up the intermediate name keep their cache).
if [ ! -f "$CANON_CACHE" ] && [ -f "$CANON_CACHE_LEGACY" ]; then
    cp "$CANON_CACHE_LEGACY" "$CANON_CACHE" 2>/dev/null || true
fi
if [ ! -f "$CANON_CACHE" ] && [ -f "$RT_CACHE" ]; then
    cp "$RT_CACHE" "$CANON_CACHE" 2>/dev/null || true
fi
if [ ! -f "$CANON_DESC" ] && [ -f "$RT_DESC" ]; then
    cp "$RT_DESC" "$CANON_DESC" 2>/dev/null || true
fi
# Legacy JSON had the same credentials under a different name.
if [ ! -f "$CANON_SETTINGS" ] && [ -f "$RT_LEGACY_JSON" ]; then
    if command -v php >/dev/null 2>&1; then
        HA_URL=$(php -r '$s=@json_decode(@file_get_contents($argv[1]),true); echo rtrim(trim($s["ha_url"]??""),"/");' "$RT_LEGACY_JSON" 2>/dev/null || true)
        HA_TOKEN=$(php -r '$s=@json_decode(@file_get_contents($argv[1]),true); echo $s["ha_token"]??"";' "$RT_LEGACY_JSON" 2>/dev/null || true)
        if [ -n "${HA_URL:-}" ] || [ -n "${HA_TOKEN:-}" ]; then
            printf 'ha_url=%s\nha_token=%s\n' "$HA_URL" "$HA_TOKEN" > "$CANON_SETTINGS" 2>/dev/null || true
        fi
    fi
fi

# --- Self-update bootstrap (in-place git update) ---
if [ -d "${PLUGIN_DIR}/.git" ]; then
    git -C "$PLUGIN_DIR" fetch origin 2>/dev/null || true

    LOCAL=$(git -C "$PLUGIN_DIR" rev-parse HEAD 2>/dev/null || true)
    REMOTE=$(git -C "$PLUGIN_DIR" rev-parse origin/main 2>/dev/null || true)

    if [ -n "${LOCAL:-}" ] && [ -n "${REMOTE:-}" ] && [ "$LOCAL" != "$REMOTE" ]; then
        echo "fpp-haCommands: Self-updating from GitHub..."
        # Persistent copies in $CONFIG_DIR are untouched by these operations.
        git -C "$PLUGIN_DIR" checkout -- . 2>/dev/null || true
        git -C "$PLUGIN_DIR" clean -fd 2>/dev/null || true
        git -C "$PLUGIN_DIR" reset --hard origin/main 2>/dev/null || true

        # Re-execute the updated version of this script
        exec "${PLUGIN_DIR}/scripts/fpp_install.sh" "$@"
    fi
fi
# --- End self-update bootstrap ---

# --- Restore runtime from persistent (fresh clone / reinstall case) ---
# FPP restores /home/fpp/media/config from backup on OS upgrade, so the
# canonical copies survive while the plugin dir is a fresh clone.
if [ -f "$CANON_SETTINGS" ] && [ ! -f "$RT_SETTINGS" ]; then
    cp "$CANON_SETTINGS" "$RT_SETTINGS" 2>/dev/null || true
fi
if [ -f "$CANON_CACHE" ] && [ ! -f "$RT_CACHE" ]; then
    cp "$CANON_CACHE" "$RT_CACHE" 2>/dev/null || true
fi
# Drop the old canonical name once the new one exists (avoids stale divergence).
if [ -f "$CANON_CACHE" ] && [ -f "$CANON_CACHE_LEGACY" ]; then
    rm -f "$CANON_CACHE_LEGACY" 2>/dev/null || true
fi
if [ -f "$CANON_DESC" ] && [ ! -f "$RT_DESC" ]; then
    cp "$CANON_DESC" "$RT_DESC" 2>/dev/null || true
fi

# Ensure config directory exists
mkdir -p "${PLUGIN_DIR}/config" 2>/dev/null || true

# Fix permissions so the web server can write config and command files
# (must run AFTER all files are created, so globs match)
if chown -R fpp:fpp "${PLUGIN_DIR}/config" "${PLUGIN_DIR}/commands" 2>/dev/null || chown -R :fpp "${PLUGIN_DIR}/config" "${PLUGIN_DIR}/commands" 2>/dev/null; then
    chmod 775 "${PLUGIN_DIR}/config" "${PLUGIN_DIR}/commands" 2>/dev/null || true
    find "${PLUGIN_DIR}/config" "${PLUGIN_DIR}/commands" -type f -exec chmod 664 {} + 2>/dev/null || true
else
    chmod 775 "${PLUGIN_DIR}/config" "${PLUGIN_DIR}/commands" 2>/dev/null || true
    find "${PLUGIN_DIR}/config" "${PLUGIN_DIR}/commands" -type f -exec chmod 664 {} + 2>/dev/null || true
fi

# Tighten token files when ownership allows; otherwise keep group-readable
# so the web UI (fpp user) can still save settings (no breaking change).
for f in "$CANON_SETTINGS" "$RT_SETTINGS"; do
    if [ -f "$f" ]; then
        if chown fpp:fpp "$f" 2>/dev/null; then
            chmod 600 "$f" 2>/dev/null || true
        fi
    fi
done
if [ -f "$CONFIG_DIR" ]; then
    # Persistent cache/descriptions stay readable by the web UI.
    chmod 775 "$CONFIG_DIR" 2>/dev/null || true
fi

# Re-apply execute bit on command scripts (find/chmod 664 above strips it)
if [ -d "${PLUGIN_DIR}/commands" ]; then
    chmod +x ${PLUGIN_DIR}/commands/*.php 2>/dev/null || true
fi

echo "fpp-haCommands: Plugin installed successfully."
echo "fpp-haCommands: Go to Content Setup -> HA Commands to configure your Home Assistant connection."
