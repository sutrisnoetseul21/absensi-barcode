<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\AlumniSetting as ModelAlumniSetting;

class AlumniSetting extends Component
{
    public string $banner_title = '';
    public string $button_text = '';
    public string $banner_text = '';
    public bool $is_active = true;
    public bool $show_table = true;

    protected function rules(): array
    {
        return [
            'banner_title' => 'required|string|max:255',
            'button_text'  => 'required|string|max:100',
            'banner_text'  => 'nullable|string',
            'is_active'    => 'boolean',
            'show_table'   => 'boolean',
        ];
    }

    public function mount(): void
    {
        $setting = ModelAlumniSetting::instance();
        $this->banner_title = $setting->banner_title ?? 'Tracer Study Alumni';
        $this->button_text  = $setting->button_text ?? 'Daftarkan Data Saya';
        $this->banner_text  = $setting->banner_text ?? '';
        $this->is_active    = (bool) $setting->is_active;
        $this->show_table   = (bool) $setting->show_table;
    }

    public function save(): void
    {
        $data = $this->validate();

        $setting = ModelAlumniSetting::instance();
        $setting->update($data);

        session()->flash('success', 'Pengaturan Tracer Alumni berhasil disimpan.');
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        return view('livewire.portal-web.alumni-setting')
            ->title('Pengaturan Tracer Alumni — Portal Web');
    }
}
