<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RelationshipSelect extends Select
{
    use Concerns\RelationshipCanHaveFormSchema;
    use Concerns\RelationshipCanHaveLocalization;
    use Concerns\RelationshipCanHaveModelLabel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureLabel();

        $this->configureManageOptionForm();

    }

    public function relationship(string|Closure|null $name = null, string|Closure|null $titleAttribute = null, ?Closure $modifyQueryUsing = null, bool $ignoreRecord = false): static
    {
        parent::relationship(
            $name,
            $titleAttribute,
            static function (Builder $query, RelationshipSelect $component) use ($modifyQueryUsing) {
                if ($component->relationshipHasLocalization()) {
                    $query
                        ->withLocalization()
                        ->with('localizations')
                        ->addLocalizationSelect();
                }
                if ($modifyQueryUsing) {
                    $modifyQueryUsing($query);
                }
            },
            $ignoreRecord
        );

        $this->getOptionLabelFromRecordUsing(static function (Model $record, RelationshipSelect $component) {
            if ($component->relationshipHasLocalization()) {
                return $record->display_name;
            }

            return $record->{$component->getRelationshipTitleAttribute()} ?? '';
        });

        return $this;
    }

    public function getSearchColumns(): ?array
    {
        if ($this->relationshipHasLocalization()) {
            $table = $this->getRelationship()->getRelated()->getTable();

            return [
                "{$table}.{$this->relationshipTitleAttribute}",
                '_localizables.value',
                '_localizables.synonyms',
            ];
        }

        return parent::getSearchColumns();
    }
}
