<?php

namespace App\Filament\Resources\Genera\RelationManagers;

use App\Filament\Forms\Builder\SpeciesForm;
use App\Filament\Resources\Species\Tables\SpeciesTable;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SpeciesRelationManager extends RelationManager
{
    protected static string $relationship = 'species';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpeciesForm::nameField(),
                ...SpeciesForm::attributeFields()
            ]);
    }

    public function table(Table $table): Table
    {
        return SpeciesTable::configure($table)
            ->headerActions([CreateAction::make()]);
    }
}
