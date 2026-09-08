<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ Sekolah — {{ $sekolah?->school_name ?? 'Sekolah' }}</title>
    @php
        $favicon = $sekolah?->school_logo_path ? asset('storage/' . $sekolah->school_logo_path) : asset('favicon.ico');
    @endphp
    <link rel="icon" type="image/png" href="{{ $favicon }}">
    <meta name="description" content="Pusat Bantuan dan FAQ Resmi {{ $sekolah?->school_name ?? 'Sekolah' }}. Temukan jawaban mengenai pendaftaran SPMB, layanan administrasi, kegiatan, fasilitas, dan informasi sekolah.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @if($sekolah)
        <style>
            :root {
                @if($sekolah->theme_primary) --color-brand-primary: {{ $sekolah->theme_primary }}; @endif
                @if($sekolah->theme_secondary) --color-brand-secondary: {{ $sekolah->theme_secondary }}; @endif
            }
        </style>
    @endif
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-brand-primary-light selection:text-brand-primary-dark min-h-screen flex flex-col">

{{-- Navbar --}}
<x-public-dashboard.navbar :pengaturanSekolah="$sekolah" />

{{-- Hero Section (Sesuai Referensi Screenshot 2) --}}
<div class="pt-32 pb-24 relative overflow-hidden text-white border-b-4 border-amber-400 bg-gradient-to-br from-brand-primary via-blue-900 to-indigo-950">
    <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl translate-y-1/3 -translate-x-1/4 pointer-events-none"></div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <!-- Badge Pusat Bantuan -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-xs font-bold text-amber-300 uppercase tracking-widest mb-5 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
            PUSAT BANTUAN
        </div>

        <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold mb-4 tracking-tight leading-tight">
            FAQ <span class="text-amber-300">Sekolah</span>
        </h1>

        <p class="text-base sm:text-lg text-blue-100/90 font-medium leading-relaxed max-w-2xl mx-auto mb-6">
            Temukan jawaban mengenai layanan administrasi, kegiatan, fasilitas, dan informasi sekolah.
        </p>

        <!-- Breadcrumb -->
        <nav class="inline-flex items-center gap-2 text-xs text-blue-200/80 font-medium bg-black/20 backdrop-blur-md px-4 py-2 rounded-full border border-white/10">
            <a href="{{ route('beranda') }}" class="hover:text-amber-300 transition-colors">Beranda</a>
            <span><i class="fas fa-chevron-right text-[10px] opacity-60"></i></span>
            <a href="{{ route('beranda.layanan.index') }}" class="hover:text-amber-300 transition-colors">Layanan Publik</a>
            <span><i class="fas fa-chevron-right text-[10px] opacity-60"></i></span>
            <span class="text-amber-300 font-semibold">FAQ</span>
        </nav>
    </div>
</div>

