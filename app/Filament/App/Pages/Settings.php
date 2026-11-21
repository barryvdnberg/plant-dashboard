<?php

namespace App\Filament\App\Pages;

use Filament\Actions\Action;
use Filament\Pages\Page;
use Override;

class Settings extends Page
{
    protected string $view = 'filament.app.pages.settings';

    public $defaultAction = 'onboarding';

    // public function onboardingAction(): Action
// {
    // return Action::make('onboarding')
    //     ->modalHeading('Welcome')
    //     ->visible(fn (): bool => ! auth()->user()->isOnBoarded());
// }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test')
                ->action(function () {
                    dd('test');
                })
        ];
    }

    // public static function canAccess(): bool
    // {
    //     return auth()->user()->canManageSettings();
    // }
}
