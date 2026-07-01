<?php

namespace OiLab\OiLaravelPublish\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OiLab\OiLaravelPublish\Enums\PublishTemplateType;
use OiLab\OiLaravelPublish\OiLaravelPublish;

class PublishBlockRequest extends FormRequest
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
        $maxFiles = (int) config('oi-laravel-publish.attachments.max_files', 30);
        $maxSize = (int) config('oi-laravel-publish.attachments.max_file_size', 10240);

        return [
            'publish_page_id' => ['required', 'integer', "exists:{$pagesTable},id"],
            'template_key' => ['required', 'string', Rule::in(OiLaravelPublish::registry()->keys(PublishTemplateType::Block))],
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'props' => ['nullable', 'array'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'cover' => ['nullable', 'file', 'image', "max:{$maxSize}"],
            'slides' => ['nullable', 'array', "max:{$maxFiles}"],
            'slides.*' => ['file', 'image', "max:{$maxSize}"],
        ];
    }
}
