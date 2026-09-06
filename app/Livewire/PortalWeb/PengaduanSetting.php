<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Models\PengaduanSetting as ModelPengaduanSetting;
use App\Models\WebSetting;

class PengaduanSetting extends Component
{
    use WithFileUploads;

    // Formulir Aspirasi & Pengaduan
    public string $module_name = '';
    public string $banner_title = '';
    public string $banner_text = '';
    public bool $is_active = true;

    // Dokumen & Kanal Terintegrasi Layanan Publik
    public $gambar_visi_misi_pelayanan = null;
    public $gambar_maklumat_pelayanan = null;
    public ?string $existingGambarVisiMisi = null;
    public ?string $existingGambarMaklumat = null;
    public string $link_sp4n_lapor = '';
    public string $link_cariyanlik = '';
    public string $link_pengaduan_daerah = '';
    public string $link_survei_kepuasan = '';

    protected function rules(): array
    {
        return [
            'module_name'                => 'required|string|max:100',
            'banner_title'               => 'required|string|max:255',
            'banner_text'                => 'nullable|string',
            'is_active'                  => 'boolean',
            'gambar_visi_misi_pelayanan' => 'nullable|image|max:5120',
            'gambar_maklumat_pelayanan'  => 'nullable|image|max:5120',
            'link_sp4n_lapor'            => 'nullable|url|max:500',
            'link_cariyanlik'            => 'nullable|url|max:500',
            'link_pengaduan_daerah'      => 'nullable|url|max:500',
            'link_survei_kepuasan'       => 'nullable|url|max:500',
        ];
    }

    public function mount(): void
    {
        $setting = ModelPengaduanSetting::instance();
        $this->module_name  = $setting->module_name;
        $this->banner_title = $setting->banner_title;
        $this->banner_text  = $setting->banner_text ?? '';
        $this->is_active    = (bool) $setting->is_active;

        $web = WebSetting::instance();
        $this->existingGambarVisiMisi = $web->gambar_visi_misi_pelayanan;
        $this->existingGambarMaklumat = $web->gambar_maklumat_pelayanan;
        $this->link_sp4n_lapor        = $web->link_sp4n_lapor ?? '';
        $this->link_cariyanlik        = $web->link_cariyanlik ?? '';
        $this->link_pengaduan_daerah  = $web->link_pengaduan_daerah ?? '';
        $this->link_survei_kepuasan   = $web->link_survei_kepuasan ?? '';
    }

    public function save(): void
    {
        $this->validate();

        $setting = ModelPengaduanSetting::instance();
        $setting->update([
            'module_name'  => $this->module_name,
            'banner_title' => $this->banner_title,
            'banner_text'  => $this->banner_text ?: null,
            'is_active'    => $this->is_active,
        ]);

        $web = WebSetting::instance();
        $webData = [
            'link_sp4n_lapor'       => $this->link_sp4n_lapor ?: null,
            'link_cariyanlik'       => $this->link_cariyanlik ?: null,
            'link_pengaduan_daerah' => $this->link_pengaduan_daerah ?: null,
            'link_survei_kepuasan'  => $this->link_survei_kepuasan ?: null,
        ];

        if ($this->gambar_visi_misi_pelayanan) {
            $webData['gambar_visi_misi_pelayanan'] = $this->gambar_visi_misi_pelayanan->store('web-profil', 'public');
        }
        if ($this->gambar_maklumat_pelayanan) {
            $webData['gambar_maklumat_pelayanan'] = $this->gambar_maklumat_pelayanan->store('web-profil', 'public');
        }

        $web->update($webData);
        $fresh = $web->fresh();
        $this->existingGambarVisiMisi = $fresh->gambar_visi_misi_pelayanan;
        $this->existingGambarMaklumat = $fresh->gambar_maklumat_pelayanan;
        $this->gambar_visi_misi_pelayanan = null;
        $this->gambar_maklumat_pelayanan = null;

        session()->flash('success', 'Pengaturan layanan berhasil disimpan.');
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        return view('livewire.portal-web.pengaduan-setting')
            ->title('Pengaturan Layanan — Portal Web');
    }
}
