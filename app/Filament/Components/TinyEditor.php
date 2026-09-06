<?php

namespace App\Filament\Components;

use Closure;
use Filament\Forms\Components\Field;

class TinyEditor extends Field
{
    protected string $view = 'filament.forms.components.tiny-editor';

    protected int|Closure $height = 360;

    protected ?string $uploadDirectory = 'web-profil/editor';

    protected ?string $uploadUrl = null;

    protected bool|Closure $menubar = false;

    /**
     * Atur tinggi editor dalam piksel (px).
     */
    public function height(int|Closure $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getHeight(): int
    {
        return $this->evaluate($this->height);
    }

    /**
     * Atur folder tujuan upload gambar.
     */
    public function uploadDirectory(?string $directory): static
    {
        $this->uploadDirectory = $directory;

        return $this;
    }

    public function getUploadDirectory(): string
    {
        return $this->uploadDirectory ?? 'web-profil/editor';
    }

    /**
     * Atur URL endpoint upload gambar kustom.
     */
    public function uploadUrl(?string $url): static
    {
        $this->uploadUrl = $url;

        return $this;
    }

    public function getUploadUrl(): string
    {
        return $this->uploadUrl ?? route('admin.tinymce.upload');
    }

    /**
     * Tampilkan atau sembunyikan menubar TinyMCE.
     */
    public function menubar(bool|Closure $menubar = true): static
    {
        $this->menubar = $menubar;

        return $this;
    }

    public function getMenubar(): bool
    {
        return (bool) $this->evaluate($this->menubar);
    }
}
