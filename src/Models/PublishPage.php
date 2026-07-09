<?php

namespace OiLab\OiLaravelPublish\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OiLab\OiLaravelAttachments\Concerns\HasAttachments;
use OiLab\OiLaravelAttachments\Concerns\HasCreatorAndUpdater;
use OiLab\OiLaravelAttachments\Concerns\HasSortable;
use OiLab\OiLaravelAttachments\Models\Attachment;
use OiLab\OiLaravelPublish\Casts\PropsCast;
use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\PublishPageData;
use OiLab\OiLaravelPublish\Data\PublishTemplateData;
use OiLab\OiLaravelPublish\Database\Factories\PublishPageFactory;
use OiLab\OiLaravelPublish\OiLaravelPublish;

/**
 * PublishPage
 *
 * A recursive CMS page. Pages form a tree via `parent_id`, own an ordered
 * collection of {@see PublishBlock}s, reference a code-defined template by
 * `template_key`, and carry a single `cover` attachment.
 *
 * @property int $id
 * @property string $uuid
 * @property int|null $parent_id
 * @property string $template_key
 * @property string $name
 * @property string $slug
 * @property string|null $excerpt
 * @property string|null $description
 * @property PropsData $props
 * @property int $sort
 * @property bool $is_active
 * @property-read PublishPage|null $parent
 * @property-read Collection<int, PublishPage> $children
 * @property-read Collection<int, PublishBlock> $blocks
 * @property-read Attachment|null $cover
 */
class PublishPage extends Model
{
    use HasAttachments;
    use HasCreatorAndUpdater;

    /** @use HasFactory<PublishPageFactory> */
    use HasFactory;

    use HasSortable;
    use HasUuids;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'parent_id',
        'template_key',
        'name',
        'slug',
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
        return PublishPageFactory::new();
    }

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'props' => PropsCast::class,
            'parent_id' => 'integer',
            'sort' => 'integer',
            'is_active' => 'boolean',
            ...$this->creatorAndUpdaterCasts(),
        ];
    }

    /**
     * @return BelongsTo<PublishPage, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(OiLaravelPublish::pageModel(), 'parent_id');
    }

    /**
     * @return HasMany<PublishPage, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(OiLaravelPublish::pageModel(), 'parent_id')->orderBy('sort');
    }

    /**
     * @return HasMany<PublishBlock, $this>
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(OiLaravelPublish::blockModel(), 'publish_page_id')->orderBy('sort');
    }

    /**
     * The single `cover` attachment for this page.
     *
     * @return MorphOne<Attachment, $this>
     */
    public function cover(): MorphOne
    {
        return $this->singleAttachment('cover');
    }

    /**
     * Resolve the code-defined template backing this page.
     */
    public function template(): ?PublishTemplateData
    {
        return OiLaravelPublish::template($this->template_key);
    }

    public function toData(): PublishPageData
    {
        return PublishPageData::fromModel($this);
    }
}
