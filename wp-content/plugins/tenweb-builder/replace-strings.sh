#!/bin/bash

# Script to replace 'ai-website-builder' with 'tenweb-builder' in PHP files
# Excludes vendor, node_modules, and website-builder-package folders
# Deletes backup files after successful replacement
# Designed to work in CI/CD build jobs
# Works in GitLab repository environment

set -e  # Exit on any error

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_DIR="$SCRIPT_DIR"
SEARCH_STRING="'ai-website-builder'"
REPLACE_STRING="'tenweb-builder'"
BACKUP_DIR="${PLUGIN_DIR}/backup-$(date +%Y%m%d-%H%M%S)"
LOG_FILE="${PLUGIN_DIR}/replacement-$(date +%Y%m%d-%H%M%S).log"

# CI/CD Environment Variables
CI_MODE="${CI_MODE:-false}"
SKIP_CONFIRMATION="${SKIP_CONFIRMATION:-false}"
VERBOSE="${VERBOSE:-false}"

# Colors for output (only if not in CI mode)
if [ "$CI_MODE" = "true" ] || [ -n "$CI" ]; then
    RED=''
    GREEN=''
    YELLOW=''
    BLUE=''
    NC=''
else
    RED='\033[0;31m'
    GREEN='\033[0;32m'
    YELLOW='\033[1;33m'
    BLUE='\033[0;34m'
    NC='\033[0m' # No Color
fi

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to log messages
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "$LOG_FILE"
}

# Function to validate repository directory
validate_repository_directory() {
    # Check if we're in a Git repository
    if [ ! -d ".git" ]; then
        print_warning "Not in a Git repository. Continuing anyway..."
        log_message "Not in a Git repository. Continuing anyway..."
        return 0
    fi
    
    # Check if the directory contains expected files (adjust based on your repository structure)
    if [ -f "tenweb-builder.php" ] || [ -f "builder.php" ] || [ -f "config.php" ]; then
        print_success "Repository directory validated: $PLUGIN_DIR"
        log_message "Repository directory validated: $PLUGIN_DIR"
    else
        print_error "Expected plugin files not found. Terminating script."
        print_error "Required files: tenweb-builder.php, builder.php, or config.php"
        print_error "Current directory: $PLUGIN_DIR"
        log_message "ERROR: Expected plugin files not found. Terminating script."
        exit 1
    fi
}

# Function to create backup
create_backup() {
    print_status "Creating backup directory: $BACKUP_DIR"
    mkdir -p "$BACKUP_DIR"
    
    # Copy only PHP files to backup directory (excluding specified folders)
    print_status "Creating backup of PHP files..."
    find "$PLUGIN_DIR" -maxdepth 1 -name "*.php" -exec cp {} "$BACKUP_DIR/" \;
    
    # Copy PHP files from subdirectories (excluding specified folders)
    find "$PLUGIN_DIR" -mindepth 1 -name "*.php" -not -path "*/vendor/*" -not -path "*/node_modules/*" -not -path "*/website-builder-package/*" -not -path "*/backup-*" -not -path "*/replacement-*.log" -not -path "*/.git/*" -exec cp --parents {} "$BACKUP_DIR/" \;
    
    if [ $? -eq 0 ]; then
        print_success "Backup created successfully in: $BACKUP_DIR"
        log_message "Backup created successfully in: $BACKUP_DIR"
    else
        print_error "Failed to create backup"
        exit 1
    fi
}

