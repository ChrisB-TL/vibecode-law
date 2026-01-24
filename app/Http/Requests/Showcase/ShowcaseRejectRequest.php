<?php

namespace App\Http\Requests\Showcase;

use Illuminate\Foundation\Http\FormRequest;

class ShowcaseRejectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Please provide a reason for rejection.',
        ];
    }
}
