<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    public function uploadImage(UploadedFile $image, string $folder = 'restaurants'): string
    {
        // Generar un nombre único para la imagen
        $fileName = Str::uuid() . '.' . $image->getClientOriginalExtension();
        
        // Guardar la imagen en el storage
        $path = $image->storeAs($folder, $fileName, 'public');
        
        return $path;
    }

    public function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
} 