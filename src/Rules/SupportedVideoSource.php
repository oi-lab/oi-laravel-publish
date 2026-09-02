<?php

namespace OiLab\OiLaravelPublish\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use OiLab\OiLaravelPublish\Enums\VideoSource;

/**
 * The address of a block's video belongs to the platform the author picked.
 *
 * Given no expected platform the rule only asks for *a* platform, which is what
 * a payload carrying an address but no source is worth checking against.
 *
 * An empty value passes: the field is how an author clears the video, and
 * `required_if` is what makes it mandatory when a platform is chosen.
 */
class SupportedVideoSource implements ValidationRule
{
    public function __construct(private readonly ?VideoSource $expected = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $source = is_string($value) ? VideoSource::fromUrl($value) : null;

        if ($source === null) {
            $fail('L’adresse doit être celle d’une vidéo YouTube ou Vimeo.');

            return;
        }

        if ($this->expected !== null && $source !== $this->expected) {
            $fail("L’adresse doit être celle d’une vidéo {$this->expected->label()}.");
        }
    }
}
