<?php

namespace App\Filament\Resources\Plants\RelationManagers;

use App\Filament\Forms\Builder\CultivarForm;
use App\Filament\Resources\Cultivars\Tables\CultivarsTable;
use App\Filament\Resources\Plants\RelationManagers\Actions\CultivarAssociateAction;
use App\Filament\Resources\Plants\RelationManagers\Actions\CultivarReplicateAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CultivarsRelationManager extends RelationManager
{
    protected static string $relationship = 'cultivars';

    // protected static ?string $relatedResource = CultivarResource::class;
    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                CultivarForm::nameField(),
                ...CultivarForm::attributeFields()
            ]);
    }
    public function table(Table $table): Table
    {
        return CultivarsTable::configure($table)
            ->headerActions([
                CreateAction::make(),
                CultivarAssociateAction::make()
                
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                CultivarReplicateAction::make()
            ]);
    }
}
