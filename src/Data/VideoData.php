<?php

namespace OiLab\OiLaravelPublish\Data;

use Illuminate\Validation\Rule as ValidationRule;
use OiLab\OiLaravelPublish\Enums\VideoSource;
use OiLab\OiLaravelPublish\Rules\SilentAutoplay;
use OiLab\OiLaravelPublish\Rules\SupportedVideoSource;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

/**
 * The video a block plays in its media slot, in place of its cover image.
 *
 * The `source` leads, and decides what the rest means. A **platform** video
 * (YouTube, Vimeo) is an `url` the author pastes; a **library** video is the file
 * attached to the block's `video` collection — no address to hold here, the way a
 * cover holds none. `title` is how the player is announced to a screen reader,
 * whatever the source.
 *
 * The four options only reach a library video: its player is the browser's own,
 * so it is ours to configure. A platform player is the platform's, and it is put
 * behind a click-to-play facade rather than started on anyone's behalf.
 *
 * Nothing derived is stored — not the provider, not the embed address. A stored
 * copy would drift the day a platform changes its embed path, and the console
 * has to resolve them without the server anyway (see {@see VideoSource}).
 *
 * A block whose `source` is null has no video, and shows its cover as before:
 * the object is always present, like `styles`, so the console form has a shape
 * to bind to rather than a null to guard against.
 */
class VideoData extends Data
{
    public function __construct(
        public ?VideoSource $source = null,
        public ?string $url = null,
        public ?string $title = null,
        /** Starts on its own. Browsers only allow it muted, which the rules enforce. */
        public bool $autoplay = false,
        public bool $loop = false,
        public bool $muted = false,
        /** The browser's own play bar. Off, with autoplay and loop, is an ambient video. */
        public bool $controls = true,
    ) {}

    /**
     * `url` is required by — and checked against — the chosen platform, and has
     * no place at all on a library video. Autoplay carries `muted` with it: every
     * browser refuses to start a video with sound on its own, so a payload asking
     * for both is refused here rather than silently ignored by the player.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(ValidationContext $context): array
    {
        $source = VideoSource::tryFrom((string) ($context->payload['source'] ?? ''));
        $autoplay = filter_var($context->payload['autoplay'] ?? false, FILTER_VALIDATE_BOOL);

        return [
            'source' => ['nullable', ValidationRule::enum(VideoSource::class)],
            'url' => $source?->isPlatform()
                ? ['required', 'string', 'max:2048', new SupportedVideoSource($source)]
                : ['nullable', 'string', 'max:2048'],
            'title' => ['nullable', 'string', 'max:255'],
            'autoplay' => ['boolean'],
            'loop' => ['boolean'],
            'muted' => $autoplay ? [new SilentAutoplay] : ['boolean'],
            'controls' => ['boolean'],
        ];
    }
}
