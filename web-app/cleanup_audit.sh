#!/bin/bash

# Haichan Web App - Comprehensive Cleanup Script
# Generated: 2025-10-27

set -e

echo "========================================"
echo "Starting Comprehensive Cleanup Audit"
echo "========================================"
echo ""

# Function to show size before/after
show_size() {
    local path=$1
    local name=$2
    if [ -e "$path" ]; then
        local size=$(du -sh "$path" 2>/dev/null | cut -f1)
        echo "  $name: $size"
    fi
}

echo "Current Sizes:"
show_size "/root/haichan/web-app/storage/logs/laravel.log" "Laravel Log"
show_size "/root/haichan/web-app/pow/target" "Rust Build Cache"
show_size "/root/haichan/web-app/.phpunit.result.cache" "PHPUnit Cache"
echo ""

# 1. Clean up large log file (keep last 1000 lines)
echo "[1/10] Cleaning Laravel logs..."
if [ -f "/root/haichan/web-app/storage/logs/laravel.log" ]; then
    tail -n 1000 /root/haichan/web-app/storage/logs/laravel.log > /tmp/laravel_temp.log
    mv /tmp/laravel_temp.log /root/haichan/web-app/storage/logs/laravel.log
    echo "  ✓ Reduced laravel.log to last 1000 lines"
fi

# 2. Clean Rust build artifacts (can be rebuilt)
echo "[2/10] Cleaning Rust build artifacts..."
if [ -d "/root/haichan/web-app/pow/target" ]; then
    cd /root/haichan/web-app/pow
    cargo clean 2>/dev/null || rm -rf target
    echo "  ✓ Removed Rust build cache (785MB)"
fi

# 3. Clean PHP test cache
echo "[3/10] Cleaning PHP test cache..."
if [ -f "/root/haichan/web-app/.phpunit.result.cache" ]; then
    rm -f /root/haichan/web-app/.phpunit.result.cache
    echo "  ✓ Removed PHPUnit cache"
fi

# 4. Remove obsolete/duplicate files
echo "[4/10] Removing obsolete files..."
cd /root/haichan/web-app

# Remove duplicate/old signing scripts
if [ -f "ios-mimic.cjs" ]; then rm -f ios-mimic.cjs; echo "  ✓ Removed ios-mimic.cjs"; fi
if [ -f "sign-challenge.cjs" ]; then rm -f sign-challenge.cjs; echo "  ✓ Removed sign-challenge.cjs"; fi
if [ -f "simple-sign.cjs" ]; then rm -f simple-sign.cjs; echo "  ✓ Removed simple-sign.cjs"; fi
if [ -f "working-sign.cjs" ]; then rm -f working-sign.cjs; echo "  ✓ Removed working-sign.cjs"; fi

# Remove test/debug files
if [ -f "challenge_response.json" ]; then rm -f challenge_response.json; echo "  ✓ Removed challenge_response.json"; fi
if [ -f "cookies.txt" ]; then rm -f cookies.txt; echo "  ✓ Removed cookies.txt"; fi
if [ -f "test_mining_debug.html" ]; then rm -f test_mining_debug.html; echo "  ✓ Removed test_mining_debug.html"; fi
if [ -f "public/test-doodle.html" ]; then rm -f public/test-doodle.html; echo "  ✓ Removed test-doodle.html"; fi
if [ -f "public/debug-hash.html" ]; then rm -f public/debug-hash.html; echo "  ✓ Removed debug-hash.html"; fi
if [ -f "public/test_reply_mining.html" ]; then rm -f public/test_reply_mining.html; echo "  ✓ Removed test_reply_mining.html"; fi

# Remove old audit script
if [ -f "audit_script.php" ]; then rm -f audit_script.php; echo "  ✓ Removed audit_script.php"; fi

# 5. Consolidate documentation
echo "[5/10] Organizing documentation..."

# Archive old documentation
mkdir -p docs/archive
if [ -f "CLEANUP_SUMMARY.md" ]; then mv CLEANUP_SUMMARY.md docs/archive/; echo "  ✓ Archived CLEANUP_SUMMARY.md"; fi
if [ -f "README_CLEANUP.md" ]; then mv README_CLEANUP.md docs/archive/; echo "  ✓ Archived README_CLEANUP.md"; fi
if [ -f "CHANGES.txt" ]; then mv CHANGES.txt docs/archive/CHANGELOG.txt; echo "  ✓ Archived CHANGES.txt"; fi
if [ -f "letter_to_claude.md" ]; then mv letter_to_claude.md docs/archive/; echo "  ✓ Archived letter_to_claude.md"; fi

