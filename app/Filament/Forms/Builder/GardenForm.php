<?php

namespace App\Filament\Forms\Builder;

use App\Enums\HardinessZone;
use App\Filament\Forms\Builder\Contracts\NameField;
use App\Filament\Forms\Builder\Contracts\ParentField;
use App\Filament\Forms\Builder\FormBuilder;
use App\Filament\Forms\Components\NameInput;
use App\Models\Garden;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

final class GardenForm extends FormBuilder implements NameField, ParentField
{
    public static function nameField(): Component
    {
        return NameInput::make()
            ->columnSpanFullWhenFieldMissing('user_id');
    }

    public static function parentField(): Component
    {
        return Select::make('user_id')
            ->relationship('user', 'name')
            ->visible(fn (): bool => Gate::allows('assignOwner', Garden::class))
            ->required()
            ->exists('users', 'id')
            ->default(function (): int|string|null {
                $id = Auth::id();
                if (User::find($id)) {
                    return $id;
                }

                return null;
            });
    }

    public static function attributeFields(): array
    {
        return [
            Select::make('winter_hardiness')
                ->label(__('attributes.Winter_hardiness'))
                ->options(HardinessZone::class)
                ->columnSpanFull()
                ->required(),
        ];
    }
}
