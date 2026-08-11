#!/bin/bash
echo "Merestart layanan antrean (laravel-worker)..."
sudo supervisorctl restart laravel-worker:*
echo "Selesai!"
