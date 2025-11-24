<?php

declare(strict_types=1);

namespace App\Http\Requests\Protection;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDisabilityPolicyRequest extends FormRequest
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
            'provider' => ['sometimes', 'string', 'max:255'],
            'policy_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'benefit_amount' => ['sometimes', 'numeric', 'min:100', 'max:9999999.99'],
            'benefit_frequency' => ['sometimes', Rule::in(['monthly', 'weekly'])],
            'deferred_period_weeks' => ['sometimes', 'integer', 'min:0', 'max:104'],
            'benefit_period_months' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:720'],
            'premium_amount' => ['sometimes', 'numeric', 'min:0', 'max:9999999.99'],
            'premium_frequency' => ['sometimes', Rule::in(['monthly', 'quarterly', 'annually'])],
            'occupation_class' => ['sometimes', 'nullable', 'string', 'max:255'],
            'policy_start_date' => ['sometimes', 'date', 'before_or_equal:today'],
            'policy_end_date' => ['sometimes', 'nullable', 'date', 'after:policy_start_date'],
            'policy_term_years' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:50'],
            'coverage_type' => ['sometimes', Rule::in(['accident_only', 'accident_and_sickness'])],
        ];
    }
}
