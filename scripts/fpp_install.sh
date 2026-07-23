#!/bin/bash

#############################################################
## Home Assistant Commands Plugin for FPP (fpp-haCommands) ##
## Author: jessica12ryan                                   ##
## URL: https://github.com/jessica12ryan/fpp-haCommands    ##
#############################################################
## Install/Update Script                                   ##
#############################################################

# --- Self-update bootstrap ---
PLUGIN_DIR="/home/fpp/media/plugins/fpp-haCommands"
if [ ! -d "$PLUGIN_DIR" ] && [ -n "$MEDIADIR" ]; then
    PLUGIN_DIR="${MEDIADIR}/plugins/fpp-haCommands"
fi

# Save user config before git operations may reset them
if [ -f "${PLUGIN_DIR}/config/ha_settings.json" ]; then
    cp "${PLUGIN_DIR}/config/ha_settings.json" "/tmp/ha_settings_backup.json" 2>/dev/null || true
fi
if [ -f "${PLUGIN_DIR}/config/plugin.fpp-haCommands" ]; then
    cp "${PLUGIN_DIR}/config/plugin.fpp-haCommands" "/tmp/plugin_fpp_haCommands_backup" 2>/dev/null || true
fi
if [ -f "${PLUGIN_DIR}/config/entities_cache.json" ]; then
    cp "${PLUGIN_DIR}/config/entities_cache.json" "/tmp/entities_cache_backup.json" 2>/dev/null || true
fi
if [ -f "${PLUGIN_DIR}/commands/descriptions.json" ]; then
    cp "${PLUGIN_DIR}/commands/descriptions.json" "/tmp/descriptions_backup.json" 2>/dev/null || true
fi

if [ -d "${PLUGIN_DIR}/.git" ]; then
    git -C "$PLUGIN_DIR" fetch origin 2>/dev/null || true

    LOCAL=$(git -C "$PLUGIN_DIR" rev-parse HEAD 2>/dev/null)
    REMOTE=$(git -C "$PLUGIN_DIR" rev-parse origin/main 2>/dev/null)

    if [ -n "$LOCAL" ] && [ -n "$REMOTE" ] && [ "$LOCAL" != "$REMOTE" ]; then
        echo "fpp-haCommands: Self-updating from GitHub..."
        git -C "$PLUGIN_DIR" checkout -- . 2>/dev/null || true
        git -C "$PLUGIN_DIR" clean -fd 2>/dev/null || true
        git -C "$PLUGIN_DIR" reset --hard origin/main 2>/dev/null || true

        # Re-execute the updated version of this script
        exec "${PLUGIN_DIR}/scripts/fpp_install.sh" "$@"
    fi
fi
# --- End self-update bootstrap ---

# Restore user config that was saved before git operations
if [ -f "/tmp/ha_settings_backup.json" ]; then
    mkdir -p "${PLUGIN_DIR}/config" 2>/dev/null || true
    mv "/tmp/ha_settings_backup.json" "${PLUGIN_DIR}/config/ha_settings.json" 2>/dev/null || true
fi
if [ -f "/tmp/plugin_fpp_haCommands_backup" ]; then
    mkdir -p "${PLUGIN_DIR}/config" 2>/dev/null || true
    mv "/tmp/plugin_fpp_haCommands_backup" "${PLUGIN_DIR}/config/plugin.fpp-haCommands" 2>/dev/null || true
fi
if [ -f "/tmp/entities_cache_backup.json" ]; then
    mkdir -p "${PLUGIN_DIR}/config" 2>/dev/null || true
    mv "/tmp/entities_cache_backup.json" "${PLUGIN_DIR}/config/entities_cache.json" 2>/dev/null || true
fi
if [ -f "/tmp/descriptions_backup.json" ]; then
    mkdir -p "${PLUGIN_DIR}/commands" 2>/dev/null || true
    mv "/tmp/descriptions_backup.json" "${PLUGIN_DIR}/commands/descriptions.json" 2>/dev/null || true
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

# Re-apply execute bit on command scripts (find/chmod 664 above strips it)
if [ -d "${PLUGIN_DIR}/commands" ]; then
    chmod +x ${PLUGIN_DIR}/commands/*.php 2>/dev/null || true
fi

echo "fpp-haCommands: Plugin installed successfully."
echo "fpp-haCommands: Go to Content Setup -> HA Commands to configure your Home Assistant connection."
