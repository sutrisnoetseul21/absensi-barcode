#!/bin/bash
echo "Mencoba mengirim ulang semua antrean yang gagal..."
/usr/bin/php8.3 /home/smpn3kedungreja/web/app.smpn3kedungreja.sch.id/public_html/artisan queue:retry all
echo "Selesai!"
