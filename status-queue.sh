#!/bin/bash
echo "=== Status Layanan Queue (Supervisor) ==="
sudo supervisorctl status laravel-worker:*
echo ""
echo "=== Status Antrean Database ==="
/usr/bin/php8.3 /home/smpn3kedungreja/web/app.smpn3kedungreja.sch.id/public_html/artisan tinker --execute="echo 'Pending Jobs : ' . DB::table('jobs')->count() . PHP_EOL . 'Failed Jobs  : ' . DB::table('failed_jobs')->count() . PHP_EOL;"
echo ""
