<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Forms\Components\BelongsToSelect;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\EditRecord as BaseEditRecord;
use Filament\Schemas\Components\Component;
use Filament\Support\Components\Attributes\Unexposed;

abstract class EditRecord extends BaseEditRecord
{
    #[Unexposed]
    public function getSchemaComponent(string $key, bool $withHidden = false, array $skipComponentsChildContainersWhileSearching = []): Component|Action|ActionGroup|null
    {
        if (str_starts_with($key, 'form.') && substr_count($key, '.') === 1) {
            // Looking up a top-level select should not initialize its siblings' relationship schemas.
            foreach ($this->form->getComponents(withActions: false, withHidden: true) as $component) {
                if ($component instanceof BelongsToSelect && $component->getKey() === $key && ($withHidden || $component->isVisible())) {
                    return $component;
                }
            }
        }

        return parent::getSchemaComponent($key, $withHidden, $skipComponentsChildContainersWhileSearching);
    }
}
