# Client-side schema visibility

Import `App\Filament\Schemas\Schema` to toggle a whole schema from form state. Child elements keep their layout, so `between()` still spaces them apart.

```php
use App\Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Schemas\Components\Text;

Schema::between([
    Text::make(__('Generate a name from the selected species')),
    Action::make('generate')->link(),
])
    ->inline()
    ->visibleJs('!!$get("species_id") && !$get("name")');

```

Use `hiddenJs()` to hide a schema when its expression is truthy. If you set both methods, the schema shows up only when `visibleJs()` is truthy and `hiddenJs()` is falsy. Both methods take a JavaScript expression or a PHP closure that returns one. Pass `null` to clear a condition.

Inside a field slot like `belowContent()`, `$get()` reads from the parent field's container. That lets `$get("species_id")` grab a sibling field. Everywhere else, `$get()` uses the schema's own state path.

Hidden inputs get disabled during native browser validation. Add server-side validation rules whenever a condition still matters on form submit.