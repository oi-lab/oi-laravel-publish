<?php

namespace OiLab\OiLaravelPublish\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OiLab\OiLaravelPublish\Enums\PublishTemplateType;
use OiLab\OiLaravelPublish\OiLaravelPublish;

class PublishPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $pageModel = OiLaravelPublish::pageModel();
        $pagesTable = (new $pageModel)->getTable();
        $maxSize = (int) config('oi-laravel-publish.attachments.max_file_size', 10240);

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'template_key' => ['required', 'string', Rule::in(OiLaravelPublish::registry()->keys(PublishTemplateType::Page))],
            'parent_id' => ['nullable', 'integer', "exists:{$pagesTable},id"],
            'excerpt' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'props' => ['nullable', 'array'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'cover' => ['nullable', 'file', 'image', "max:{$maxSize}"],
        ];
    }
}
