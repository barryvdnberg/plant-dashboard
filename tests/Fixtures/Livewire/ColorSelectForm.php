<?php

namespace Tests\Fixtures\Livewire;

use App\Filament\Forms\Components\ColorSelect;
use App\Models\Color;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;
use Tests\Fixtures\Models\TabBuilderRecord;

class ColorSelectForm extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public array $data = [];

    public array $saved = [];

    public function mount(): void
    {
        $this->form->fill();
        $this->form->getComponentByStatePath('colors')->state(Color::catalog()->pluck('id')->all());
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->model(TabBuilderRecord::class)->components([
            ColorSelect::make('colors'),
        ]);
    }

    public function save(): void
    {
        $this->saved = $this->form->getState();
    }

    public function render(): string
    {
        return <<<'BLADE'
            <div>
                @filamentStyles
                <form wire:submit="save">
                    {{ $this->form }}
                    <button type="submit">Save colors</button>
                </form>
                @filamentScripts
            </div>
            BLADE;
    }
}
