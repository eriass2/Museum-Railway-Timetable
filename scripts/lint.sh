#!/usr/bin/env bash
# Run PHPStan and PHPCS (requires: composer install)
set -e
cd "$(dirname "$0")/.."

if [ ! -d vendor ]; then
    echo "Run 'composer install' first."
    exit 1
fi

echo "Running PHPStan..."
./vendor/bin/phpstan analyse --no-progress

echo "Running PHPCS..."
./vendor/bin/phpcs

echo "Lint OK."
