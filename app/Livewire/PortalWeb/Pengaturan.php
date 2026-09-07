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
    public int $stat_tenaga_kependidikan = 0;

    public string $posisi_foto_kepsek = 'top';

    public $hero_image = null;
    public $foto_kepsek = null;
    public $struktur_organisasi = null;
    public ?string $existingHeroImage = null;
    public ?string $existingFotoKepsek = null;
    public ?string $existingStrukturOrganisasi = null;

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
            'stat_tenaga_kependidikan' => 'integer|min:0',
            'posisi_foto_kepsek'       => 'nullable|string|max:50',
            'hero_image'               => 'nullable|image|max:5120',
            'foto_kepsek'              => 'nullable|image|max:2048',
            'struktur_organisasi'      => 'nullable|image|max:5120',
        ];
    }

    public function mount(): void
    {
        $setting = WebSetting::instance();
        $this->running_text                 = $setting->running_text ?? '';
        $this->profil_singkat               = $setting->profil_singkat ?? '';
        $this->visi                         = $setting->visi ?? '';
        $this->misi                         = $setting->misi ?? '';
        $this->kutipan_kepsek               = $setting->kutipan_kepsek ?? '';
        $this->sambutan_kepsek              = $setting->sambutan_kepsek ?? '';
        $this->link_youtube                 = $setting->link_youtube ?? '';
        $this->link_tiktok                  = $setting->link_tiktok ?? '';
        $this->link_ig                      = $setting->link_ig ?? '';
        $this->link_fb                      = $setting->link_fb ?? '';
        $this->stat_tenaga_kependidikan     = $setting->stat_tenaga_kependidikan ?? 0;
        $this->posisi_foto_kepsek           = $setting->posisi_foto_kepsek ?? 'top';
        $this->existingHeroImage            = $setting->hero_image;
        $this->existingFotoKepsek           = $setting->foto_kepsek;
        $this->existingStrukturOrganisasi   = $setting->struktur_organisasi;
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
            'stat_tenaga_kependidikan' => $this->stat_tenaga_kependidikan,
            'posisi_foto_kepsek'       => $this->posisi_foto_kepsek ?: 'top',
        ];

        if ($this->hero_image) {
            $data['hero_image'] = $this->hero_image->store('web-profil', 'public');
        }
        if ($this->foto_kepsek) {
            $data['foto_kepsek'] = $this->foto_kepsek->store('web-profil', 'public');
        }
        if ($this->struktur_organisasi) {
            $data['struktur_organisasi'] = $this->struktur_organisasi->store('web-profil', 'public');
        }

        $setting->update($data);
        $fresh = $setting->fresh();
        $this->existingHeroImage          = $fresh->hero_image;
        $this->existingFotoKepsek         = $fresh->foto_kepsek;
        $this->existingStrukturOrganisasi = $fresh->struktur_organisasi;
        $this->hero_image          = null;
        $this->foto_kepsek         = null;
        $this->struktur_organisasi = null;

        session()->flash('success', 'Pengaturan web berhasil disimpan.');
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        return view('livewire.portal-web.pengaturan')->title('Pengaturan Web — Portal Web');
    }
}
