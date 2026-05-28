<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadService
{
    public function __construct(
        private string $uploadDir,
    ) {}

    public function upload(UploadedFile $file, string $prefix = 'carte'): string
    {
        $extension = $file->guessExtension() ?? 'png';
        $filename = $prefix . '_' . uniqid() . '.' . $extension;

        $file->move($this->uploadDir, $filename);

        return $filename;
    }

    public function delete(string $filename): void
    {
        $path = $this->uploadDir . '/' . $filename;
        if (file_exists($path) && $filename !== 'default-card.png') {
            unlink($path);
        }
    }
}
