<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baca Online: {{ $buku->judul }} - Perpustakaan</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0f172a; color: #f1f5f9; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }

        .reader-header {
            background: #1e293b;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-shrink: 0;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 8px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.2s;
            flex-shrink: 0;
        }
        .back-btn:hover { background: rgba(255,255,255,0.12); color: #f1f5f9; }
        .book-info { flex: 1; min-width: 0; }
        .book-title { font-size: 15px; font-weight: 700; color: #f1f5f9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .book-meta { font-size: 12px; color: #64748b; margin-top: 1px; }
        .badge-pdf {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(16,185,129,0.15);
            border: 1px solid rgba(16,185,129,0.3);
            color: #34d399;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            flex-shrink: 0;
        }

        .reader-toolbar {
            background: #1e293b;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding: 8px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-shrink: 0;
        }
        .toolbar-group { display: flex; align-items: center; gap: 8px; }
        .tool-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        .tool-btn:hover { background: rgba(255,255,255,0.12); color: #f1f5f9; }
        .page-info { font-size: 12px; color: #64748b; display: flex; align-items: center; gap: 6px; }
        .page-input {
            width: 48px;
            text-align: center;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 6px;
            color: #f1f5f9;
            font-size: 12px;
            padding: 4px 6px;
            outline: none;
        }
        .zoom-info { font-size: 12px; color: #64748b; min-width: 44px; text-align: center; }

        .pdf-container {
            flex: 1;
            overflow: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 16px;
            gap: 12px;
            background: #0f172a;
            position: relative;
        }
        #pdf-canvas-wrapper { display: flex; flex-direction: column; align-items: center; gap: 12px; }
        .pdf-page-wrapper {
            box-shadow: 0 8px 40px rgba(0,0,0,0.6);
            border-radius: 4px;
            overflow: hidden;
            background: white;
        }
        canvas { display: block; }

        .loading-overlay {
            position: absolute;
            inset: 0;
            background: #0f172a;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }
        .spinner {
            width: 40px; height: 40px;
            border: 3px solid rgba(99,102,241,0.2);
            border-top-color: #6366f1;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-text { color: #64748b; font-size: 14px; }

        .error-state {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            gap: 12px;
            text-align: center;
        }
        .error-state svg { width: 48px; height: 48px; opacity: 0.4; color: #ef4444; }
        .error-state h3 { font-size: 16px; color: #f87171; }
        .error-state p { font-size: 13px; color: #64748b; }
        .open-link { margin-top: 8px; color: #6366f1; font-size: 13px; text-decoration: underline; }
    </style>
</head>
<body>

    <div class="reader-header">
        <a href="{{ url('/perpustakaan') }}" class="back-btn">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
        <div class="book-info">
            <div class="book-title">{{ $buku->judul }}</div>
            <div class="book-meta">
                {{ $buku->penulis ?? 'Penulis tidak diketahui' }}
                @if($buku->penerbit) &bull; {{ $buku->penerbit }} @endif
                @if($buku->tahun_terbit) &bull; {{ $buku->tahun_terbit }} @endif
            </div>
        </div>
        <div class="badge-pdf">
            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            E-Book
        </div>
    </div>

    <div class="reader-toolbar">
        <div class="toolbar-group">
            <button class="tool-btn" id="prev-page" title="Halaman Sebelumnya">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <div class="page-info">
                <input type="number" class="page-input" id="page-num-input" min="1" value="1">
                <span>/ <span id="total-pages">-</span></span>
            </div>
            <button class="tool-btn" id="next-page" title="Halaman Berikutnya">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
        <div class="toolbar-group">
            <button class="tool-btn" id="zoom-out" title="Perkecil">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/></svg>
            </button>
            <span class="zoom-info" id="zoom-level">100%</span>
            <button class="tool-btn" id="zoom-in" title="Perbesar">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
            </button>
        </div>
        <div class="toolbar-group">
            <a href="{{ asset('storage/' . $buku->file_pdf) }}" target="_blank" class="tool-btn" title="Buka di Tab Baru">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </div>

    <div class="pdf-container" id="pdf-container">
        <div class="loading-overlay" id="loading-overlay">
            <div class="spinner"></div>
            <div class="loading-text">Memuat dokumen...</div>
        </div>
        <div class="error-state" id="error-state">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <h3>Gagal memuat PDF</h3>
            <p>Dokumen tidak dapat ditampilkan.<br>Coba buka langsung di tab baru.</p>
            <a class="open-link" href="{{ asset('storage/' . $buku->file_pdf) }}" target="_blank">Buka Langsung →</a>
        </div>
        <div id="pdf-canvas-wrapper"></div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        const pdfUrl = "{{ asset('storage/' . $buku->file_pdf) }}";
        let pdfDoc = null, currentPage = 1, totalPages = 0, scale = 1.3, rendering = false;

        const $loading = document.getElementById('loading-overlay');
        const $error   = document.getElementById('error-state');
        const $wrapper = document.getElementById('pdf-canvas-wrapper');
        const $pageInput = document.getElementById('page-num-input');
        const $totalPages = document.getElementById('total-pages');
        const $zoom = document.getElementById('zoom-level');

        pdfjsLib.getDocument({
            url: pdfUrl,
            cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
            cMapPacked: true
        }).promise.then(pdf => {
            pdfDoc = pdf;
            totalPages = pdf.numPages;
            $totalPages.textContent = totalPages;
            $pageInput.max = totalPages;
            $loading.style.display = 'none';
            renderPage(1);
        }).catch(() => {
            $loading.style.display = 'none';
            $error.style.display = 'flex';
        });

        async function renderPage(num) {
            if (rendering) return;
            rendering = true;
            const page = await pdfDoc.getPage(num);
            const vp = page.getViewport({ scale });
            $wrapper.innerHTML = '';
            const pw = document.createElement('div');
            pw.className = 'pdf-page-wrapper';
            const c = document.createElement('canvas');
            c.width = vp.width; c.height = vp.height;
            pw.appendChild(c); $wrapper.appendChild(pw);
            await page.render({ canvasContext: c.getContext('2d'), viewport: vp }).promise;
            document.getElementById('pdf-container').scrollTo({ top: 0, behavior: 'smooth' });
            rendering = false;
        }

        document.getElementById('prev-page').onclick = () => {
            if (currentPage <= 1) return;
            $pageInput.value = --currentPage;
            renderPage(currentPage);
        };
        document.getElementById('next-page').onclick = () => {
            if (currentPage >= totalPages) return;
            $pageInput.value = ++currentPage;
            renderPage(currentPage);
        };
        $pageInput.onchange = () => {
            let p = Math.max(1, Math.min(parseInt($pageInput.value)||1, totalPages));
            $pageInput.value = currentPage = p;
            renderPage(p);
        };
        document.getElementById('zoom-in').onclick = () => {
            if (scale >= 3) return;
            scale = Math.round((scale + 0.25)*100)/100;
            $zoom.textContent = Math.round(scale*100)+'%';
            renderPage(currentPage);
        };
        document.getElementById('zoom-out').onclick = () => {
            if (scale <= 0.5) return;
            scale = Math.round((scale - 0.25)*100)/100;
            $zoom.textContent = Math.round(scale*100)+'%';
            renderPage(currentPage);
        };
        document.addEventListener('keydown', e => {
            if (e.key==='ArrowRight'||e.key==='ArrowDown') document.getElementById('next-page').click();
            if (e.key==='ArrowLeft'||e.key==='ArrowUp') document.getElementById('prev-page').click();
            if (e.key==='+'||e.key==='=') document.getElementById('zoom-in').click();
            if (e.key==='-') document.getElementById('zoom-out').click();
        });
    </script>
</body>
</html>
