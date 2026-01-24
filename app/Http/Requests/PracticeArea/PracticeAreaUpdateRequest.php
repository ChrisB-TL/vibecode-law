<?php

namespace App\Http\Requests\PracticeArea;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PracticeAreaUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('practice_areas', 'name')->ignore($this->route('practiceArea'))],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please provide a name for the practice area.',
            'name.unique' => 'A practice area with this name already exists.',
        ];
    }
}
