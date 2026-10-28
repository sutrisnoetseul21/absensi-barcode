# Instruksi Dasar untuk AI Agent

> **Disimpan untuk User (Bapak Sutrisno)**
> *File ini berisi *template prompt* (perintah awal) yang bisa Bapak *copy-paste* saat membuka sesi percakapan baru dengan AI Agent (Gemini/ChatGPT/Claude dll) agar AI tersebut langsung memahami konteks proyek ERP kita.*

---

## 📝 Template Prompt Pembuka (Copy-Paste)

Setiap kali memulai obrolan baru untuk mengerjakan fitur pada proyek ini, silakan gunakan *prompt* berikut:

```text
Halo, kita akan mengembangkan fitur [SEBUTKAN_FITUR_YANG_INGIN_DIBUAT] untuk sistem ERP ini. 

Sebagai langkah awal yang WAJIB:
1. Tolong baca `docs/README.md` secara utuh.
2. Baca file blueprint yang relevan di folder `docs/blueprint/`.
3. Pahami skema relasi khusus di `docs/penjelasan-relasi-data.md`.

Setelah memahami aturan main (Loose Coupling & Multi-Guard) dan arsitektur proyek ini, tolong diskusikan dan buatkan rencana implementasinya (Implementation Plan).
Jangan lakukan perubahan kode apa pun sebelum rencana tersebut saya setujui!
```

---

## 🤖 Aturan Baku Sistem (Untuk Dibaca AI)

Jika kamu adalah AI yang sedang membaca dokumen ini, kamu terikat pada aturan berikut:
1. **Dilarang Merusak Arsitektur:** Jangan menyarankan kolom *Foreign Key* langsung ke entitas master (seperti menaruh `class_id` di tabel `students`) jika hal itu melanggar aturan riwayat data. Gunakan tabel pivot (seperti `student_enrollments` atau `pengajarans`).
2. **Perhatikan Multi-Guard Auth:** Ingat bahwa Filament kita menggunakan struktur multi-panel dan multi-guard (`web`, `wali_kelas`, `siswa`).
3. **Dokumentasikan Progres:** Segala pencatatan penyelesaian fitur atau *walkthrough* pengerjaan HARUS ditulis/disimpan ke dalam folder `docs/progres-development/`. 
4. **Peringatan Hapus Data:** Jangan pernah melakukan *Hard Delete* untuk data yang memiliki relasi transaksional. Selalu perhatikan aturan *Interactive Blocking Notification* di resource Filament (memblokir penghapusan jika data punya relasi).
