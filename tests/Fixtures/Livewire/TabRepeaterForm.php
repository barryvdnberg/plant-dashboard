<?php

namespace Tests\Fixtures\Livewire;

use App\Filament\Forms\Components\TabRepeater;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class TabRepeaterForm extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public array $data = [];

    public bool $partial = true;

    public bool $confirmDeletion = false;

    public function mount(): void
    {
        $this->form->fill([
            'items' => [
                ['group' => 'first', 'height' => 600],
                ['group' => 'second', 'height' => 600],
                ['group' => 'short', 'height' => 100],
            ],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            TabRepeater::make('items')
                ->keyFieldName('group')
                ->options(['first' => 'First', 'second' => 'Second', 'short' => 'Short'])
                ->addable(false)
                ->editable(false)
                ->partiallyRenderAfterActionsCalled($this->partial)
                ->deleteAction(fn (Action $action) => $action
                    ->extraAttributes(['data-test' => 'delete-item'])
                    ->requiresConfirmation($this->confirmDeletion))
                ->schema([
                    Text::make(fn (Get $get): HtmlString => new HtmlString(
                        '<div style="height:'.((int) $get('height')).'px">Panel content</div>'
                    ))->columnSpanFull(),
                ]),
        ]);
    }

    public function render(): string
    {
        return <<<'BLADE'
            <div>
                @filamentStyles
                <link rel="stylesheet" href="{{ asset('css/filament/filament/app.css') }}">
                <div style="height: 1200px"></div>
                {{ $this->form }}
                <x-filament-actions::modals />
                @filamentScripts
            </div>
            BLADE;
    }
}
