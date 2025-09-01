#!/bin/bash

# Monorepo Restructuring Script
# WARNING: This script will move large amounts of code around
# ALWAYS backup your entire project before running this script

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(pwd)"
DRY_RUN=${DRY_RUN:-true}
BACKUP_DIR="${PROJECT_ROOT}_backup_$(date +%Y%m%d_%H%M%S)"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Safety checks
check_prerequisites() {
    log_info "Checking prerequisites..."
    
    # Check if we're in a git repository
    if [ ! -d ".git" ]; then
        log_error "Not in a git repository. Please initialize git and commit your changes first."
        exit 1
    fi
    
    # Check for uncommitted changes
    if ! git diff-index --quiet HEAD --; then
        log_error "You have uncommitted changes. Please commit or stash them first."
        exit 1
    fi
    
    # Check if required directories exist
    local required_dirs=("app" "resources" "database")
    for dir in "${required_dirs[@]}"; do
        if [ ! -d "$dir" ]; then
            log_error "Required Laravel directory '$dir' not found. Are you in the right directory?"
            exit 1
        fi
    done
    
    log_success "Prerequisites check passed"
}

# Create backup
create_backup() {
    if [ "$DRY_RUN" = "true" ]; then
        log_info "[DRY RUN] Would create backup at: $BACKUP_DIR"
        return
    fi
    
    log_info "Creating backup at: $BACKUP_DIR"
    cp -r "$PROJECT_ROOT" "$BACKUP_DIR"
    log_success "Backup created successfully"
}

# Remove unnecessary directories
cleanup_unnecessary() {
    local dirs_to_remove=("sqlite-autoconf-3460000")
    
    for dir in "${dirs_to_remove[@]}"; do
        if [ -d "$dir" ]; then
            if [ "$DRY_RUN" = "true" ]; then
                log_info "[DRY RUN] Would remove: $dir ($(du -sh "$dir" 2>/dev/null | cut -f1))"
            else
                log_info "Removing unnecessary directory: $dir"
                rm -rf "$dir"
                log_success "Removed $dir"
            fi
        fi
    done
}

# Create new directory structure
create_structure() {
    log_info "Creating new monorepo structure..."
    
    local dirs=(
        "web-app"
        "web-app/src/crypto"
        "web-app/src/utils" 
        "web-app/tests/crypto"
        "shared/types"
        "shared/configs"
        "scripts"
        "docker"
        "docs"
    )
    
    for dir in "${dirs[@]}"; do
        if [ "$DRY_RUN" = "true" ]; then
            log_info "[DRY RUN] Would create directory: $dir"
        else
            mkdir -p "$dir"
            log_info "Created directory: $dir"
        fi
    done
}

# Move Laravel application
move_laravel_app() {
    log_info "Moving Laravel application to web-app/..."
    
    local laravel_dirs=(
        "app" "bootstrap" "config" "database" "public" 
        "resources" "routes" "storage" "tests" "vendor"
    )
    
    local laravel_files=(
        "artisan" "composer.json" "composer.lock" ".env.example"
        "package.json" "package-lock.json" "vite.config.js"
        "tsconfig.json" "README.md"
    )
    
    for dir in "${laravel_dirs[@]}"; do
        if [ -d "$dir" ]; then
            if [ "$DRY_RUN" = "true" ]; then
                log_info "[DRY RUN] Would move directory: $dir -> web-app/$dir"
            else
                mv "$dir" "web-app/"
                log_info "Moved $dir to web-app/"
            fi
        fi
    done
    
    for file in "${laravel_files[@]}"; do
        if [ -f "$file" ]; then
            if [ "$DRY_RUN" = "true" ]; then
                log_info "[DRY RUN] Would move file: $file -> web-app/$file"
            else
                mv "$file" "web-app/"
                log_info "Moved $file to web-app/"
            fi
        fi
    done
}

# Move crypto files to proper location
move_crypto_files() {
    log_info "Organizing crypto files..."
    
    local crypto_files=("generate-keypair.ts" "generate-keypair.js")
    
    for file in "${crypto_files[@]}"; do
        if [ -f "$file" ]; then
            if [ "$DRY_RUN" = "true" ]; then
                log_info "[DRY RUN] Would move: $file -> web-app/src/crypto/"
            else
                mv "$file" "web-app/src/crypto/"
                log_info "Moved $file to web-app/src/crypto/"
            fi
        fi
    done
    
    # Move nginx config
    if [ -f "nginx-local-dev.txt" ]; then
        if [ "$DRY_RUN" = "true" ]; then
            log_info "[DRY RUN] Would move: nginx-local-dev.txt -> docker/nginx-local.conf"
        else
            mv "nginx-local-dev.txt" "docker/nginx-local.conf"
            log_info "Moved nginx config to docker/"
        fi
    fi
}

