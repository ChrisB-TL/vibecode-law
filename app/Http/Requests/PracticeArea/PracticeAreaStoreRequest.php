<?php

namespace App\Http\Requests\PracticeArea;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PracticeAreaStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('practice_areas', 'name')],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('practice_areas', 'slug')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please provide a name for the practice area.',
            'name.unique' => 'A practice area with this name already exists.',
            'slug.required' => 'Please provide a slug for the practice area.',
            'slug.unique' => 'A practice area with this slug already exists.',
            'slug.alpha_dash' => 'The slug may only contain letters, numbers, dashes, and underscores.',
        ];
    }
}
