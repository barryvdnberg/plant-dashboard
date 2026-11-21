<?php

namespace App\Filament\Schemas\Components;

use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;

class InlineGroup extends Group
{
    // protected string $view = 'filament.schemas.components.inline-group';

    protected function configureChildSchema(Schema $schema, string $key): Schema
    {
        $schema->extraAttributes(['class' => 'fi-inline']);

        return $schema;
    }
}


