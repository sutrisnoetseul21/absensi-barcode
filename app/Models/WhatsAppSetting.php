<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppSetting extends Model
{
    protected $table = 'whatsapp_settings';
    
    protected $fillable = [
        'base_url',
        'api_key',
        'instance_name',
        'sender_number',
        'is_active',
        'delay_between_messages_seconds',
        'send_window_start',
        'send_window_end',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'is_active' => 'boolean',
    ];

    public function getBaseUrlAttribute($value)
    {
        $envVal = env('WA_BASE_URL');
        return !empty($envVal) ? $envVal : $value;
    }

    public function getApiKeyAttribute($value)
    {
        $envVal = env('WA_API_KEY');
        return !empty($envVal) ? $envVal : $value;
    }

    /**
     * Helper untuk mengambil setting secara singleton global
     */
    public static function current()
    {
        // Akan mengambil baris pertama, jika belum ada akan otomatis dibuatkan
        return static::firstOrCreate(
            ['id' => 1],
            [
                'is_active' => false,
                'delay_between_messages_seconds' => 4,
                'send_window_start' => '06:00:00',
                'send_window_end' => '17:00:00',
            ]
        );
    }
}
