#!/bin/bash
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHP_BIN="$(which php8.4 2>/dev/null || which php 2>/dev/null || echo "php")"

echo "=== Status Layanan Queue (Process / Supervisor) ==="
if sudo -n true 2>/dev/null; then
    sudo supervisorctl status smp1sampang-worker:* 2>/dev/null || sudo supervisorctl status 2>/dev/null
else
    ps aux | grep -E '[a]rtisan queue:work' || echo "Tidak ada proses queue:work yang berjalan."
fi
echo ""
echo "=== Status Antrean Database ==="
"$PHP_BIN" "$DIR/artisan" tinker --execute="echo 'Pending Jobs : ' . DB::table('jobs')->count() . PHP_EOL . 'Failed Jobs  : ' . DB::table('failed_jobs')->count() . PHP_EOL;"
echo ""
