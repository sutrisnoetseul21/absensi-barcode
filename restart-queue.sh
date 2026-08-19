#!/bin/bash
echo "Merestart layanan antrean (smp1sampang-worker)..."
sudo supervisorctl restart smp1sampang-worker:* 2>/dev/null || sudo supervisorctl restart laravel-worker:* 2>/dev/null || sudo supervisorctl restart all
echo "Selesai!"
