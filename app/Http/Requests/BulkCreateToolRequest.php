<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkCreateToolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tools' => ['required', 'array', 'min:1'],
            'tools.*.title' => ['required', 'string', 'max:255'],
            'tools.*.description' => ['required', 'string'],
            'tools.*.link' => ['required', 'url'],
            'tools.*.tags' => ['array'],
            'tools.*.tags.*' => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'tools.*.title.required' => 'The title is required.',
            'tools.*.title.string' => 'The title must be a string.',
            'tools.*.title.max' => 'The title may not be greater than 255 characters.',
            'tools.*.description.required' => 'The description is required.',
            'tools.*.description.string' => 'The description must be a string.',
            'tools.*.link.required' => 'The link is required.',
            'tools.*.link.url' => 'The link must be a valid URL.',
            'tools.*.tags.array' => 'The tags must be an array.',
            'tools.*.tags.*.string' => 'Each tag must be a string.',
        ];
    }
}