# Function to find and replace strings in PHP files
replace_strings() {
    local total_files=0
    local modified_files=0
    
    print_status "Searching for PHP files containing '$SEARCH_STRING' in repository..."
    
    # Find all PHP files containing the search string (excluding specified folders)
    while IFS= read -r -d '' file; do
        # Additional safety check - ensure file is within repository directory
        if [[ "$file" != "$PLUGIN_DIR"/* ]]; then
            print_warning "Skipping file outside repository directory: $file"
            continue
        fi
        
        # Skip backup and log files
        if [[ "$file" == *"backup-"* ]] || [[ "$file" == *"replacement-"* ]] || [[ "$file" == *".log" ]]; then
            continue
        fi
        
        # Skip files in excluded directories
        if [[ "$file" == *"/vendor/"* ]] || [[ "$file" == *"/node_modules/"* ]] || [[ "$file" == *"/website-builder-package/"* ]]; then
            continue
        fi
        
        # Process only PHP files
        if [ -f "$file" ] && [ -r "$file" ] && [[ "$file" == *.php ]]; then
            total_files=$((total_files + 1))
            
            # Check if file contains the search string
            if grep -q "$SEARCH_STRING" "$file" 2>/dev/null; then
                print_status "Processing PHP file: $file"
                
                # Create a temporary file for the replacement
                temp_file=$(mktemp)
                
                # Perform the replacement
                if sed "s/$SEARCH_STRING/$REPLACE_STRING/g" "$file" > "$temp_file"; then
                    # Check if the file was actually modified
                    if ! cmp -s "$file" "$temp_file"; then
                        # Move the modified content back to the original file
                        mv "$temp_file" "$file"
                        modified_files=$((modified_files + 1))
                        print_success "Modified: $file"
                        log_message "Modified: $file"
                        
                        # Show verbose output if enabled
                        if [ "$VERBOSE" = "true" ]; then
                            echo "  - Replaced instances in: $file"
                        fi
                    else
                        # No changes were made
                        rm "$temp_file"
                        if [ "$VERBOSE" = "true" ]; then
                            print_status "No changes needed: $file"
                        fi
                    fi
                else
                    print_error "Failed to process: $file"
                    rm "$temp_file"
                    log_message "ERROR: Failed to process: $file"
                fi
            fi
        fi
    done < <(find "$PLUGIN_DIR" -name "*.php" -not -path "*/vendor/*" -not -path "*/node_modules/*" -not -path "*/website-builder-package/*" -not -path "*/backup-*" -not -path "*/replacement-*.log" -not -path "*/.git/*" -print0)
    
    print_success "Processing complete!"
    print_status "Total PHP files scanned: $total_files"
    print_status "PHP files modified: $modified_files"
    log_message "Processing complete! Total PHP files scanned: $total_files, PHP files modified: $modified_files"
}

# Function to verify replacements
verify_replacements() {
    print_status "Verifying replacements..."
    
    # Check if any instances of the old string remain in PHP files within repository directory only
    remaining_instances=$(grep -r "$SEARCH_STRING" "$PLUGIN_DIR" --include="*.php" --exclude-dir="vendor" --exclude-dir="node_modules" --exclude-dir="website-builder-package" --exclude-dir="backup-*" --exclude="replacement-*.log" --exclude-dir=".git" 2>/dev/null | wc -l)
    
    if [ "$remaining_instances" -eq 0 ]; then
        print_success "Verification passed! No remaining instances of '$SEARCH_STRING' found in PHP files."
        log_message "Verification passed! No remaining instances of '$SEARCH_STRING' found in PHP files."
        return 0
    else
        print_warning "Verification failed! Found $remaining_instances remaining instances of '$SEARCH_STRING' in PHP files."
        log_message "WARNING: Verification failed! Found $remaining_instances remaining instances of '$SEARCH_STRING' in PHP files."
        
        # Show remaining instances
        print_status "Remaining instances:"
        grep -r "$SEARCH_STRING" "$PLUGIN_DIR" --include="*.php" --exclude-dir="vendor" --exclude-dir="node_modules" --exclude-dir="website-builder-package" --exclude-dir="backup-*" --exclude="replacement-*.log" --exclude-dir=".git" 2>/dev/null | head -10
        return 1
    fi
}

# Function to delete backup files
delete_backup() {
    print_status "Deleting backup files..."
    
    if [ -d "$BACKUP_DIR" ]; then
        rm -rf "$BACKUP_DIR"
        if [ $? -eq 0 ]; then
            print_success "Backup directory deleted: $BACKUP_DIR"
            log_message "Backup directory deleted: $BACKUP_DIR"
        else
            print_error "Failed to delete backup directory: $BACKUP_DIR"
            log_message "ERROR: Failed to delete backup directory: $BACKUP_DIR"
        fi
    fi
    
    # Also delete any other backup files that might exist within repository directory only
    find "$PLUGIN_DIR" -maxdepth 1 -name "backup-*" -type d 2>/dev/null | while read -r backup_dir; do
        if [ "$backup_dir" != "$BACKUP_DIR" ]; then
            print_status "Deleting existing backup: $backup_dir"
            rm -rf "$backup_dir"
            log_message "Deleted existing backup: $backup_dir"
        fi
    done
}

# Function to show summary
show_summary() {
    echo
    echo "=========================================="
    echo "           REPLACEMENT SUMMARY            "
    echo "=========================================="
    echo "Repository Directory: $PLUGIN_DIR"
    echo "Search String: $SEARCH_STRING"
    echo "Replace String: $REPLACE_STRING"
    echo "Log File: $LOG_FILE"
    echo "Excluded Folders: vendor, node_modules, website-builder-package"
    echo "CI Mode: $CI_MODE"
    echo "Scope: Current repository directory"
    echo "=========================================="
    echo
}

# Function to check CI environment
check_ci_environment() {
    if [ -n "$CI" ] || [ "$CI_MODE" = "true" ]; then
        print_status "Running in CI/CD environment"
        CI_MODE="true"
        SKIP_CONFIRMATION="true"
        log_message "Running in CI/CD environment"
    fi
}

# Main execution
main() {
    echo "=========================================="
    echo "    AI-Website Builder String Replacer    "
    echo "=========================================="
    echo "    GitLab Repository Compatible          "
    echo "=========================================="
    echo
    
    # Check CI environment
    check_ci_environment
    
    # Validate repository directory
    validate_repository_directory
    
    print_status "Starting string replacement process..."
    log_message "Starting string replacement process..."
    
    # Show what will be done
    print_status "Will replace all instances of '$SEARCH_STRING' with '$REPLACE_STRING' in PHP files"
    print_status "Current working directory: $PLUGIN_DIR"
    print_status "Scope: Current repository directory"
    print_status "Excluding: vendor, node_modules, website-builder-package folders"
    print_status "CI Mode: $CI_MODE"
    
    # Skip confirmation in CI mode or if explicitly set
    if [ "$SKIP_CONFIRMATION" = "true" ]; then
        print_status "Skipping confirmation (CI mode or SKIP_CONFIRMATION=true)"
    else
        # Ask for confirmation
        echo
        read -p "Do you want to continue? (y/N): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_status "Operation cancelled by user"
            exit 0
        fi
    fi
    
    # Create backup
    create_backup
    
    # Perform replacements
    replace_strings
    
    # Verify replacements
    if verify_replacements; then
        # Delete backup files only if verification passed
        delete_backup
        print_success "String replacement process completed successfully!"
        print_status "Backup files have been deleted"
    else
        print_warning "String replacement completed but verification failed!"
        if [ "$CI_MODE" = "true" ]; then
            print_error "Build job failed due to verification failure"
            print_status "Backup location: $BACKUP_DIR"
            exit 1  # Exit with error code for CI
        else
            print_status "Backup files have been preserved for manual review"
            print_status "Backup location: $BACKUP_DIR"
        fi
    fi
    
    # Show summary
    show_summary
    
    print_status "Check the log file for detailed information: $LOG_FILE"
}

# Run main function
main "$@"
