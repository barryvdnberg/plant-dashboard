<?php

namespace App\Filament\Imports;

use App\Filament\Imports\Components\ImportImagesColumn;
use App\Models\Family;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class FamilyImporter extends Importer
{
    protected static ?string $model = Family::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('Liliaceae'),
            ImportImagesColumn::make('image_paths')
        ];
    }

    public function resolveRecord(): Family
    {
        return new Family();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your family import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
