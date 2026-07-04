<div class="space-y-6" x-data="{ 
    showNewStudentForm: false,
    draggedStudentId: null
}">
    <!-- Inline Premium CSS (mencegah tailwind purging) -->
    <style>
        .rombel-header-summary {
            padding: 1.25rem;
            background-color: #18181b; /* zinc-900 */
            border: 1px solid #27272a; /* zinc-800 */
            border-radius: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .light .rombel-header-summary, [data-theme="light"] .rombel-header-summary {
            background-color: #f4f4f5;
            border-color: #e4e4e7;
        }

        .rombel-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-top: 1rem;
        }
        @media (min-width: 1024px) {
            .rombel-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .rombel-pane {
            background-color: #09090b; /* zinc-950 */
            border: 1px solid #27272a; /* zinc-800 */
            border-radius: 0.75rem;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            height: 540px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .light .rombel-pane, [data-theme="light"] .rombel-pane {
            background-color: #ffffff;
            border-color: #e4e4e7;
        }

        .rombel-pane-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .rombel-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .light .rombel-title, [data-theme="light"] .rombel-title {
            color: #18181b;
        }

        .rombel-dot {
            width: 0.625rem;
            height: 0.625rem;
            border-radius: 9999px;
        }
        .dot-green { background-color: #10b981; }
        .dot-blue { background-color: #3b82f6; }

        .rombel-search-wrapper {
            position: relative;
            width: 12rem;
        }
        .rombel-search-input {
            width: 100%;
            background-color: #18181b !important;
            border: 1px solid #27272a !important;
            border-radius: 0.5rem !important;
            padding: 0.375rem 2rem 0.375rem 0.75rem !important;
            font-size: 0.75rem !important;
            color: #ffffff !important;
        }
        .light .rombel-search-input, [data-theme="light"] .rombel-search-input {
            background-color: #ffffff !important;
            border-color: #e4e4e7 !important;
            color: #18181b !important;
        }
        .rombel-search-icon {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: #52525b;
            pointer-events: none;
        }

        .rombel-scrollable {
            flex: 1;
            overflow-y: auto;
            border: 1px solid rgba(39, 39, 42, 0.4);
            border-radius: 0.5rem;
            background-color: rgba(24, 24, 27, 0.2);
            padding-right: 2px;
        }
        .light .rombel-scrollable, [data-theme="light"] .rombel-scrollable {
            background-color: rgba(244, 244, 245, 0.4);
            border-color: #e4e4e7;
        }

        .rombel-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .rombel-th {
            padding: 0.5rem 0.75rem;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #71717a;
            border-bottom: 1px solid #27272a;
            letter-spacing: 0.05em;
        }
        .light .rombel-th, [data-theme="light"] .rombel-th {
            border-bottom-color: #e4e4e7;
        }

        .rombel-tr {
            border-bottom: 1px solid rgba(39, 39, 42, 0.3);
            transition: all 0.2s;
            cursor: grab;
        }
        .rombel-tr:hover {
            background-color: rgba(39, 39, 42, 0.15);
        }
        .light .rombel-tr:hover, [data-theme="light"] .rombel-tr:hover {
            background-color: rgba(228, 228, 231, 0.4);
        }
        .rombel-tr:active {
            cursor: grabbing;
        }

        .rombel-td {
            padding: 0.625rem 0.75rem;
            font-size: 0.75rem;
            vertical-align: middle;
        }
        .rombel-td-name {
            color: #ffffff;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .light .rombel-td-name, [data-theme="light"] .rombel-td-name {
            color: #18181b;
        }
        .rombel-td-nisn {
            color: #a1a1aa;
            font-family: monospace;
        }
        .light .rombel-td-nisn, [data-theme="light"] .rombel-td-nisn {
            color: #52525b;
        }

        /* Action Buttons */
        .btn-move {
            padding: 0.25rem;
            border-radius: 0.375rem;
            border: 1px solid transparent;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-move-left {
            background-color: rgba(59, 130, 246, 0.1);
            color: #60a5fa;
        }
        .btn-move-left:hover {
            background-color: #3b82f6;
            color: #ffffff;
        }
        .btn-move-right {
            background-color: rgba(239, 68, 68, 0.1);
            color: #f87171;
        }
        .btn-move-right:hover {
            background-color: #ef4444;
            color: #ffffff;
        }

        .rombel-btn-add {
            background-color: #2563eb;
            color: #ffffff;
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.7rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        .rombel-btn-add:hover {
            background-color: #1d4ed8;
        }

        /* Inline Student Registration Form */
        .rombel-form-add {
            margin-bottom: 1rem;
            padding: 1rem;
            border: 1px solid #27272a;
            border-radius: 0.5rem;
            background-color: rgba(24, 24, 27, 0.5);
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .light .rombel-form-add, [data-theme="light"] .rombel-form-add {
            background-color: #f4f4f5;
            border-color: #e4e4e7;
        }
        .rombel-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }
        .rombel-form-label {
            display: block;
            font-size: 0.65rem;
            font-weight: 700;
            color: #a1a1aa;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
        }
        .light .rombel-form-label, [data-theme="light"] .rombel-form-label {
            color: #52525b;
        }
        .rombel-form-input {
            width: 100%;
            background-color: #18181b !important;
            border: 1px solid #27272a !important;
            border-radius: 0.375rem !important;
            padding: 0.375rem 0.5rem !important;
            font-size: 0.75rem !important;
            color: #ffffff !important;
        }
        .light .rombel-form-input, [data-theme="light"] .rombel-form-input {
            background-color: #ffffff !important;
            border-color: #e4e4e7 !important;
            color: #18181b !important;
        }
        .rombel-form-radio-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Badges */
        .rombel-badge {
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 700;
            border: 1px solid transparent;
        }
        .rombel-badge-green {
            background-color: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }
        .rombel-badge-blue {
            background-color: rgba(59, 130, 246, 0.1);
            border-color: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }

        /* Empty State */
        .rombel-empty {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 1.5rem;
            color: #71717a;
        }
        
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #27272a;
            border-radius: 9999px;
        }
        .light .custom-scrollbar::-webkit-scrollbar-thumb, [data-theme="light"] .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e4e4e7;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #3f3f46;
        }
    </style>

    <!-- Header Summary -->
    <div class="rombel-header-summary">
        <div>
            <h3 class="text-base font-bold text-white dark:text-white" style="margin: 0; font-size: 1.1rem; line-height: 1.4;">Manajemen Rombel: Kelas {{ $kelas->name }}</h3>
            <p class="text-xs text-zinc-400" style="margin: 0.1rem 0 0 0;">Tahun Ajaran: <strong class="text-zinc-200">{{ $academicYear->name ?? '—' }}</strong></p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <span class="rombel-badge rombel-badge-green">
                {{ count($leftStudents) }} Siswa Terdaftar
            </span>
            <span class="rombel-badge rombel-badge-blue">
                {{ count($rightStudents) }} Siswa Tanpa Kelas
            </span>
        </div>
    </div>

    <!-- Dual Pane Grid -->
    <div class="rombel-grid">
        
        <!-- PANEL KIRI: Anggota Kelas -->
        <div class="rombel-pane"
             @dragover.prevent
             @drop="let id = event.dataTransfer.getData('student_id'); if (id) $wire.enrollStudent(id)">
            
            <div class="rombel-pane-header">
                <h4 class="rombel-title">
                    <span class="rombel-dot dot-green"></span>
                    Siswa Kelas Ini ({{ count($leftStudents) }})
                </h4>
                <div class="rombel-search-wrapper">
                    <input type="text" 
                           wire:model.live.debounce.300ms="searchLeft" 
                           placeholder="Cari anggota..." 
                           class="rombel-search-input" />
                    <span class="rombel-search-icon">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Scrollable Area -->
            <div class="rombel-scrollable custom-scrollbar">
                @if(count($leftStudents) > 0)
                    <table class="rombel-table">
                        <thead>
                            <tr>
                                <th class="rombel-th">Nama Siswa</th>
                                <th class="rombel-th">NISN</th>
                                <th class="rombel-th" style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leftStudents as $siswa)
                                <tr draggable="true" 
                                    @dragstart="event.dataTransfer.setData('student_id', '{{ $siswa->id }}')"
                                    class="rombel-tr">
                                    <td class="rombel-td rombel-td-name">
                                        <!-- Drag Indicator -->
                                        <svg class="w-3.5 h-3.5 text-zinc-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                        {{ $siswa->name }}
                                    </td>
                                    <td class="rombel-td rombel-td-nisn">{{ $siswa->nisn }}</td>
                                    <td class="rombel-td" style="text-align: right;">
                                        <button type="button" 
                                                wire:click="unenrollStudent('{{ $siswa->id }}')" 
                                                title="Keluarkan dari kelas"
                                                class="btn-move btn-move-right">
                                            <svg style="width: 1rem; height: 1rem;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="rombel-empty">
                        <svg style="width: 2.75rem; height: 2.75rem; color: #3f3f46; margin-bottom: 0.5rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                        <p style="font-size: 0.85rem; font-weight: 700; color: #a1a1aa; margin: 0;">Belum Ada Anggota</p>
                        <p style="font-size: 0.65rem; color: #52525b; max-w: 220px; margin: 0.25rem 0 0 0;">Tarik siswa dari panel kanan ke sini atau klik tombol panah untuk memasukkan anggota.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- PANEL KANAN: Siswa Tanpa Kelas -->
        <div class="rombel-pane"
             @dragover.prevent
             @drop="let id = event.dataTransfer.getData('student_id'); if (id) $wire.unenrollStudent(id)">
            
            <div class="rombel-pane-header">
                <h4 class="rombel-title">
                    <span class="rombel-dot dot-blue"></span>
                    Siswa Tanpa Kelas ({{ count($rightStudents) }})
                </h4>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <!-- Button Tambah Siswa Baru (Buka sub form) -->
                    <button type="button" 
                            @click="showNewStudentForm = !showNewStudentForm" 
                            class="rombel-btn-add">
                        <svg style="width: 0.875rem; height: 0.875rem;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5H4.5" />
                        </svg>
                        + Siswa Baru
                    </button>
                    
                    <div class="rombel-search-wrapper" style="width: 9rem;">
                        <input type="text" 
                               wire:model.live.debounce.300ms="searchRight" 
                               placeholder="Cari siswa..." 
                               class="rombel-search-input" />
                        <span class="rombel-search-icon">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Collapsible Form Tambah Siswa Baru -->
            <div x-show="showNewStudentForm" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-4"
                 class="rombel-form-add">
                <h5 style="font-size: 0.7rem; font-weight: 700; color: #ffffff; text-transform: uppercase; margin: 0 0 0.25rem 0;">Daftar Siswa Baru (Masuk ke Sisi Kanan)</h5>
                
                <div class="rombel-form-grid">
                    <div>
                        <label class="rombel-form-label">Nama Lengkap</label>
                        <input type="text" wire:model="newStudentName" class="rombel-form-input" />
                        @error('newStudentName') <span style="font-size: 0.65rem; color: #f87171; display: block; margin-top: 0.2rem;">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="rombel-form-label">NISN</label>
                        <input type="text" wire:model="newStudentNisn" class="rombel-form-input" style="font-family: monospace;" />
                        @error('newStudentNisn') <span style="font-size: 0.65rem; color: #f87171; display: block; margin-top: 0.2rem;">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.25rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div class="rombel-form-radio-group">
                        <label class="inline-flex items-center" style="cursor: pointer; font-size: 0.7rem; color: #a1a1aa;">
                            <input type="radio" wire:model="newStudentGender" value="L" style="background-color: #18181b; border-color: #27272a; color: #2563eb;" />
                            <span style="margin-left: 0.25rem;">Laki-laki</span>
                        </label>
                        <label class="inline-flex items-center" style="cursor: pointer; font-size: 0.7rem; color: #a1a1aa;">
                            <input type="radio" wire:model="newStudentGender" value="P" style="background-color: #18181b; border-color: #27272a; color: #2563eb;" />
                            <span style="margin-left: 0.25rem;">Perempuan</span>
                        </label>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" @click="showNewStudentForm = false" style="background: none; border: none; font-size: 0.7rem; font-weight: 700; color: #a1a1aa; cursor: pointer; padding: 0.25rem 0.5rem;">Batal</button>
                        <button type="button" wire:click="registerNewStudent" class="rombel-btn-add" style="padding: 0.25rem 0.75rem;">Simpan</button>
                    </div>
                </div>
            </div>

            <!-- Scrollable Area -->
            <div class="rombel-scrollable custom-scrollbar">
                @if(count($rightStudents) > 0)
                    <table class="rombel-table">
                        <thead>
                            <tr>
                                <th class="rombel-th">Aksi</th>
                                <th class="rombel-th">Nama Siswa</th>
                                <th class="rombel-th">NISN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rightStudents as $siswa)
                                <tr draggable="true" 
                                    @dragstart="event.dataTransfer.setData('student_id', '{{ $siswa->id }}')"
                                    class="rombel-tr">
                                    <td class="rombel-td">
                                        <button type="button" 
                                                wire:click="enrollStudent('{{ $siswa->id }}')" 
                                                title="Masukkan ke kelas"
                                                class="btn-move btn-move-left">
                                            <svg style="width: 1rem; height: 1rem;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                            </svg>
                                        </button>
                                    </td>
                                    <td class="rombel-td rombel-td-name">
                                        <!-- Drag Indicator -->
                                        <svg class="w-3.5 h-3.5 text-zinc-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                        {{ $siswa->name }}
                                    </td>
                                    <td class="rombel-td rombel-td-nisn">{{ $siswa->nisn }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="rombel-empty">
                        <svg style="width: 2.75rem; height: 2.75rem; color: #3f3f46; margin-bottom: 0.5rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p style="font-size: 0.85rem; font-weight: 700; color: #a1a1aa; margin: 0;">Tidak Ada Siswa</p>
                        <p style="font-size: 0.65rem; color: #52525b; max-w: 220px; margin: 0.25rem 0 0 0;">Semua siswa aktif sudah tuntas terdaftar dalam rombel untuk tahun ajaran terpilih ini.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
