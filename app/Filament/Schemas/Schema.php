<?php

namespace App\Filament\Schemas;

use Closure;
use Filament\Schemas\Schema as BaseSchema;
use Illuminate\Support\Js;
use Illuminate\View\ComponentAttributeBag;

class Schema extends BaseSchema
{
    protected string|Closure|null $visibleJs = null;

    protected string|Closure|null $hiddenJs = null;

    public function visibleJs(string|Closure|null $condition): static
    {
        $this->visibleJs = $condition;

        return $this;
    }

    public function hiddenJs(string|Closure|null $condition): static
    {
        $this->hiddenJs = $condition;

        return $this;
    }

    protected function hasJsVisibility(): bool
    {
        return ($this->visibleJs !== null) || ($this->hiddenJs !== null);
    }

    public function getExtraAttributeBag(): ComponentAttributeBag
    {
        $attributes = parent::getExtraAttributeBag();

        if ($this->hasJsVisibility()) {
            // Filament morphs the partial's outer element, which is now the fieldset.
            $attributes->offsetSet('wire:partial', false);
        }

        return $attributes;
    }

    protected function renderEmbeddedHtml(): string
    {
        $html = parent::renderEmbeddedHtml();

        if (($html === '') || (! $this->hasJsVisibility())) {
            return $html;
        }

        $hiddenJs = $this->evaluate($this->hiddenJs);
        $visibleJs = $this->evaluate($this->visibleJs);

        $visibilityJs = match ([filled($hiddenJs), filled($visibleJs)]) {
            [true, true] => "(! ({$hiddenJs})) && ({$visibleJs})",
            [true, false] => "! ({$hiddenJs})",
            [false, true] => $visibleJs,
            default => 'true',
        };

        $parentComponent = $this->isEmbeddedInParentComponent() ? $this->getParentComponent() : null;
        $statePath = $this->getStatePath();
        $scope = Js::from([
            'path' => $parentComponent?->getStatePath() ?? $statePath,
            'containerPath' => $parentComponent ? $parentComponent->getContainer()->getStatePath() : $statePath,
        ]);
        $condition = e($visibilityJs);
        $partialAttribute = ($this->isLazyLoaded() || $this->shouldPartiallyRender())
            ? ' wire:partial="'.e('schema.'.$this->getKey()).'"'
            : '';

        // A disabled fieldset keeps hidden inputs out of native validation.
        // Display contents keeps the schema in its original layout when shown.
        return <<<HTML
            <fieldset{$partialAttribute}
                class="fi-sc-visibility-fieldset"
                role="none"
                disabled
                x-cloak
                x-data="filamentSchemaComponent({$scope})"
                x-bind:style="{ display: ({$condition}) ? 'contents' : 'none' }"
                x-bind:disabled="! ({$condition})"
            >
                {$html}
            </fieldset>
            HTML;
    }
}
