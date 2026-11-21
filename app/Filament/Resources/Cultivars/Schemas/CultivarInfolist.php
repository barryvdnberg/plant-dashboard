<?php

namespace App\Filament\Resources\Cultivars\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CultivarInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('plant.name')
                    ->numeric(),
                TextEntry::make('name'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
