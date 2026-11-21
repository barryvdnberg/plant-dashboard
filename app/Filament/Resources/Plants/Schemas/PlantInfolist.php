<?php

namespace App\Filament\Resources\Plants\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PlantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('species.name')
                    ->numeric(),
                TextEntry::make('name'),
                TextEntry::make('english_name'),
                TextEntry::make('dutch_name'),
                ImageEntry::make('image_path'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
