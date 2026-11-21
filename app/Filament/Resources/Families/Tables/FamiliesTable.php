<?php

namespace App\Filament\Resources\Families\Tables;

use App\Filament\Exports\FamilyExporter;
use App\Filament\Imports\FamilyImporter;
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

class FamiliesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['localizations']))
            ->columns([
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
                    ->importer(FamilyImporter::class),
                ExportAction::make()
                    ->exporter(FamilyExporter::class),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
