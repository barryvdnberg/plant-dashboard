<?php

namespace App\Filament\Resources\Gardens\Actions;

use App\Models\Garden;
use Filament\Actions\Action;

class GetGardenInfoAction
{
    public static function make(): Action
    {
        return Action::make('getGardenInfo')
            ->authorize('view')
            ->action(function (Garden $record) {
                // load the gardenPlants and their cultivar and inside that their environments
                $record->load('plants.cultivar.environment');

                $plantsWithLowWinterHardiness = $record->plants->filter(function ($plant) {
                    // plants with hardiness null should be not appear in the list
                    $environment = $plant->cultivar->environment;
                    if (!$environment) return false;

                    $hardiness = $environment->winter_hardiness;
                    if ($hardiness === null) return false;
                    
                    return $hardiness->value < 4;
                });
            });
    }
}
