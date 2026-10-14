<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCriteriaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare data for validation if criteria is sent as direct array or nested
     */
    protected function prepareForValidation(): void
    {
        // If criteria is passed as a wrapper object { criteria: [...] } or direct array
        if ($this->has('criteria') && is_array($this->input('criteria'))) {
            // Keep as is
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->has('criteria') && is_array($this->input('criteria'))) {
            return [
                'criteria' => ['required', 'array'],
                'criteria.*.id' => ['required', 'exists:criteria,id'],
                'criteria.*.weight' => ['required', 'numeric', 'gt:0', 'lte:1'],
                'criteria.*.type' => ['required', 'in:benefit,cost'],
            ];
        }

        return [
            '*.id' => ['required', 'exists:criteria,id'],
            '*.weight' => ['required', 'numeric', 'gt:0', 'lte:1'],
            '*.type' => ['required', 'in:benefit,cost'],
        ];
    }
}
