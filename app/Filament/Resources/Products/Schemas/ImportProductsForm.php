<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Company;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class ImportProductsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->label('Empresa')
                    ->options(fn (): array => Company::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->required(),
                FileUpload::make('spreadsheet')
                    ->label('Planilha (XLSX)')
                    ->disk('local')
                    ->directory('product-imports')
                    ->visibility('private')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                    ])
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file): string => Str::ulid().'-'.$file->getClientOriginalName(),
                    )
                    ->required()
                    ->helperText('O layout das colunas pode ser qualquer um — a IA vai propor um mapeamento para os campos do produto, que você revisa antes de confirmar.'),
            ]);
    }
}
