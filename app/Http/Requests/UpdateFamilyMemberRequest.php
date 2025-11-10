<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFamilyMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'relationship' => ['sometimes', Rule::in(['spouse', 'child', 'step_child', 'parent', 'other_dependent'])],
            'name' => ['sometimes', 'string', 'max:255'],
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'before:today'],
            'gender' => ['sometimes', 'nullable', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'national_insurance_number' => ['sometimes', 'nullable', 'string', 'regex:/^[A-Z]{2}[0-9]{6}[A-Z]{1}$/'],
            'annual_income' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'is_dependent' => ['sometimes', 'boolean'],
            'education_status' => ['sometimes', 'nullable', Rule::in(['nursery', 'primary', 'secondary', 'sixth_form', 'university', 'graduated', 'not_in_education'])],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'national_insurance_number.regex' => 'National Insurance number must be in format: AB123456C',
            'annual_income.min' => 'Income cannot be negative.',
        ];
    }
}
