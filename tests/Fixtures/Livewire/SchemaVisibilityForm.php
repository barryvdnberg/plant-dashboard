<?php

namespace Tests\Fixtures\Livewire;

use App\Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema as BaseSchema;
use Livewire\Component;

class SchemaVisibilityForm extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public array $data = [];

    public bool $deferContent = false;

    public function form(BaseSchema $schema): BaseSchema
    {
        return $schema->statePath('data')->components([
            TextInput::make('mode')->belowContent(
                Schema::between([
                    TextInput::make('first')->required(),
                    TextInput::make('second'),
                ])
                    ->key('conditional')
                    ->lazyLoaded($this->deferContent)
                    ->extraAttributes(['data-testid' => 'conditional-schema'])
                    ->visibleJs(<<<'JS'
                        !!$get('mode')
                        JS)
                    ->hiddenJs(fn (): string => <<<'JS'
                        $get('mode') === "hide"
                        JS),
            ),
        ]);
    }

    public function render(): string
    {
        return <<<'BLADE'
            <div>
                @filamentStyles
                <link rel="stylesheet" href="{{ asset('css/filament/filament/app.css') }}">
                <form>{{ $this->form }}</form>
                @filamentScripts
            </div>
            BLADE;
    }
}
