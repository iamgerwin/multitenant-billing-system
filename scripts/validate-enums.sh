#!/bin/bash

# Enum Validation Script
# Validates that PHP and TypeScript enums are in sync

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(dirname "$SCRIPT_DIR")"

PHP_ENUMS_DIR="$ROOT_DIR/backend/app/Enums"
TS_ENUMS_FILE="$ROOT_DIR/shared/enums/index.ts"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "========================================"
echo "Enum Validation Script"
echo "========================================"
echo ""

# Function to extract PHP enum values
extract_php_enum_values() {
    local file="$1"
    local enum_name="$2"

    # Extract lines with "case X = 'value';" pattern and get the values
    grep -oE "case\s+\w+\s*=\s*'[^']+'" "$file" | \
        grep -oE "'[^']+'" | \
        tr -d "'" | \
        sort
}

# Function to extract TypeScript enum values
extract_ts_enum_values() {
    local file="$1"
    local enum_name="$2"

    # Extract the enum block and get values
    awk "/export enum $enum_name/,/^}/" "$file" | \
        grep -oE "=\s*'[^']+'" | \
        grep -oE "'[^']+'" | \
        tr -d "'" | \
        sort
}

# Function to compare enum values
compare_enums() {
    local enum_name="$1"
    local php_file="$2"
    local ts_file="$3"

    echo -e "${YELLOW}Checking $enum_name...${NC}"

    php_values=$(extract_php_enum_values "$php_file" "$enum_name")
    ts_values=$(extract_ts_enum_values "$ts_file" "$enum_name")

    # Create temp files for comparison
    php_temp=$(mktemp)
    ts_temp=$(mktemp)

    echo "$php_values" > "$php_temp"
    echo "$ts_values" > "$ts_temp"

    # Compare
    if diff -q "$php_temp" "$ts_temp" > /dev/null 2>&1; then
        echo -e "  ${GREEN}✓ $enum_name enums are in sync${NC}"
        echo "    Values: $(echo "$php_values" | tr '\n' ', ' | sed 's/,$//')"
        rm "$php_temp" "$ts_temp"
        return 0
    else
        echo -e "  ${RED}✗ $enum_name enums are OUT OF SYNC${NC}"
        echo ""
        echo "  PHP values:"
        echo "$php_values" | sed 's/^/    /'
        echo ""
        echo "  TypeScript values:"
        echo "$ts_values" | sed 's/^/    /'
        echo ""
        echo "  Differences:"
        diff "$php_temp" "$ts_temp" | sed 's/^/    /' || true
        rm "$php_temp" "$ts_temp"
        return 1
    fi
}

# Track overall status
has_errors=0

# Validate UserRole enum
echo ""
if ! compare_enums "UserRole" "$PHP_ENUMS_DIR/UserRole.php" "$TS_ENUMS_FILE"; then
    has_errors=1
fi

echo ""

# Validate InvoiceStatus enum
if ! compare_enums "InvoiceStatus" "$PHP_ENUMS_DIR/InvoiceStatus.php" "$TS_ENUMS_FILE"; then
    has_errors=1
fi

echo ""
echo "========================================"

if [ $has_errors -eq 0 ]; then
    echo -e "${GREEN}All enums are in sync!${NC}"
    exit 0
else
    echo -e "${RED}Enum validation failed! Please sync PHP and TypeScript enums.${NC}"
    exit 1
fi
