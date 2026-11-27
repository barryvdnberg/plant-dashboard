<?php

namespace App\Filament\Resources\Species\Tables;

use App\Filament\Exports\SpeciesExporter;
use App\Filament\Imports\SpeciesImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SpeciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['localizations', 'parent']))
            ->columns([
                TextColumn::make('genus.name')
                    ->numeric()
                    ->sortable(),
                TextInputColumn::make('name')
                    ->rules(['required', 'string', 'max:100'])
                    ->disabled(fn ($record) => ! auth()->user()?->can('update', $record))
                    ->searchable(),
                TextColumn::make('display_name')
                    ->label(__('preferences.display_name')),
                ImageColumn::make('image_path')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->headerActions([
                ImportAction::make()
                    ->authorize('import')
                    ->importer(SpeciesImporter::class),
                ExportAction::make()
                    ->exporter(SpeciesExporter::class),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
