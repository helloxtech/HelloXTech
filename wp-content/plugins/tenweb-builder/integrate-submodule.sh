#!/bin/bash

# Script to integrate website-builder-package submodule into main plugin
# This script should be called during the CI/CD build process

echo "Starting submodule integration..."

# Ensure submodule is up to date
git submodule update --init --recursive --remote

# Define paths
SUBMODULE_PATH="website-builder-package"
MAIN_PLUGIN_PATH="."

# Copy essential files from submodule to main plugin
if [ -d "$SUBMODULE_PATH" ]; then
    echo "Integrating submodule content..."
    
    # Copy widgets (always copy latest)
    if [ -d "$SUBMODULE_PATH/widgets" ]; then
        rm -rf "$MAIN_PLUGIN_PATH/widgets" 2>/dev/null || true
        cp -r "$SUBMODULE_PATH/widgets" "$MAIN_PLUGIN_PATH/"
        echo "Copied widgets from submodule"
    fi
    
    # Copy assets (always copy latest)
    if [ -d "$SUBMODULE_PATH/assets" ]; then
        rm -rf "$MAIN_PLUGIN_PATH/assets" 2>/dev/null || true
        cp -r "$SUBMODULE_PATH/assets" "$MAIN_PLUGIN_PATH/"
        echo "Copied assets from submodule"
    fi
    
    # Copy Apps (always copy latest)
    if [ -d "$SUBMODULE_PATH/Apps" ]; then
        rm -rf "$MAIN_PLUGIN_PATH/Apps" 2>/dev/null || true
        cp -r "$SUBMODULE_PATH/Apps" "$MAIN_PLUGIN_PATH/"
        echo "Copied Apps from submodule"
    fi
    
    # Copy Modules (always copy latest)
    if [ -d "$SUBMODULE_PATH/Modules" ]; then
        rm -rf "$MAIN_PLUGIN_PATH/Modules" 2>/dev/null || true
        cp -r "$SUBMODULE_PATH/Modules" "$MAIN_PLUGIN_PATH/"
        echo "Copied Modules from submodule"
    fi
    
    # Copy languages folder with exclusions
    if [ -d "$SUBMODULE_PATH/languages" ]; then
        rm -rf "$MAIN_PLUGIN_PATH/languages" 2>/dev/null || true
        cp -r "$SUBMODULE_PATH/languages" "$MAIN_PLUGIN_PATH/"
        # Remove all ai-website-builder files
        find "$MAIN_PLUGIN_PATH/languages" -name "ai-website-builder*" -type f -exec rm -f {} + 2>/dev/null || true
        echo "Copied languages from submodule (excluding all ai-website-builder files)"
    fi

    # Copy other essential directories (excluding languages)
    for dir in "classes" "controls" "dynamic-tags" "library" "templates" "pro-features"; do
        if [ -d "$SUBMODULE_PATH/$dir" ]; then
            rm -rf "$MAIN_PLUGIN_PATH/$dir" 2>/dev/null || true
            cp -r "$SUBMODULE_PATH/$dir" "$MAIN_PLUGIN_PATH/"
            echo "Copied $dir from submodule"
        fi
    done
    
    # Copy important PHP files
    for file in "builder.php" "widgets-list.php" "gulpfile.js" "common-main.php"; do
        if [ -f "$SUBMODULE_PATH/$file" ]; then
            cp "$SUBMODULE_PATH/$file" "$MAIN_PLUGIN_PATH/"
            echo "Copied $file from submodule"
        fi
    done
    
    echo "Submodule integration completed successfully"
else
    echo "Warning: Submodule directory not found"
fi
