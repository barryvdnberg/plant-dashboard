<?php

namespace App\Filament\Schemas\Components\RelationshipTabs;

use App\Filament\Schemas\Components\Concerns\HasChildWithSingularRelationship;
use Closure;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class RelationshipTab extends Tab
{
    use HasChildWithSingularRelationship;
    
    protected bool | Closure $isLoadingDeferred = false;

    public function schema(array|Closure|Schema $components): static
    {
        return $this->childComponents(static function (RelationshipTab $component) use ($components): Schema {
            $group = Group::make($components);

            if ($component->hasRelationship()) {
                $group->relationship($component->relationship, $component->hasRelationship, $component->relatedModel);
            }
            else {
                throw new \Exception('Relationship is not set for the RelationshipTab.');
            }

            return Schema::make()->components([$group])->lazyLoaded($component->isLoadingDeferred);
        });
    }

    public function lazyLoaded(bool | Closure $condition = true): static
    {
        $this->isLoadingDeferred = $condition;

        return $this;
    }
}