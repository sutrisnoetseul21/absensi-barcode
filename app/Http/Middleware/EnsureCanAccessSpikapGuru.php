<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanAccessSpikapGuru
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('web')->check()) {
            return redirect('/portal-guru/login');
        }

        $user = Auth::user();

        // 1. Cek role khusus dengan hak akses langsung: Super Admin, SPIKAP Admin, Guru BK, Kepala Sekolah (via role atau jabatan)
        $hasDirectRole = $user->hasAnyRole([
            'super_admin',
            'spikap_admin',
            'spikap_guru_bk',
            'spikap_kepala_sekolah',
        ]) || $user->isGuruBk() || $user->isKepalaSekolah();

        // 2. Cek Wali Kelas (wajib memiliki record teacher/guru aktif)
        $isWaliKelasWithTeacher = $user->hasAnyRole(['spikap_wali_kelas', 'wali_kelas']) && $user->teacher !== null;

        if (!$hasDirectRole && !$isWaliKelasWithTeacher) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses modul SPIKAP Guru.');
        }

        if ($user && $user->must_change_password) {
            if (!$request->is('portal-guru/ganti-password') && !$request->is('portal-guru/logout')) {
                return redirect('/portal-guru/ganti-password');
            }
        }

        return $next($request);
    }
}
