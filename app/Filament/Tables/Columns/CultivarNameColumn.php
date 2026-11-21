<?php

namespace App\Filament\Tables\Columns;

use Filament\Tables\Columns\Column;

class CultivarNameColumn extends Column
{
    protected string $view = 'filament.tables.columns.cultivar-name-column';

    public function getState(): mixed
    {

        $record = $this->record;

        return $record->display_name;
    }
}
