<?php

namespace OiLab\OiLaravelPublish\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A video that starts on its own starts without sound.
 *
 * Every browser refuses to autoplay an audible video, and answers the attempt
 * with a rejected promise the page never sees: the video simply does not start.
 * The pair is therefore refused at save time rather than left to fail silently
 * in front of a reader.
 *
 * The message rides in the rule rather than in a `messages()` map: props are
 * validated through `PropsData::getValidationRules()`, which collects rules and
 * nothing else, so a message declared anywhere else never reaches the console.
 */
class SilentAutoplay implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filter_var($value, FILTER_VALIDATE_BOOL) !== true) {
            $fail('Une vidéo qui démarre seule doit être sans son : aucun navigateur ne la lancerait autrement.');
        }
    }
}
