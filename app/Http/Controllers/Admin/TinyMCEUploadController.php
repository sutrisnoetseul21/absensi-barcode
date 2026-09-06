<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TinyMCEUploadController extends Controller
{
    /**
     * Handle image upload from TinyMCE editor.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|image|mimes:jpeg,png,jpg,gif,webp,svg|max:5120',
        ], [
            'file.required' => 'Tidak ada file gambar yang diunggah.',
            'file.image'    => 'File yang diunggah harus berupa gambar.',
            'file.mimes'    => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif, webp, svg.',
            'file.max'      => 'Ukuran gambar maksimal 5MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first('file'),
            ], 422);
        }

        try {
            $file = $request->file('file');
            $folder = $request->input('folder', 'web-profil/editor');
            
            // Simpan gambar ke disk public
            $path = $file->store($folder, 'public');

            return response()->json([
                'location' => asset('storage/' . $path),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal mengunggah gambar: ' . $e->getMessage(),
            ], 500);
        }
    }
}
