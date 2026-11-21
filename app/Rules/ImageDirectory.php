<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ImageDirectory implements ValidationRule
{
    public bool $implicit = true;

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)
            || ! preg_match('~\Auploads/images/(?:[A-Za-z0-9_-][A-Za-z0-9._-]*/)*[A-Za-z0-9_-][A-Za-z0-9._-]*\.(?:jpe?g|png|gif|webp|avif)\z~i', $value)) {
            $fail('The :attribute must be an image path beneath "uploads/images/" without traversal segments.');
        }
    }
}
