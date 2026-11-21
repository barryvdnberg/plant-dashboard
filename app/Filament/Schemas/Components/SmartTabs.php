<?php

namespace App\Filament\Schemas\Components;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Components\Component;
use Filament\Support\Components\Contracts\HasEmbeddedView;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\View\ComponentAttributeBag as FilamentComponentAttributeBag;
use Illuminate\Contracts\Support\Htmlable;

class SmartTabs extends Component implements HasEmbeddedView
{
    protected ?string $publishedViewOverrideCheckPath = 'filament-schemas::components.grid';

    protected bool | Closure $isLoadingDeferred = false;

    protected string | Closure | null $tabQueryStringKey = null;

    /**
     * @param  array<Component | Action | ActionGroup | string | Htmlable> | Closure  $tabs
     */
    final public function __construct(array | Closure $tabs = [])
    {
        $this->tabs($tabs);
    }

    /**
     * @param  array<Component | Action | ActionGroup | string | Htmlable> | Closure  $tabs
     */
    public static function make(array | Closure $tabs = []): static
    {
        $static = app(static::class, ['tabs' => $tabs]);
        $static->configure();

        return $static;
    }
    
    public function tabs(array|Closure $tabs) : static
    {
        return $this->schema(static function (SmartTabs $component) use ($tabs): array | Schema {
            return Schema::make()
                ->lazyLoaded($component->isLazyLoaded())
                ->components([
                    Tabs::make()->tabs($tabs)->persistTabInQueryString($component->getTabQueryStringKey())
                ]);
        });
    }

    public function lazyLoaded(bool | Closure $condition = true): static
    {
        $this->isLoadingDeferred = $condition;

        return $this;
    }

    public function isLazyLoaded(): bool
    {
        return (bool) $this->evaluate($this->isLoadingDeferred);
    }

    public function persistTabInQueryString(string | Closure | null $key = 'tab'): static
    {
        $this->tabQueryStringKey = $key;

        return $this;
    }

    public function getTabQueryStringKey(): ?string
    {
        return $this->evaluate($this->tabQueryStringKey);
    }

    public function toEmbeddedHtml(): string
    {
        $attributes = (new FilamentComponentAttributeBag)
            ->merge(['id' => $this->getId()], escape: false)
            ->merge($this->getExtraAttributes(), escape: false);

        ob_start(); ?>

        <div <?= $attributes->toHtml() ?>>
            <?= $this->getChildSchema()->toHtml() ?>
        </div>

        <?php return ob_get_clean();
    }
    
    
}