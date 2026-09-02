<?php

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;
use OiLab\OiLaravelPublish\Data\Blocks\ContentData;
use OiLab\OiLaravelPublish\Data\VideoData;
use OiLab\OiLaravelPublish\Enums\VideoSource;
use OiLab\OiLaravelPublish\Models\PublishBlock;
use OiLab\OiLaravelPublish\Models\PublishPage;
use OiLab\OiLaravelPublish\OiLaravelPublish;

/** The rules a `content` block's props draw from the payload it is given. */
function contentValidator(array $video): ValidatorContract
{
    $payload = ['video' => $video];

    return Validator::make($payload, ContentData::getValidationRules($payload));
}

it('recognises the platform an address belongs to', function (string $url, VideoSource $source) {
    expect(VideoSource::fromUrl($url))->toBe($source);
})->with([
    ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', VideoSource::YouTube],
    ['https://youtu.be/dQw4w9WgXcQ', VideoSource::YouTube],
    ['https://m.youtube.com/shorts/dQw4w9WgXcQ', VideoSource::YouTube],
    ['https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', VideoSource::YouTube],
    ['https://vimeo.com/76979871', VideoSource::Vimeo],
    ['https://player.vimeo.com/video/76979871', VideoSource::Vimeo],
]);

it('recognises no platform in anything else', function (?string $url) {
    expect(VideoSource::fromUrl($url))->toBeNull();
})->with([
    'null' => [null],
    'empty' => [''],
    'a page that is not a video' => ['https://example.com/une-page'],
    'a lookalike host' => ['https://youtube.com.evil.test/watch?v=abc'],
    // A library video has no address: it is the block's `video` attachment.
    'a video file' => ['https://cdn.example.com/teaser.mp4'],
    'a scheme that is not http' => ['javascript:alert(1)'],
]);

it('requires an address that matches the chosen platform', function () {
    $validator = contentValidator(['source' => 'youtube', 'url' => 'https://vimeo.com/76979871']);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('video.url'))->toContain('YouTube');
});

it('requires an address at all when a platform is chosen', function () {
    $validator = contentValidator(['source' => 'vimeo', 'url' => null]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('video.url'))->toBeTrue();
});

it('asks a library video for no address', function () {
    expect(contentValidator(['source' => 'library', 'url' => null])->passes())->toBeTrue();
});

it('accepts a block with no video at all', function () {
    expect(contentValidator(['source' => null, 'url' => null])->passes())->toBeTrue();
});

it('refuses a video that would start on its own with sound', function () {
    $validator = contentValidator([
        'source' => 'library',
        'autoplay' => true,
        'muted' => false,
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('video.muted'))->toContain('sans son');

    expect(contentValidator([
        'source' => 'library',
        'autoplay' => true,
        'muted' => true,
    ])->passes())->toBeTrue();
});

it('hydrates the video of a content block, and defaults it to none', function () {
    $page = PublishPage::factory()->create();

    $block = PublishBlock::factory()->forPage($page)->template('content')->create([
        'props' => ['video' => [
            'source' => 'youtube',
            'url' => 'https://youtu.be/dQw4w9WgXcQ',
            'title' => 'La démo',
            'loop' => true,
        ]],
    ]);
    $block->refresh();

    expect($block->props->video)->toBeInstanceOf(VideoData::class)
        ->and($block->props->video->source)->toBe(VideoSource::YouTube)
        ->and($block->props->video->url)->toBe('https://youtu.be/dQw4w9WgXcQ')
        ->and($block->props->video->title)->toBe('La démo')
        ->and($block->props->video->loop)->toBeTrue()
        ->and($block->props->video->controls)->toBeTrue();

    $bare = PublishBlock::factory()->forPage($page)->template('content')->create(['props' => []]);
    $bare->refresh();

    expect($bare->props->video->source)->toBeNull();
});

it('declares the video collection as a single file the content block renders', function () {
    $capabilities = OiLaravelPublish::template('content')->capabilities;

    expect($capabilities->media)->toBe(['cover', 'video'])
        ->and($capabilities->hasMedia('video'))->toBeTrue()
        ->and($capabilities->isSingleFile('video'))->toBeTrue()
        ->and($capabilities->isSingleFile('slides'))->toBeFalse();
});

it('hangs a single video attachment off a block', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->template('content')->create();

    expect($block->video()->exists())->toBeFalse();
});
