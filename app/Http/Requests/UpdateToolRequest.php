<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateToolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $method = $this->method();
        
        if ($method == 'PUT') {
            return [
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string'],
                'link' => ['required', 'url'],
                'tags' => ['array'],
                'tags.*' => ['string', 'distinct'],
            ];
        }

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'link' => ['sometimes', 'required', 'url'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['sometimes', 'string', 'distinct'],
        ];
    }
}
