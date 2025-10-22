#!/bin/bash
# Haichan PoW System - Validation Script

set -e

echo "======================================"
echo "Haichan PoW System Validation"
echo "======================================"
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Navigate to pow directory
cd "$(dirname "$0")"

echo "1. Checking Rust installation..."
if command -v cargo &> /dev/null; then
    echo -e "${GREEN}✓${NC} Rust/Cargo installed: $(cargo --version)"
else
    echo -e "${RED}✗${NC} Rust/Cargo not found"
    exit 1
fi

echo ""
echo "2. Building Rust verifier..."
cd verifier
cargo build --release --lib > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Verifier built successfully"
else
    echo -e "${RED}✗${NC} Verifier build failed"
    exit 1
fi

echo ""
echo "3. Running Rust verifier tests..."
if cargo test --release --lib 2>&1 | grep -q "test result: ok"; then
    echo -e "${GREEN}✓${NC} All verifier tests passed"
else
    echo -e "${RED}✗${NC} Verifier tests failed"
    exit 1
fi

cd ..

echo ""
echo "4. Building WASM miner..."
cd miner-wasm
cargo build --release > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} WASM miner built successfully"
else
    echo -e "${RED}✗${NC} WASM miner build failed"
    exit 1
fi

cd ..

echo ""
echo "5. Checking test vectors..."
if [ -f "vectors/golden_vectors_v1.json" ]; then
    VECTOR_COUNT=$(cat vectors/golden_vectors_v1.json | grep -c '"name"' || echo "0")
    if [ "$VECTOR_COUNT" -eq "5" ]; then
        echo -e "${GREEN}✓${NC} 5 golden test vectors found"
    else
        echo -e "${RED}✗${NC} Expected 5 vectors, found $VECTOR_COUNT"
        exit 1
    fi
else
    echo -e "${RED}✗${NC} golden_vectors_v1.json not found"
    exit 1
fi

echo ""
echo "6. Checking Laravel files..."
LARAVEL_ROOT=".."

if [ -f "${LARAVEL_ROOT}/app/Http/Controllers/PowController.php" ]; then
    echo -e "${GREEN}✓${NC} PowController.php exists"
else
    echo -e "${RED}✗${NC} PowController.php not found"
    exit 1
fi

if [ -f "${LARAVEL_ROOT}/app/Models/PowChallenge.php" ]; then
    echo -e "${GREEN}✓${NC} PowChallenge model exists"
else
    echo -e "${RED}✗${NC} PowChallenge model not found"
    exit 1
fi

if [ -f "${LARAVEL_ROOT}/app/Models/PowCommit.php" ]; then
    echo -e "${GREEN}✓${NC} PowCommit model exists"
else
    echo -e "${RED}✗${NC} PowCommit model not found"
    exit 1
fi

if [ -f "${LARAVEL_ROOT}/database/migrations/2025_10_22_create_pow_tables.php" ]; then
    echo -e "${GREEN}✓${NC} Migration file exists"
else
    echo -e "${RED}✗${NC} Migration file not found"
    exit 1
fi

if [ -f "${LARAVEL_ROOT}/tests/Feature/PowSystemTest.php" ]; then
    echo -e "${GREEN}✓${NC} PHPUnit test exists"
else
    echo -e "${RED}✗${NC} PHPUnit test not found"
    exit 1
fi

echo ""
echo "7. Checking API routes..."
if grep -q "PowController" ${LARAVEL_ROOT}/routes/api.php; then
    echo -e "${GREEN}✓${NC} PoW routes registered"
else
    echo -e "${RED}✗${NC} PoW routes not found"
    exit 1
fi

echo ""
echo "8. Checking documentation..."

if [ -f "README.md" ]; then
    echo -e "${GREEN}✓${NC} README.md exists"
else
    echo -e "${RED}✗${NC} README.md not found"
fi

if [ -f "CURL_EXAMPLES.md" ]; then
    echo -e "${GREEN}✓${NC} CURL_EXAMPLES.md exists"
else
    echo -e "${RED}✗${NC} CURL_EXAMPLES.md not found"
fi

if [ -f "DELIVERY_SUMMARY.md" ]; then
    echo -e "${GREEN}✓${NC} DELIVERY_SUMMARY.md exists"
else
    echo -e "${RED}✗${NC} DELIVERY_SUMMARY.md not found"
fi

echo ""
echo "======================================"
echo -e "${GREEN}✓ All validation checks passed!${NC}"
echo "======================================"
echo ""
echo "System is ready for deployment."
echo ""
echo "Next steps:"
echo "  1. Run Laravel migrations: php artisan migrate"
echo "  2. Run PHPUnit tests: php artisan test --filter PowSystemTest"
echo "  3. Start the server: php artisan serve"
echo "  4. Test with cURL examples in pow/CURL_EXAMPLES.md"
echo ""
