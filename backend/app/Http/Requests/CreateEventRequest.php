<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            
            'event_type_id' => ['required', 'integer', 'exists:event_types,id'],
            
            'date' => ['required', 'date'],
            'city' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'invited_emails'   => ['sometimes', 'array', 'max:100'],
            'invited_emails.*' => [
                'sometimes',
                'string',
                'email',
                'distinct',
                'exists:users,email',
            ],
            'public' => ['required', 'boolean'],
            'cover_image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],        
        ];
    }
}