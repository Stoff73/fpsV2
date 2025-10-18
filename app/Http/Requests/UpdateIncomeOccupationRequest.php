<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncomeOccupationRequest extends FormRequest
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
            'occupation' => ['sometimes', 'nullable', 'string', 'max:255'],
            'employer' => ['sometimes', 'nullable', 'string', 'max:255'],
            'industry' => ['sometimes', 'nullable', 'string', 'max:255'],
            'employment_status' => ['sometimes', 'nullable', Rule::in(['employed', 'self_employed', 'unemployed', 'retired', 'student', 'other'])],
            'annual_employment_income' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'annual_self_employment_income' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'annual_rental_income' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'annual_dividend_income' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'annual_other_income' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999999999.99'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            '*.numeric' => 'Income must be a valid number.',
            '*.min' => 'Income cannot be negative.',
            '*.max' => 'Income value is too large.',
        ];
    }
}
