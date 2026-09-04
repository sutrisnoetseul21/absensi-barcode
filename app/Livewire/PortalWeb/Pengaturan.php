<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Models\WebSetting;

class Pengaturan extends Component
{
    use WithFileUploads;

    public string $running_text = '';
    public string $profil_singkat = '';
    public string $visi = '';
    public string $misi = '';
    public string $kutipan_kepsek = '';
    public string $sambutan_kepsek = '';
    public string $link_youtube = '';
    public string $link_tiktok = '';
    public string $link_ig = '';
    public string $link_fb = '';
    public string $link_pengaduan = '';
    public int $stat_tenaga_kependidikan = 0;

    public $hero_image = null;
    public $foto_kepsek = null;
    public ?string $existingHeroImage = null;
    public ?string $existingFotoKepsek = null;

    protected function rules(): array
    {
        return [
            'running_text'             => 'nullable|string|max:65535',
            'profil_singkat'           => 'nullable|string|max:65535',
            'visi'                     => 'nullable|string|max:65535',
            'misi'                     => 'nullable|string|max:65535',
            'kutipan_kepsek'           => 'nullable|string|max:65535',
            'sambutan_kepsek'          => 'nullable|string|max:65535',
            'link_youtube'             => 'nullable|url|max:500',
            'link_tiktok'              => 'nullable|url|max:500',
            'link_ig'                  => 'nullable|url|max:500',
            'link_fb'                  => 'nullable|url|max:500',
            'link_pengaduan'           => 'nullable|url|max:500',
            'stat_tenaga_kependidikan' => 'integer|min:0',
            'hero_image'               => 'nullable|image|max:5120',
            'foto_kepsek'              => 'nullable|image|max:2048',
        ];
    }

    public function mount(): void
    {
        $setting = WebSetting::instance();
        $this->running_text             = $setting->running_text ?? '';
        $this->profil_singkat           = $setting->profil_singkat ?? '';
        $this->visi                     = $setting->visi ?? '';
        $this->misi                     = $setting->misi ?? '';
        $this->kutipan_kepsek           = $setting->kutipan_kepsek ?? '';
        $this->sambutan_kepsek          = $setting->sambutan_kepsek ?? '';
        $this->link_youtube             = $setting->link_youtube ?? '';
        $this->link_tiktok              = $setting->link_tiktok ?? '';
        $this->link_ig                  = $setting->link_ig ?? '';
        $this->link_fb                  = $setting->link_fb ?? '';
        $this->link_pengaduan           = $setting->link_pengaduan ?? '';
        $this->stat_tenaga_kependidikan = $setting->stat_tenaga_kependidikan ?? 0;
        $this->existingHeroImage        = $setting->hero_image;
        $this->existingFotoKepsek       = $setting->foto_kepsek;
    }

    public function save(): void
    {
        $this->validate();

        $setting = WebSetting::instance();

        $data = [
            'running_text'             => $this->running_text ?: null,
            'profil_singkat'           => $this->profil_singkat ?: null,
            'visi'                     => $this->visi ?: null,
            'misi'                     => $this->misi ?: null,
            'kutipan_kepsek'           => $this->kutipan_kepsek ?: null,
            'sambutan_kepsek'          => $this->sambutan_kepsek ?: null,
            'link_youtube'             => $this->link_youtube ?: null,
            'link_tiktok'              => $this->link_tiktok ?: null,
            'link_ig'                  => $this->link_ig ?: null,
            'link_fb'                  => $this->link_fb ?: null,
            'link_pengaduan'           => $this->link_pengaduan ?: null,
            'stat_tenaga_kependidikan' => $this->stat_tenaga_kependidikan,
        ];

        if ($this->hero_image) {
            $data['hero_image'] = $this->hero_image->store('web-profil', 'public');
        }
        if ($this->foto_kepsek) {
            $data['foto_kepsek'] = $this->foto_kepsek->store('web-profil', 'public');
        }

        $setting->update($data);
        $this->existingHeroImage  = $setting->fresh()->hero_image;
        $this->existingFotoKepsek = $setting->fresh()->foto_kepsek;
        $this->hero_image  = null;
        $this->foto_kepsek = null;

        session()->flash('success', 'Pengaturan web berhasil disimpan.');
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        return view('livewire.portal-web.pengaturan')->title('Pengaturan Web — Portal Web');
    }
}
