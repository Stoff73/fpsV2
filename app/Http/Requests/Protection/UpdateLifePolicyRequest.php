<?php

declare(strict_types=1);

namespace App\Http\Requests\Protection;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLifePolicyRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'policy_type' => ['sometimes', Rule::in(['term', 'whole_of_life', 'decreasing_term', 'family_income_benefit', 'level_term'])],
            'provider' => ['sometimes', 'string', 'max:255'],
            'policy_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sum_assured' => ['sometimes', 'numeric', 'min:1000', 'max:9999999999999.99'],
            'premium_amount' => ['sometimes', 'numeric', 'min:0', 'max:9999999.99'],
            'premium_frequency' => ['sometimes', Rule::in(['monthly', 'quarterly', 'annually'])],
            'policy_start_date' => ['sometimes', 'date', 'before_or_equal:today'],
            'policy_term_years' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'indexation_rate' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:0.10'],
            'in_trust' => ['sometimes', 'boolean'],
            'beneficiaries' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
