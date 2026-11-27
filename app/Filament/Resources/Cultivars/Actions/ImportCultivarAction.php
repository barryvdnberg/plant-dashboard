<?php

namespace App\Filament\Resources\Cultivars\Actions;

use App\Filament\Forms\Components\ModelSelects\PlantSelect;
use App\Filament\Resources\Cultivars\Pages\CreateCultivar;
use App\Models\Cultivar;
use App\Models\Plant;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;
use Throwable;

class ImportCultivarAction
{
    public static function make(): Action
    {
        return Action::make('importCultivar')
            ->authorize(fn (): bool => Gate::allows('create', Cultivar::class))
            ->label(__('Import data'))
            ->modalHeading(__('Import fields from another cultivar'))
            ->icon(Heroicon::ArrowDownTray)
            ->requiresConfirmation()
            ->fillForm(fn (CreateCultivar $livewire): array => [
                'plant_id' => $livewire->form->getComponent('plant_id')->getState(),
            ])
            ->schema([
                PlantSelect::make('plant_id')
                    ->label(Plant::getModelLabel())
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('cultivar_id', null))
                    ->required(),

                Select::make('cultivar_id')
                    ->label(Cultivar::getModelLabel())
                    ->options(function (Get $get): array {
                        if (blank($plantId = $get('plant_id'))) {
                            return [];
                        }

                        return Cultivar::query()
                            ->withLocalizedData(depth: 1)
                            ->where('cultivars.plant_id', $plantId)
                            ->select('cultivars.*')
                            ->get()
                            ->mapWithKeys(fn (Cultivar $cultivar) => [$cultivar->id => $cultivar->display_name])
                            ->all();
                    })
                    ->required(),
            ])
            ->action(function (array $data, CreateCultivar $livewire): void {
                $source = Cultivar::query()
                    ->where('plant_id', $data['plant_id'])
                    ->find($data['cultivar_id']);

                if (! $source) {
                    return;
                }

                Gate::authorize('view', $source);

                self::import($livewire, $source);
            });
    }

    private static function import(CreateCultivar $livewire, Cultivar $source): void
    {
        $targetForm = $livewire->form;
        $targetState = $targetForm->getRawState();

        // Hydrate relationships against the source using a separate schema.
        // Both schemas write to the create page's form state.
        $sourceForm = $livewire->form($livewire->defaultForm(Schema::make($livewire)))
            ->model($source);

        try {
            $sourceForm->fill($source->only(['plant_id', 'name']));
            $importedState = $sourceForm->getRawState();
            $importedState['image_paths'] = $targetState['image_paths'] ?? [];
            $targetForm->rawState($importedState);
        } catch (Throwable $exception) {
            $targetForm->rawState($targetState);

            throw $exception;
        }
    }
}