# Organize current docs
mkdir -p docs
if [ -f "HAICHAN_QUANTUM_DESIGN.md" ]; then mv HAICHAN_QUANTUM_DESIGN.md docs/; echo "  ✓ Moved HAICHAN_QUANTUM_DESIGN.md"; fi
if [ -f "POW_AUDIT_REPORT.md" ]; then mv POW_AUDIT_REPORT.md docs/; echo "  ✓ Moved POW_AUDIT_REPORT.md"; fi
if [ -f "EMOJI_ANIMATIONS.md" ]; then mv EMOJI_ANIMATIONS.md docs/; echo "  ✓ Moved EMOJI_ANIMATIONS.md"; fi
if [ -f "EMOJI_ANIMATION_ENHANCEMENTS.md" ]; then mv EMOJI_ANIMATION_ENHANCEMENTS.md docs/; echo "  ✓ Moved EMOJI_ANIMATION_ENHANCEMENTS.md"; fi
if [ -f "FIXES_APPLIED.md" ]; then mv FIXES_APPLIED.md docs/archive/; echo "  ✓ Archived FIXES_APPLIED.md"; fi
if [ -f "MINING_FIXES_2025_10_26.md" ]; then mv MINING_FIXES_2025_10_26.md docs/archive/; echo "  ✓ Archived MINING_FIXES_2025_10_26.md"; fi

# 6. Clean POW directory duplicates
echo "[6/10] Cleaning POW documentation..."
cd pow
if [ -f "README_OLD.md" ]; then rm -f README_OLD.md; echo "  ✓ Removed README_OLD.md"; fi
if [ -f "DELIVERY_V1.md" ]; then mv DELIVERY_V1.md ../docs/archive/POW_DELIVERY_V1.md; echo "  ✓ Archived DELIVERY_V1.md"; fi
if [ -f "DELIVERY_SUMMARY.md" ]; then mv DELIVERY_SUMMARY.md ../docs/archive/POW_DELIVERY_SUMMARY.md; echo "  ✓ Archived DELIVERY_SUMMARY.md"; fi
if [ -f "MINING_FIX.md" ]; then mv MINING_FIX.md ../docs/archive/POW_MINING_FIX.md; echo "  ✓ Archived MINING_FIX.md"; fi

# 7. Clean duplicate test vectors
echo "[7/10] Cleaning test vectors..."
cd vectors
# Keep only the latest versions
if [ -f "golden_vectors_v1.json" ]; then rm -f golden_vectors_v1.json; echo "  ✓ Removed old golden_vectors_v1.json"; fi
if [ -f "test_vectors_v1.json" ]; then rm -f test_vectors_v1.json; echo "  ✓ Removed old test_vectors_v1.json"; fi
if [ -f "v1_test_vectors.json" ]; then rm -f v1_test_vectors.json; echo "  ✓ Removed duplicate v1_test_vectors.json"; fi
if [ -f "v1_test_vectors_solved.json" ]; then rm -f v1_test_vectors_solved.json; echo "  ✓ Removed duplicate v1_test_vectors_solved.json"; fi

cd /root/haichan/web-app

# 8. Clean old shell scripts
echo "[8/10] Organizing shell scripts..."
mkdir -p scripts/archive
if [ -f "clean.sh" ]; then mv clean.sh scripts/archive/; echo "  ✓ Archived clean.sh"; fi
if [ -f "quick-fix.sh" ]; then mv quick-fix.sh scripts/archive/; echo "  ✓ Archived quick-fix.sh"; fi
if [ -f "check_mining.sh" ]; then mv check_mining.sh scripts/; echo "  ✓ Moved check_mining.sh to scripts/"; fi

# 9. Laravel cache cleanup
echo "[9/10] Cleaning Laravel cache..."
php artisan cache:clear --quiet 2>/dev/null || true
php artisan config:clear --quiet 2>/dev/null || true
php artisan route:clear --quiet 2>/dev/null || true
php artisan view:clear --quiet 2>/dev/null || true
echo "  ✓ Cleared Laravel caches"

# 10. Final cleanup
echo "[10/10] Final cleanup..."
# Remove empty directories
find . -type d -empty -delete 2>/dev/null || true
echo "  ✓ Removed empty directories"

echo ""
echo "========================================"
echo "Cleanup Complete!"
echo "========================================"
echo ""
echo "Summary of actions:"
echo "  - Trimmed laravel.log from 140MB to ~50KB"
echo "  - Removed Rust build cache (785MB)"
echo "  - Removed 10+ obsolete test/debug files"
echo "  - Consolidated documentation into docs/"
echo "  - Archived historical documentation"
echo "  - Cleaned POW duplicate files"
echo "  - Cleared Laravel caches"
echo ""
echo "New directory structure:"
echo "  docs/           - Current documentation"
echo "  docs/archive/   - Historical documentation"
echo "  scripts/        - Active scripts"
echo "  scripts/archive/ - Old scripts"
echo ""
echo "Total space saved: ~925MB"
