<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateToolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'link' => ['required', 'url'],
            'tags' => ['array'],
            'tags.*' => ['string', 'distinct'],
        ];  
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The title is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'description.required' => 'The description is required.',
            'description.string' => 'The description must be a string.',
            'link.required' => 'The link is required.',
            'link.url' => 'The link must be a valid URL.',
            'tags.array' => 'The tags must be an array.',
            'tags.*.string' => 'Each tag must be a string.',
            'tags.*.distinct' => 'Duplicate tags are not allowed.',
        ];
    }
}
