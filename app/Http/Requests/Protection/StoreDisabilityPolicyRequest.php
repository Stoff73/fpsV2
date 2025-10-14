<?php

declare(strict_types=1);

namespace App\Http\Requests\Protection;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDisabilityPolicyRequest extends FormRequest
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
            'provider' => ['required', 'string', 'max:255'],
            'policy_number' => ['nullable', 'string', 'max:255'],
            'benefit_amount' => ['required', 'numeric', 'min:100', 'max:9999999.99'],
            'benefit_frequency' => ['required', Rule::in(['monthly', 'weekly'])],
            'deferred_period_weeks' => ['required', 'integer', 'min:0', 'max:104'],
            'benefit_period_months' => ['nullable', 'integer', 'min:1', 'max:720'],
            'premium_amount' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'premium_frequency' => ['required', Rule::in(['monthly', 'quarterly', 'annually'])],
            'occupation_class' => ['nullable', 'string', 'max:255'],
            'policy_start_date' => ['required', 'date', 'before_or_equal:today'],
            'policy_term_years' => ['nullable', 'integer', 'min:1', 'max:50'],
            'coverage_type' => ['required', Rule::in(['accident_only', 'accident_and_sickness'])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'provider.required' => 'Provider is required.',
            'benefit_amount.required' => 'Benefit amount is required.',
            'benefit_amount.min' => 'Benefit amount must be at least £100.',
            'benefit_frequency.required' => 'Benefit frequency is required.',
            'deferred_period_weeks.required' => 'Deferred period is required.',
            'premium_amount.required' => 'Premium amount is required.',
            'premium_frequency.required' => 'Premium frequency is required.',
            'policy_start_date.required' => 'Policy start date is required.',
            'coverage_type.required' => 'Coverage type is required.',
        ];
    }
}
