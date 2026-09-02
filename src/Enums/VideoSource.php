<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * Where a block's video comes from: a YouTube page, a Vimeo page, or a file of
 * the site's own media library.
 *
 * The source is **chosen**, not guessed. It was inferred from the pasted URL
 * until now, which reads well for the two platforms and not at all for a local
 * file — there is no URL to infer from, the video hangs off the block as an
 * attachment like its cover does. Choosing first is also what lets the console
 * ask for the right thing: an address for a platform, a picker for a file.
 *
 * `fromUrl()` therefore only answers the two platforms, and only to say whether
 * an address is one of theirs. Recognition stops at the host: the video id is
 * extracted in the browser, by `resources/js/lib/video-embed.ts` in the host
 * application — the console previews a block *as it is typed*, before anything
 * is saved, so the player has to be resolvable without asking the server. What
 * is checked here is the one thing the browser cannot: that what an author
 * pasted is a video address at all, refused at save time rather than rendering
 * an empty slot.
 */
enum VideoSource: string
{
    case YouTube = 'youtube';
    case Vimeo = 'vimeo';
    case Library = 'library';

    /**
     * The hosts each platform answers on, `www.` stripped before comparison.
     *
     * @var array<int, string>
     */
    private const YOUTUBE_HOSTS = [
        'youtube.com',
        'm.youtube.com',
        'youtube-nocookie.com',
        'youtu.be',
    ];

    /**
     * @var array<int, string>
     */
    private const VIMEO_HOSTS = [
        'vimeo.com',
        'player.vimeo.com',
    ];

    /**
     * The platform a URL belongs to, or `null` when it belongs to none.
     *
     * Never answers `Library`: a library video has no address of its own — it is
     * the file attached to the block's `video` collection.
     */
    public static function fromUrl(?string $url): ?self
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = str_starts_with($host, 'www.') ? substr($host, 4) : $host;

        return match (true) {
            in_array($host, self::YOUTUBE_HOSTS, true) => self::YouTube,
            in_array($host, self::VIMEO_HOSTS, true) => self::Vimeo,
            default => null,
        };
    }

    /**
     * Whether the source is played from an address the author pastes, rather
     * than from a file the block holds.
     */
    public function isPlatform(): bool
    {
        return $this !== self::Library;
    }

    /** The platform's name, as an error message names it. */
    public function label(): string
    {
        return match ($this) {
            self::YouTube => 'YouTube',
            self::Vimeo => 'Vimeo',
            self::Library => 'la médiathèque',
        };
    }
}
