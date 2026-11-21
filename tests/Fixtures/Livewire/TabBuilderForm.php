<?php

namespace Tests\Fixtures\Livewire;

use App\Filament\Forms\Components\TabBuilder;
use App\Filament\Forms\Components\TabBuilder\TabSchema;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;
use Tests\Fixtures\Models\TabBuilderRecord;

class TabBuilderForm extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public array $data = [];

    public array $saved = [];

    public ?TabBuilderRecord $record = null;

    public bool $nested = false;

    public bool $defaults = false;

    public bool $relationshipOnly = false;

    public bool $disabled = false;

    public string $relationshipName = 'details';

    public function mount(?TabBuilderRecord $record = null, ?array $initialState = null): void
    {
        $this->record = $record;
        $this->form->fill($initialState);
    }

    public function form(Schema $schema): Schema
    {
        $builder = TabBuilder::make('items')
            ->disabled($this->disabled)
            ->schemas([
                ...($this->relationshipOnly ? [] : [
                    'plain' => TabSchema::make('General')->schema([
                        TextInput::make('note')->required()->default('Default note'),
                    ]),
                    'other' => [TextInput::make('other_note')->default('Other default')],
                    'native' => Schema::make()->components([TextInput::make('native_note')]),
                ]),
                'profile' => TabSchema::make('Profile')->relationship($this->relationshipName)->schema([
                    TextInput::make('name')->required(),
                ]),
            ])
            ->deleteAction(fn ($action) => $action->extraAttributes(['data-test' => 'delete-item']));

        $builder->addAction(fn ($action) => $action->extraAttributes(['data-test' => 'add-item']));

        if ($this->defaults) {
            $builder->default(['plain' => []]);
        }

        return $schema->statePath('data')->model($this->record ?? TabBuilderRecord::class)->components([
            $this->nested ? Group::make([$builder])->relationship('details') : $builder,
        ]);
    }

    public function save(): void
    {
        $this->saved = $this->form->getState();
    }

    public function create(): void
    {
        $this->saved = $this->form->getState();
        $this->record = TabBuilderRecord::create($this->saved);
        $this->form->model($this->record)->saveRelationships();
    }

    public function render(): string
    {
        return <<<'BLADE'
            <div>
                @filamentStyles
                <link rel="stylesheet" href="{{ asset('css/filament/filament/app.css') }}">
                {{ $this->form }}
                <x-filament-actions::modals />
                @filamentScripts
            </div>
            BLADE;
    }
}
