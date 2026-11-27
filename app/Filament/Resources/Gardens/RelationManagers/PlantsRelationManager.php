<?php

namespace App\Filament\Resources\Gardens\RelationManagers;

use App\Filament\Resources\Cultivars\Tables\CultivarsTable;
use App\Models\Cultivar;
use App\Models\GardenPlant;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Gate;

class PlantsRelationManager extends RelationManager
{
    protected static string $relationship = 'plants';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return Gate::allows('view', $ownerRecord) && parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public function mount(): void
    {
        Gate::authorize('view', $this->getOwnerRecord());
        parent::mount();
    }

    public function hydrate(): void
    {
        Gate::authorize('view', $this->getOwnerRecord());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ModalTableSelect::make('cultivar_id')
                    ->relationship('cultivar', 'name')
                    ->columnSpanFull()
                    ->required()
                    ->tableConfiguration(CultivarsTable::class)
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        return $record->display_name;
                    })
                    ->selectAction(function (Action $action) {
                        $action
                            ->modalHeading(__('Select :name', ['name' => Cultivar::getModelLabel()]))
                            ->modalWidth(Width::SixExtraLarge);
                    }),
                TextInput::make('name')
                    ->required(),
                TextInput::make('size')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(999999.99),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('cultivar.name')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('size')
                    ->numeric()
                    ->sortable(),
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
            ->headerActions([
                CreateAction::make()
                    ->before(fn () => Gate::authorize('createIn', [GardenPlant::class, $this->getOwnerRecord()])),
                AssociateAction::make()
                    ->authorize(fn (): bool => ! $this->isReadOnly() && Gate::allows('update', $this->getOwnerRecord()))
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query
                        ->when(! auth()->user()->is_admin, fn (Builder $query) => $query
                            ->whereHas('garden', fn (Builder $query) => $query->where('user_id', auth()->id()))))
                    ->using(function (GardenPlant $record, BelongsTo $inverseRelationship): bool {
                        $destination = $this->getOwnerRecord();
                        Gate::authorize('moveTo', [$record, $destination]);

                        return $inverseRelationship->associate($destination)->save();
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->authorizeIndividualRecords(),
                ]),
            ]);
    }
}
