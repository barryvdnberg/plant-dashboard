<?php

namespace App\Filament\Resources\Plants\Pages;

use App\Filament\Forms\Builder\PlantForm;
use App\Filament\Resources\Plants\PlantResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;

class CreatePlant extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = PlantResource::class;

    protected function getSteps(): array
    {
        return [
            Step::make('Taxonomy')
                ->schema(PlantForm::form())
                ->icon(Heroicon::OutlinedDocumentText)
                ->completedIcon(Heroicon::DocumentCheck)
                ->description('Specify the naming and taxonomy.'),
            
            Step::make('Details')
                ->schema([PlantForm::getBaseCultivarForm()])
                ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                ->completedIcon(Heroicon::Sparkles)
                ->description('Provide the defining characteristics.'),

            Step::make('Cultivars')
                ->schema([
                    PlantForm::getCultivarsForm()
                        ->extraItemActions([
                            $this->getBaseCultivarReferenceAction()
                        ])
                ])
                ->icon(Heroicon::OutlinedSquare3Stack3d)
                ->completedIcon(Heroicon::CubeTransparent)
                ->description('Create additional cultivars.')

        ];
    }

    private function getBaseCultivarReferenceAction(): Action
    {
        return Action::make('getBaseCultivarReference')
            ->icon(Heroicon::Sparkles)
            ->tooltip('Copy base cultivar details')
            ->requiresConfirmation()
            ->action(function ($get, $arguments, Repeater $component) {
                $reference = $get('baseCultivar');
                unset($reference['name']);

                $state = $component->getState();

                $newstate = array_replace($state[$arguments['item']], $reference);

                $state[$arguments['item']] = $newstate;

                $component->state($state);
            });
    }


}
