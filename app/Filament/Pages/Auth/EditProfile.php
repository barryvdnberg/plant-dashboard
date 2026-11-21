<?php

namespace App\Filament\Pages\Auth;

use App\Enums\NameDisplay;
use App\Enums\NameDisplayGroup;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Components\Attributes\Exposed;
use Filament\Support\Components\Attributes\Unexposed;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class EditProfile extends BaseEditProfile
{
    #[Unexposed]
    public function mount(): void
    {
        $this->authorizeAdmin();
        parent::mount();
    }

    #[Exposed]
    public function save(): void
    {
        $this->authorizeAdmin();
        parent::save();
    }

    protected function authorizeAdmin(): void
    {
        abort_unless(Filament::getCurrentPanel()?->getId() === 'admin' && $this->getUser()->is_admin, 403);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('preferences.account'))->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]),
            Section::make(__('preferences.region'))->schema([
                Select::make('locale')
                    ->label(__('preferences.locale'))
                    ->helperText(__('preferences.locale_help'))
                    ->placeholder(__('preferences.default_locale'))
                    ->options(config('localization.locales'))
                    ->rules(['nullable', Rule::in(array_keys(config('localization.locales')))]),
            ]),
            Section::make(__('preferences.names'))
                ->description(__('preferences.names_help'))
                ->schema([
                    Select::make('name_display_preference')
                        ->label(__('preferences.global'))
                        ->options(NameDisplay::class)
                        ->required()
                        ->rule(Rule::enum(NameDisplay::class))
                        ->live(),
                    Text::make(__('preferences.overrides_help')),
                    Fieldset::make(__('preferences.overrides'))
                        ->schema(array_map(fn (NameDisplayGroup $group) => Select::make('name_display_overrides.'.$group->value)
                            ->label($group->getLabel())
                            ->options(NameDisplay::class)
                            ->placeholder(function (Get $get): string {
                                $display = $get('name_display_preference');
                                $display = $display instanceof NameDisplay ? $display : NameDisplay::tryFrom($display ?? '');

                                return __('preferences.inherit', ['display' => ($display ?? NameDisplay::Scientific)->getLabel()]);
                            })
                            ->rules(['nullable', Rule::enum(NameDisplay::class)]), NameDisplayGroup::cases())),
                ]),
        ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['name_display_preference'] ??= NameDisplay::Scientific->value;
        $data['name_display_overrides'] ??= [];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['name_display_overrides'] = array_filter(
            Arr::only($data['name_display_overrides'] ?? [], array_column(NameDisplayGroup::cases(), 'value')),
            fn ($value) => filled($value),
        ) ?: null;

        return $data;
    }

    protected function getRedirectUrl(): ?string
    {
        // A fresh admin request applies the selected language to the whole layout.
        return static::getUrl(panel: 'admin');
    }
}
