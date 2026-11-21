<?php

namespace App\Filament\Resources\Species\RelationManagers;

use App\Filament\Forms\Builder\PlantForm;
use App\Filament\Resources\Plants\Tables\PlantsTable;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PlantsRelationManager extends RelationManager
{
    protected static string $relationship = 'plants';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                PlantForm::nameField(),
                ...PlantForm::attributeFields()
            ]);
    }

    public function table(Table $table): Table
    {
        return PlantsTable::configure($table)
            ->headerActions([CreateAction::make()]);
    }
}
