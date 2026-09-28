<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    {{-- Judul dinamis berdasarkan state --}}
    <title>
        @if($state === 'confirm') Konfirmasi Ijin Siswa
        @elseif($state === 'success') Berhasil Diproses
        @elseif($state === 'used') Sudah Diproses
        @elseif($state === 'expired') Link Kedaluwarsa
        @elseif($state === 'csrf_expired') Sesi Habis
        @elseif($state === 'error') Terjadi Kesalahan
        @else Link Tidak Valid
        @endif
    </title>

    {{-- Security Headers via meta --}}
    <meta http-equiv="X-Content-Type-Options" content="nosniff">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            max-width: 480px;
            width: 100%;
            overflow: hidden;
        }

        .card-header {
            padding: 1.5rem 1.75rem 1.25rem;
            border-bottom: 1px solid #e8ecf0;
        }

        .card-header .badge {
            display: inline-block;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: .3rem .75rem;
            border-radius: 999px;
            margin-bottom: .75rem;
        }
        .badge-blue   { background:#dbeafe; color:#1d4ed8; }
        .badge-green  { background:#dcfce7; color:#15803d; }
        .badge-red    { background:#fee2e2; color:#b91c1c; }
        .badge-yellow { background:#fef9c3; color:#92400e; }
        .badge-gray   { background:#f3f4f6; color:#4b5563; }

        .card-header h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.3;
        }

        .card-header p {
            margin-top: .4rem;
            font-size: .875rem;
            color: #64748b;
            line-height: 1.5;
        }

        .card-body { padding: 1.5rem 1.75rem; }

        .info-grid {
            display: grid;
            gap: .85rem;
        }

        .info-row {
            display: flex;
            gap: .75rem;
            align-items: flex-start;
        }

        .info-icon {
            flex-shrink: 0;
            width: 2rem;
            height: 2rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            font-size: .9rem;
        }

        .info-label {
            font-size: .7rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .info-value {
            font-size: .9rem;
            font-weight: 600;
            color: #1e293b;
            margin-top: .1rem;
        }

        .info-value.alasan {
            font-weight: 400;
            color: #374151;
            line-height: 1.5;
        }

        .divider {
            height: 1px;
            background: #e8ecf0;
            margin: 1.25rem 0;
        }

        .attachments-grid {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            margin-top: .4rem;
        }
        
        .attachment-link {
            display: block;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            transition: transform .2s, box-shadow .2s;
        }
        
        .attachment-link:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,.1);
            border-color: #cbd5e1;
        }
        
        .attachment-preview {
            width: 72px;
            height: 72px;
            object-fit: cover;
            display: block;
        }
        
        .attachment-doc {
            display: inline-flex;
            align-items: center;
            padding: .4rem .75rem;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: .75rem;
            font-weight: 500;
            text-decoration: none;
            color: #334155;
            transition: background .2s;
        }
        
        .attachment-doc:hover {
            background: #e2e8f0;
        }

        .btn-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
            margin-top: 1.25rem;
        }

        .btn {
            padding: .75rem 1rem;
            border-radius: 10px;
            font-size: .9rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: opacity .15s, transform .1s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
        }

        .btn:active { transform: scale(.97); }
        .btn:disabled { opacity: .55; cursor: not-allowed; transform: none; }

        .btn-approve {
            background: #16a34a;
            color: #fff;
        }
        .btn-approve:hover:not(:disabled) { background: #15803d; }

        .btn-reject {
            background: #fff;
            color: #dc2626;
            border: 1.5px solid #fca5a5;
        }
        .btn-reject:hover:not(:disabled) { background: #fff1f2; }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            margin-top: 1rem;
            padding: .6rem 1.1rem;
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            background: #fff;
            color: #475569;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s;
        }
        .btn-back:hover { background: #f8fafc; }

        .state-icon {
            font-size: 3rem;
            text-align: center;
            margin-bottom: .75rem;
        }

        .state-body {
            text-align: center;
            padding: .5rem 0 .25rem;
        }

        .state-body h2 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
        }

        .state-body p {
            margin-top: .5rem;
            font-size: .875rem;
            color: #64748b;
            line-height: 1.6;
        }

        .notice {
            margin-top: 1rem;
            padding: .75rem 1rem;
            border-radius: 10px;
            font-size: .8rem;
            line-height: 1.5;
            color: #92400e;
            background: #fef3c7;
            border: 1px solid #fde68a;
        }

        footer {
            padding: 1rem 1.75rem;
            border-top: 1px solid #e8ecf0;
            text-align: center;
            font-size: .72rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<div class="card">

    {{-- ================================================================
         STATE: confirm — tampilkan detail ijin dan tombol Setujui/Tolak
         ================================================================ --}}
    @if($state === 'confirm' && $record)

        @php
            $siswa  = $record->student;
            $kelas  = $siswa?->enrollmentAktif?->kelas?->name ?? '-';
            $jenis  = ucfirst($record->type);
            $mulai  = \Carbon\Carbon::parse($record->start_date)->translatedFormat('d F Y');
            $selesai= \Carbon\Carbon::parse($record->end_date)->translatedFormat('d F Y');
            $alasan = $record->reason ?? '-';
        @endphp

        <div class="card-header">
            <span class="badge badge-blue">📋 Permohonan Ijin</span>
            <h1>Konfirmasi Persetujuan</h1>
            <p>Silakan periksa detail permohonan ijin di bawah ini, lalu pilih tindakan.</p>
        </div>

        <div class="card-body">
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-icon">👤</div>
                    <div>
                        <div class="info-label">Nama Siswa</div>
                        <div class="info-value">{{ $siswa?->name ?? '-' }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon">🏫</div>
                    <div>
                        <div class="info-label">Kelas</div>
                        <div class="info-value">{{ $kelas }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon">📌</div>
                    <div>
                        <div class="info-label">Jenis</div>
                        <div class="info-value">{{ $jenis }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon">📅</div>
                    <div>
                        <div class="info-label">Tanggal</div>
                        <div class="info-value">{{ $mulai }}
                            @if($mulai !== $selesai) s/d {{ $selesai }} ({{ $record->duration_days }} hari)@endif
                        </div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon">💬</div>
                    <div>
                        <div class="info-label">Alasan</div>
                        <div class="info-value alasan">{{ $alasan }}</div>
                    </div>
                </div>
                
                @if(count($record->attachments) > 0)
                <div class="info-row" style="margin-top: .25rem;">
                    <div class="info-icon">📎</div>
                    <div>
                        <div class="info-label">Lampiran Bukti</div>
                        <div class="info-value attachments-grid">
                            @foreach($record->attachments as $att)
                                @php
                                    // Generate signed URL agar guru bisa melihat foto asli (valid 6 jam)
                                    $mediaUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                                        'ijin.media',
                                        now()->addHours(6),
                                        ['leaveRequestId' => $record->id, 'index' => $att['index']]
                                    );
                                @endphp
                                @if(str_starts_with($att['mime'], 'image/'))
                                    <a href="{{ $mediaUrl }}" target="_blank" class="attachment-link" title="Klik untuk perbesar">
                                        <img src="{{ $mediaUrl }}" alt="Lampiran {{ $att['name'] }}" class="attachment-preview" loading="lazy">
                                    </a>
                                @else
                                    <a href="{{ $mediaUrl }}" target="_blank" class="attachment-doc" title="Download dokumen">
                                        📄 {{ $att['name'] }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="divider"></div>

            <p style="font-size:.8rem;color:#64748b;margin-bottom:.25rem;">
                Sebagai Wali Kelas, pilih tindakan untuk permohonan ini:
            </p>

            {{-- Form Setujui --}}
            <form id="form-approve" method="POST"
                  action="{{ route('ijin.approval.action', ['token' => $token]) }}">
                @csrf
                <input type="hidden" name="action" value="approve">
            </form>

            {{-- Form Tolak --}}
            <form id="form-reject" method="POST"
                  action="{{ route('ijin.approval.action', ['token' => $token]) }}">
                @csrf
                <input type="hidden" name="action" value="reject">
            </form>

            <div class="btn-group">
                <button type="button" class="btn btn-approve" id="btn-approve"
                        onclick="submitAction('approve')">
                    ✅ Setujui
                </button>
                <button type="button" class="btn btn-reject" id="btn-reject"
                        onclick="submitAction('reject')">
                    ❌ Tolak
                </button>
            </div>

            <div class="notice">
                ⚠️ Tindakan ini akan langsung mengubah status ijin dan memengaruhi data presensi siswa.
            </div>
        </div>

    {{-- ================================================================
         STATE: success — aksi berhasil diproses
         ================================================================ --}}
    @elseif($state === 'success')

        <div class="card-header">
            @if(($action ?? '') === 'approve')
                <span class="badge badge-green">✅ Disetujui</span>
                <h1>Ijin Berhasil Disetujui</h1>
                <p>Data presensi siswa telah diperbarui secara otomatis.</p>
            @else
                <span class="badge badge-red">❌ Ditolak</span>
                <h1>Ijin Berhasil Ditolak</h1>
                <p>Siswa akan mendapat notifikasi WhatsApp mengenai keputusan ini.</p>
            @endif
        </div>

        <div class="card-body">
            <div class="state-body">
                <div class="state-icon">{{ ($action ?? '') === 'approve' ? '🎉' : '📩' }}</div>
                <p>Terima kasih. Permohonan ijin telah diproses. Halaman ini dapat ditutup.</p>
            </div>
        </div>

    {{-- ================================================================
         STATE: used — token sudah pernah dipakai / ijin sudah diproses
         ================================================================ --}}
    @elseif($state === 'used')

        <div class="card-header">
            <span class="badge badge-gray">🔒 Sudah Diproses</span>
            <h1>Link Sudah Digunakan</h1>
        </div>

        <div class="card-body">
            <div class="state-body">
                <div class="state-icon">🔁</div>
                <p>Link konfirmasi ini sudah pernah digunakan atau permohonan ijin sudah diproses melalui jalur lain.</p>
                <p style="margin-top:.75rem;">Untuk meninjau status ijin, silakan buka <strong>Portal Guru</strong>.</p>
            </div>
        </div>

    {{-- ================================================================
         STATE: expired — token kedaluwarsa (> 7 hari)
         ================================================================ --}}
    @elseif($state === 'expired')

        <div class="card-header">
            <span class="badge badge-yellow">⏰ Kedaluwarsa</span>
            <h1>Link Sudah Kedaluwarsa</h1>
        </div>

        <div class="card-body">
            <div class="state-body">
                <div class="state-icon">📭</div>
                <p>Link konfirmasi ini sudah tidak berlaku (lebih dari 7 hari sejak dikirim).</p>
                <p style="margin-top:.75rem;">Silakan proses permohonan ijin ini melalui <strong>Portal Guru</strong> atau hubungi Admin.</p>
            </div>
        </div>

    {{-- ================================================================
         STATE: invalid — token tidak ditemukan di database
         ================================================================ --}}
    @elseif($state === 'invalid')

        <div class="card-header">
            <span class="badge badge-gray">❓ Tidak Valid</span>
            <h1>Link Tidak Valid</h1>
        </div>

        <div class="card-body">
            <div class="state-body">
                <div class="state-icon">🚫</div>
                <p>Link konfirmasi yang Anda buka tidak valid atau tidak ditemukan dalam sistem.</p>
                <p style="margin-top:.75rem;">Pastikan link diakses langsung dari pesan WhatsApp tanpa modifikasi.</p>
            </div>
        </div>

    {{-- ================================================================
         STATE: csrf_expired — sesi CSRF habis (419)
         ================================================================ --}}
    @elseif($state === 'csrf_expired')

        <div class="card-header">
            <span class="badge badge-yellow">🔄 Sesi Habis</span>
            <h1>Sesi Habis, Muat Ulang</h1>
        </div>

        <div class="card-body">
            <div class="state-body">
                <div class="state-icon">⏳</div>
                <p>Sesi keamanan halaman ini telah habis. Hal ini biasa terjadi jika halaman dibiarkan terlalu lama atau dibuka melalui browser bawaan aplikasi.</p>
                <p style="margin-top:.75rem;">Muat ulang halaman lalu coba lagi.</p>
            </div>
            @if(!empty($backUrl))
                <div style="text-align:center;margin-top:1rem;">
                    <a href="{{ $backUrl }}" class="btn-back">🔄 Muat Ulang Halaman</a>
                </div>
            @endif
        </div>

    {{-- ================================================================
         STATE: error — exception / unexpected failure
         ================================================================ --}}
    @else

        <div class="card-header">
            <span class="badge badge-red">⚠️ Kesalahan</span>
            <h1>Terjadi Kesalahan</h1>
        </div>

        <div class="card-body">
            <div class="state-body">
                <div class="state-icon">🛠️</div>
                <p>Terjadi kesalahan saat memproses permintaan ini. Data ijin <strong>tidak berubah</strong> dan token masih dapat digunakan kembali.</p>
                <p style="margin-top:.75rem;">Silakan coba lagi dalam beberapa saat, atau proses melalui <strong>Portal Guru</strong>.</p>
            </div>
            @if(!empty($token))
                <div style="text-align:center;margin-top:1rem;">
                    <a href="{{ route('ijin.approval.show', ['token' => $token]) }}"
                       class="btn-back">🔄 Coba Lagi</a>
                </div>
            @endif
        </div>

    @endif

    <footer>
        Sistem Informasi Sekolah &mdash; Halaman ini hanya dapat diakses oleh penerima link resmi.
    </footer>
</div>

<script>
    var submitted = false;

    function submitAction(action) {
        if (submitted) return;

        // Konfirmasi khusus untuk aksi Tolak
        if (action === 'reject') {
            var ok = window.confirm(
                'Anda akan MENOLAK permohonan ijin ini.\n\n' +
                'Siswa akan mendapat notifikasi WhatsApp.\n\n' +
                'Lanjutkan?'
            );
            if (!ok) return;
        }

        submitted = true;

        // Disable kedua tombol agar tidak bisa diklik ganda
        var btnApprove = document.getElementById('btn-approve');
        var btnReject  = document.getElementById('btn-reject');
        if (btnApprove) { btnApprove.disabled = true; btnApprove.textContent = '⏳ Memproses...'; }
        if (btnReject)  { btnReject.disabled  = true; }

        document.getElementById('form-' + action).submit();
    }
</script>

</body>
</html>
