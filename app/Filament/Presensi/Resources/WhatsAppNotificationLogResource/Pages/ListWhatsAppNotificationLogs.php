<?php

namespace App\Filament\Presensi\Resources\WhatsAppNotificationLogResource\Pages;

use App\Filament\Presensi\Resources\WhatsAppNotificationLogResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Contracts\View\View;
use App\Models\WhatsAppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ListWhatsAppNotificationLogs extends ListRecords
{
    protected static string $resource = WhatsAppNotificationLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Intentionally empty. Logs are auto-generated.
        ];
    }

    public function getHeader(): ?View
    {
        $connectionError = $this->checkEvolutionApiConnection();

        if ($connectionError) {
            return view('filament.presensi.resources.whatsapp-log-warning-header', [
                'connectionError' => $connectionError
            ]);
        }

        return null;
    }

    private function checkEvolutionApiConnection(): ?string
    {
        // Cache the connection check for 1 minute to prevent slow page loads
        return Cache::remember('evolution_api_connection_status', 60, function () {
            try {
                $waSetting = WhatsAppSetting::current();
                
                if (!$waSetting->is_active) {
                    return 'Fitur WhatsApp Gateway dinonaktifkan di pengaturan.';
                }

                $baseUrl = $waSetting->base_url;
                $apiKey = $waSetting->api_key;
                $instanceName = $waSetting->instance_name;

                if (!$baseUrl || !$apiKey || !$instanceName) {
                    return 'Kredensial API WhatsApp (Base URL / API Key / Instance) belum dikonfigurasi lengkap.';
                }

                $endpoint = rtrim($baseUrl, '/') . '/instance/connectionState/' . $instanceName;
                
                $response = Http::withHeaders([
                    'apikey' => $apiKey
                ])->timeout(3)->get($endpoint);

                if ($response->successful()) {
                    $data = $response->json();
                    $state = $data['instance']['state'] ?? 'unknown';
                    
                    if ($state !== 'open') {
                        return 'Instance WhatsApp ditemukan, namun statusnya: ' . strtoupper($state) . '. Silakan pastikan sudah Scan QR dan perangkat terhubung.';
                    }

                    // Connected
                    return null; 
                } else {
                    return 'Gagal terhubung ke server Evolution API (HTTP ' . $response->status() . ').';
                }
            } catch (\Exception $e) {
                return 'Tidak dapat menjangkau server API WhatsApp: ' . $e->getMessage();
            }
        });
    }
}
