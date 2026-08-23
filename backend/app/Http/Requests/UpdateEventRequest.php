<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'type' => ['sometimes', 'string', 'max:255'],
            'date' => ['sometimes', 'date'],
            'city' => ['sometimes', 'string', 'max:255'],
            'location' => ['sometimes', 'string', 'max:255'],
            'public'=>['required','boolean'],
            'event_type_id' => ['required', 'integer', 'exists:event_types,id'],
            'invited_emails'   => ['sometimes', 'array','max:100'],
            'invited_emails.*' => [
            'sometimes',
            'string',
            'email',
            'distinct',
            'exists:users,email', ],            
            'cover_image' => [
                'sometimes',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],        
        ];
    }
}