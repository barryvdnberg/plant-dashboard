<?php

namespace App\Filament\Forms\Components\Concerns;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

trait HasEmptySchema
{
    protected ?Closure $modifyEmptySchemaUsing = null;

    /**
     * @param  array<Component | Action | ActionGroup | string | Htmlable> | Schema | Component | Action | ActionGroup | string | Htmlable | Closure | null  $schema
     */
    public function emptySchema(array | Schema | Component | Action | ActionGroup | string | Htmlable | Closure | null $schema): static
    {
        return $this->childComponents($schema, 'empty-schema');
    }

    public function modifyEmptySchemaUsing(?Closure $callback): static
    {
        $this->modifyEmptySchemaUsing = $callback;

        return $this;
    }

    public function getEmptySchema(): ?Schema
    {
        $schema = $this->getChildSchema('empty-schema');

        if ($this->modifyEmptySchemaUsing) {
            $this->evaluate($this->modifyEmptySchemaUsing, [
                'component' => $this,
                'schema' => $schema,
            ]);
        }

        return $schema;
    }
}