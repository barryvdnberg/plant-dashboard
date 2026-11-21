<?php

namespace Tests\Fixtures\Livewire;

use App\Filament\Forms\Components\MatrixField;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;

class MatrixFieldForm extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public array $data = [];

    public array $saved = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                MatrixField::make('months')->layout(3, 1),
            ]);
    }

    public function preset(): void
    {
        $this->form->getComponentByStatePath('months')->state([2, 1, 0]);
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
                    <button type="button" wire:click="preset">Apply preset</button>
                    <button type="submit">Save matrix</button>
                </form>
                @filamentScripts
            </div>
            BLADE;
    }
}
