<?php

namespace App\Filament\Forms\Components\TabBuilder;

use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Concerns\EntanglesStateWithSingularRelationship;
use Filament\Schemas\Components\Concerns\HasLabel;
use Filament\Schemas\Components\Concerns\HasName;
use Filament\Schemas\Components\Contracts\CanEntangleWithSingularRelationships;
use Filament\Support\Components\Contracts\HasEmbeddedView;
use Illuminate\Contracts\Support\Htmlable;

class TabSchema extends Component implements CanEntangleWithSingularRelationships, HasEmbeddedView
{
    use EntanglesStateWithSingularRelationship;
    use HasLabel {
        getLabel as protected getBaseLabel;
    }
    use HasName;

    final public function __construct(string $name)
    {
        $this->name($name);
    }

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    public function getLabel(): string|Htmlable
    {
        return $this->getBaseLabel() ?? str($this->getName())->headline()->toString();
    }

    public function toEmbeddedHtml(): string
    {
        return $this->getChildSchema()->toHtml();
    }
}
