<?php

namespace OiLab\OiLaravelPublish\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OiLab\OiLaravelAttachments\Concerns\HasAttachments;
use OiLab\OiLaravelAttachments\Concerns\HasCreatorAndUpdater;
use OiLab\OiLaravelAttachments\Concerns\HasSortable;
use OiLab\OiLaravelAttachments\Models\Attachment;
use OiLab\OiLaravelPublish\Casts\PropsCast;
use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\PublishBlockData;
use OiLab\OiLaravelPublish\Data\PublishTemplateData;
use OiLab\OiLaravelPublish\Database\Factories\PublishBlockFactory;
use OiLab\OiLaravelPublish\OiLaravelPublish;

/**
 * PublishBlock
 *
 * An ordered block belonging to a single {@see PublishPage}. It references a
 * code-defined block template by `template_key`, carries typed `props`, and owns
 * `cover` and `slides` attachment collections.
 *
 * @property int $id
 * @property string $uuid
 * @property int $publish_page_id
 * @property string $template_key
 * @property string $name
 * @property string $key
 * @property string|null $excerpt
 * @property string|null $description
 * @property PropsData $props
 * @property int $sort
 * @property bool $is_active
 * @property-read PublishPage $page
 * @property-read Attachment|null $cover
 * @property-read Collection<int, Attachment> $slides
 * @property-read Collection<int, Attachment> $gallery
 */
class PublishBlock extends Model
{
    use HasAttachments;
    use HasCreatorAndUpdater;

    /** @use HasFactory<PublishBlockFactory> */
    use HasFactory;

    use HasSortable;
    use HasUuids;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'publish_page_id',
        'template_key',
        'name',
        'key',
        'excerpt',
        'description',
        'props',
        'sort',
        'is_active',
    ];

    /**
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    protected static function newFactory(): Factory
    {
        return PublishBlockFactory::new();
    }

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'props' => PropsCast::class,
            'publish_page_id' => 'integer',
            'sort' => 'integer',
            'is_active' => 'boolean',
            ...$this->creatorAndUpdaterCasts(),
        ];
    }

    /**
     * @return BelongsTo<PublishPage, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(OiLaravelPublish::pageModel(), 'publish_page_id');
    }

    /**
     * The single `cover` attachment for this block.
     *
     * @return MorphOne<Attachment, $this>
     */
    public function cover(): MorphOne
    {
        return $this->singleAttachment('cover');
    }

    /**
     * The ordered `slides` attachment collection for this block.
     *
     * @return MorphMany<Attachment, $this>
     */
    public function slides(): MorphMany
    {
        return $this->attachments('slides');
    }

    /**
     * The ordered `gallery` attachment collection for this block.
     *
     * @return MorphMany<Attachment, $this>
     */
    public function gallery(): MorphMany
    {
        return $this->attachments('gallery');
    }

    /**
     * Resolve the code-defined template backing this block.
     */
    public function template(): ?PublishTemplateData
    {
        return OiLaravelPublish::template($this->template_key);
    }

    public function toData(): PublishBlockData
    {
        return PublishBlockData::fromModel($this);
    }
}
