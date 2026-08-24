<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

   public function rules(): array
    {
        return [
            'scope' => [
            'sometimes',
            'string',
            'in:public,own,joined,invited',
            function ($attribute, $value, $fail) {
                if ($value !== 'public' && !$this->user('sanctum')) {
                    $fail('You must be logged in to access this scope.');
                }
            },
            ],
            'search'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'city'     => ['sometimes', 'nullable', 'string', 'max:255'],
            'date'     => ['sometimes', 'nullable', 'date'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page'     => ['sometimes', 'integer', 'min:1'],
        ];
    }
}