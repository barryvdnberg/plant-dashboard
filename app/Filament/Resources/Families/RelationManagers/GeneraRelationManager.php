<?php

namespace App\Filament\Resources\Families\RelationManagers;

use App\Filament\Forms\Builder\GenusForm;
use App\Filament\Resources\Genera\Tables\GeneraTable;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class GeneraRelationManager extends RelationManager
{
    protected static string $relationship = 'genera';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                GenusForm::nameField(),
                ...GenusForm::attributeFields(),
            ]);
    }

    public function table(Table $table): Table
    {
        return GeneraTable::configure($table)
            ->headerActions([CreateAction::make()]);
    }
}
