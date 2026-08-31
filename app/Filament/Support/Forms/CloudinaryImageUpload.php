<?php

namespace App\Filament\Support\Forms;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class CloudinaryImageUpload
{
    public static function make(string $name): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->disk('cloudinary')
            ->visibility('public')
            ->maxSize(5120)
            ->fetchFileInformation(false)
            ->getUploadedFileNameForStorageUsing(
                fn (TemporaryUploadedFile $file): string => Str::ulid().'-'.str($file->getClientOriginalName())->beforeLast('.'),
            );
    }
}
