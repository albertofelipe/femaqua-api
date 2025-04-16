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
}