{{-- Main Content Container (Overlapping Hero) --}}
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12 relative z-20 pb-20 w-full flex-grow"
     x-data="{
         search: '',
         selectedCategory: 'semua',
         openCategories: {},
         openQuestions: {},
         faqs: {{ Js::from($faqs) }},
         get filteredFaqs() {
             const q = this.search.toLowerCase().trim();
             return this.faqs.filter(item => {
                 const matchCat = (this.selectedCategory === 'semua') || (item.kategori === this.selectedCategory);
                 const matchQuery = !q || item.pertanyaan.toLowerCase().includes(q) || item.jawaban.toLowerCase().includes(q) || (item.kategori && item.kategori.toLowerCase().includes(q));
                 return matchCat && matchQuery;
             });
         },
         get groupedFaqs() {
             const groups = {};
             this.filteredFaqs.forEach(item => {
                 const cat = item.kategori || 'Umum';
                 if (!groups[cat]) groups[cat] = [];
                 groups[cat].push(item);
             });
             return groups;
         },
         get filteredCount() {
             return this.filteredFaqs.length;
         },
         toggleCategory(cat) {
             this.openCategories[cat] = !(this.openCategories[cat] ?? true);
         },
         isCategoryOpen(cat) {
             return this.openCategories[cat] ?? true;
         },
         toggleQuestion(id) {
             this.openQuestions[id] = !this.openQuestions[id];
         },
         isQuestionOpen(id) {
             return !!this.openQuestions[id];
         }
     }">

    <!-- Big Card Container -->
    <div class="bg-white rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden">

        <!-- Top Banner: Survei Kepuasan (Sesuai Referensi) -->
        <div class="p-5 sm:p-6 bg-slate-50/70 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h4 class="font-bold text-slate-800 text-sm sm:text-base">Tidak menemukan jawaban di sini?</h4>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Langsung beri penilaian layanan melalui survei kepuasan kami.</p>
            </div>
            <div class="shrink-0">
                @if($setting?->link_survei_kepuasan)
                    <a href="{{ $setting->link_survei_kepuasan }}" target="_blank" 
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm transition-all shadow-sm">
                        <i class="fas fa-clipboard-list"></i> Isi Survei Kepuasan
                    </a>
                @else
                    <a href="{{ route('beranda.layanan.index') }}" 
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm transition-all shadow-sm">
                        <i class="fas fa-headset"></i> Kontak Layanan
                    </a>
                @endif
            </div>
        </div>

        <!-- Search & Filter Section -->
        <div class="p-6 sm:p-8 border-b border-slate-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                <div>
                    <h2 class="text-lg sm:text-xl font-extrabold text-slate-900">Temukan jawaban yang Anda butuhkan dengan cepat.</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Filter berdasarkan kategori atau cari topik FAQ yang relevan.</p>
                </div>
                <div class="shrink-0">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                        <span x-text="filteredCount"></span> pertanyaan ditemukan
                    </span>
                </div>
            </div>

            <!-- Search Input -->
            <div class="relative mb-5">
                <input type="text" 
                       x-model="search" 
                       placeholder="Cari pertanyaan, kata kunci, atau kategori..." 
                       class="w-full pl-11 pr-10 py-3.5 rounded-2xl border border-slate-200 bg-slate-50/70 text-slate-900 text-sm focus:ring-2 focus:ring-brand-primary focus:bg-white focus:border-transparent outline-none transition-all shadow-xs">
                <div class="absolute left-4 top-4 text-slate-400">
                    <i class="fas fa-search text-base"></i>
                </div>
                <button type="button" 
                        x-show="search.length > 0" 
                        @click="search = ''" 
                        class="absolute right-3.5 top-3.5 text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-200 transition-colors" 
                        style="display: none;">
                    <i class="fas fa-times-circle text-sm"></i>
                </button>
            </div>

            <!-- Category Filter Pills -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar">
                <button type="button" 
                        @click="selectedCategory = 'semua'"
                        :class="selectedCategory === 'semua' ? 'bg-brand-primary text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                        class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition-colors">
                    Semua Kategori
                </button>
                @foreach($kategoris as $cat)
                <button type="button" 
                        @click="selectedCategory = '{{ $cat }}'"
                        :class="selectedCategory === '{{ $cat }}' ? 'bg-brand-primary text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                        class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition-colors">
                    {{ $cat }}
                </button>
                @endforeach
            </div>
        </div>

        <!-- FAQ Accordion List (Grouped by Category) -->
        <div class="p-6 sm:p-8 space-y-6">

            <!-- Empty State -->
            <template x-if="filteredCount === 0">
                <div class="py-12 text-center">
                    <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="far fa-question-circle"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Pertanyaan Tidak Ditemukan</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                        Maaf, tidak ada pertanyaan yang cocok dengan pencarian "<span class="font-semibold text-slate-700" x-text="search"></span>".
                    </p>
                    <button type="button" 
                            @click="search = ''; selectedCategory = 'semua'" 
                            class="mt-4 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                        Reset Pencarian
                    </button>
                </div>
            </template>

            <!-- Grouped FAQ List -->
            <template x-for="(items, categoryName) in groupedFaqs" :key="categoryName">
                <div class="border border-slate-200/80 rounded-2xl overflow-hidden bg-white shadow-xs transition-all">

                    <!-- Category Group Header (Collapsible) -->
                    <button type="button" 
                            @click="toggleCategory(categoryName)" 
                            class="w-full px-5 py-4 bg-slate-50/80 hover:bg-slate-100/80 border-b border-slate-200/60 flex items-center justify-between gap-3 text-left transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-brand-primary flex items-center justify-center shrink-0 text-xs font-bold">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm uppercase tracking-wide" x-text="categoryName"></h3>
                                <span class="text-[11px] text-slate-500" x-text="items.length + ' pertanyaan'"></span>
                            </div>
                        </div>
                        <div class="w-7 h-7 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400 text-xs transition-transform duration-300"
                             :class="isCategoryOpen(categoryName) ? 'rotate-180 text-brand-primary border-brand-200' : ''">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </button>

                    <!-- Questions inside Category -->
                    <div x-show="isCategoryOpen(categoryName)" x-collapse class="p-3 sm:p-4 space-y-3">
                        <template x-for="item in items" :key="item.id">
                            <div class="border border-slate-200/70 rounded-xl overflow-hidden bg-white hover:border-brand-primary/40 transition-all">
                                
                                <!-- Question Header -->
                                <button type="button" 
                                        @click="toggleQuestion(item.id)" 
                                        class="w-full text-left px-4 sm:px-5 py-3.5 flex items-center justify-between gap-4 transition-colors"
                                        :class="isQuestionOpen(item.id) ? 'bg-blue-50/40 text-brand-primary' : 'bg-white text-slate-800 hover:bg-slate-50/60'">
                                    <span class="font-bold text-xs sm:text-sm leading-snug" x-text="item.pertanyaan"></span>
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 transition-transform duration-300 text-xs"
                                         :class="isQuestionOpen(item.id) ? 'rotate-180 text-brand-primary' : 'text-slate-400'">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </button>

                                <!-- Answer Body -->
                                <div x-show="isQuestionOpen(item.id)" x-collapse style="display: none;">
                                    <div class="px-4 sm:px-5 pb-4 pt-2 border-t border-slate-100 bg-slate-50/40 text-xs sm:text-sm text-slate-600 leading-relaxed whitespace-pre-line" x-text="item.jawaban"></div>
                                </div>
                            </div>
                        </template>
                    </div>

                </div>
            </template>

        </div>

    </div>

</div>

{{-- Footer --}}
<x-public-dashboard.footer :pengaturanSekolah="$sekolah" />

@livewireScripts
</body>
</html>