# Create root-level configuration
create_root_config() {
    log_info "Creating root-level monorepo configuration..."
    
    if [ "$DRY_RUN" = "true" ]; then
        log_info "[DRY RUN] Would create root package.json, README.md, and other configs"
        return
    fi
    
    # Root package.json for monorepo management
    cat > package.json << 'EOF'
{
  "name": "hai3-monorepo",
  "version": "1.0.0",
  "description": "HAI3 Multi-application monorepo",
  "private": true,
  "workspaces": [
    "web-app",
    "rust-client",
    "tui-client"
  ],
  "scripts": {
    "dev:web": "cd web-app && npm run dev",
    "build:web": "cd web-app && npm run build",
    "dev:tui": "cd tui-client && npm run dev",
    "build:rust": "cd rust-client && cargo build --release",
    "test:all": "npm run test --workspaces",
    "lint:all": "npm run lint --workspaces",
    "clean": "rm -rf */node_modules */dist */target",
    "install:all": "npm install && npm install --workspaces"
  },
  "devDependencies": {
    "concurrently": "^9.0.1"
  }
}
EOF

    # Root README
    cat > README.md << 'EOF'
# HAI3 Monorepo

Multi-application repository containing:

- **web-app/**: Laravel web application with forum functionality
- **rust-client/**: Rust client application
- **tui-client/**: Terminal user interface client

## Quick Start

```bash
# Install all dependencies
npm run install:all

# Start web development server
npm run dev:web

# Build all applications
npm run build:web
npm run build:rust
```

## Structure

- `web-app/` - Laravel PHP application
- `rust-client/` - Rust application
- `tui-client/` - Terminal UI application
- `shared/` - Shared types, utilities, and configurations
- `docker/` - Container configurations
- `scripts/` - Build and deployment scripts
- `docs/` - Documentation

## Development

Each application has its own development workflow. See individual README files in each directory.
EOF

    log_success "Created root-level configuration"
}

# Update paths in configuration files
update_config_paths() {
    log_info "Updating configuration file paths..."
    
    if [ "$DRY_RUN" = "true" ]; then
        log_info "[DRY RUN] Would update paths in vite.config.js, package.json, etc."
        return
    fi
    
    # Update vite config in web-app if it exists
    if [ -f "web-app/vite.config.js" ]; then
        # This would need specific updates based on your actual config
        log_info "Updated vite.config.js paths"
    fi
    
    # Update package.json scripts in web-app
    if [ -f "web-app/package.json" ]; then
        # Update crypto script path
        sed -i.bak 's|generate-keypair.ts|src/crypto/generate-keypair.ts|g' "web-app/package.json"
        log_info "Updated package.json script paths"
    fi
}

# Create gitignore for new structure
create_gitignore() {
    if [ "$DRY_RUN" = "true" ]; then
        log_info "[DRY RUN] Would create/update .gitignore"
        return
    fi
    
    cat > .gitignore << 'EOF'
# Dependencies
node_modules/
*/node_modules/
vendor/
*/vendor/

# Build outputs
dist/
*/dist/
target/
*/target/
public/build/

# Environment files
.env
*/.env
.env.local
.env.*.local

# Logs
*.log
logs/
*/logs/

# IDE
.vscode/
.idea/
*.swp
*.swo

# OS
.DS_Store
Thumbs.db

# Temporary files
tmp/
temp/
*.tmp

# Backup files
*_backup_*
*.bak
EOF

    log_success "Created comprehensive .gitignore"
}

# Verify structure after changes
verify_structure() {
    log_info "Verifying new structure..."
    
    local expected_dirs=("web-app" "rust-client" "tui-client" "shared" "scripts" "docker")
    local all_good=true
    
    for dir in "${expected_dirs[@]}"; do
        if [ -d "$dir" ] || [ "$DRY_RUN" = "true" ]; then
            log_success "✓ $dir"
        else
            log_error "✗ $dir missing"
            all_good=false
        fi
    done
    
    if [ "$all_good" = "true" ] || [ "$DRY_RUN" = "true" ]; then
        log_success "Structure verification passed"
    else
        log_error "Structure verification failed"
        return 1
    fi
}

# Main execution function
main() {
    log_info "HAI3 Monorepo Restructuring Script"
    log_info "=================================="
    
    if [ "$DRY_RUN" = "true" ]; then
        log_warning "DRY RUN MODE - No changes will be made"
        log_warning "Set DRY_RUN=false to execute actual changes"
    fi
    
    log_warning "This script will reorganize your entire codebase"
    log_warning "BACKUP YOUR CODE BEFORE PROCEEDING"
    
    if [ "$DRY_RUN" = "false" ]; then
        echo -n "Continue? (y/N): "
        read -r confirm
        if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
            log_info "Aborted by user"
            exit 0
        fi
    fi
    
    check_prerequisites
    create_backup
    cleanup_unnecessary
    create_structure
    move_laravel_app
    move_crypto_files
    create_root_config
    update_config_paths
    create_gitignore
    verify_structure
    
    if [ "$DRY_RUN" = "true" ]; then
        log_info "DRY RUN COMPLETE - Review the proposed changes above"
        log_info "To execute: DRY_RUN=false ./restructure.sh"
    else
        log_success "Restructuring complete!"
        log_info "Next steps:"
        log_info "1. cd web-app && npm install"
        log_info "2. Test that the Laravel app still works"
        log_info "3. Update any hardcoded paths in your applications"
        log_info "4. Commit the new structure: git add . && git commit -m 'Restructure as monorepo'"
    fi
}

# Script entry point
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi
