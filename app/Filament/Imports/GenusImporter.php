<?php

namespace App\Filament\Imports;

use App\Filament\Imports\Components\ImportImagesColumn;
use App\Models\Genus;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class GenusImporter extends Importer
{
    protected static ?string $model = Genus::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('family')
                ->relationship()
                ->example(1),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('Tulipa'),
            ImportImagesColumn::make('image_paths'),
        ];
    }

    public function resolveRecord(): Genus
    {
        return new Genus();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your genus import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
