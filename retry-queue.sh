#!/bin/bash
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHP_BIN="$(which php8.4 2>/dev/null || which php 2>/dev/null || echo "php")"

echo "Mencoba mengirim ulang semua antrean yang gagal..."
"$PHP_BIN" "$DIR/artisan" queue:retry all
echo "Selesai!"
