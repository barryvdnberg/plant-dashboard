<?php

namespace App\Filament\Resources\Plants\Tables;

use App\Filament\Exports\PlantExporter;
use App\Filament\Imports\PlantImporter;
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

class PlantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['localizations']))
            ->columns([
                TextColumn::make('species.name')
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
            // ->headerActions([
            //     ImportAction::make()
            //         ->importer(PlantImporter::class),
            //     ExportAction::make()
            //         ->exporter(PlantExporter::class),
            // ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
