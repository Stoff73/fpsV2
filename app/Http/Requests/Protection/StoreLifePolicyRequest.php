<?php

declare(strict_types=1);

namespace App\Http\Requests\Protection;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLifePolicyRequest extends FormRequest
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
        $rules = [
            'policy_type' => ['required', Rule::in(['term', 'whole_of_life', 'decreasing_term', 'family_income_benefit', 'level_term'])],
            'provider' => ['required', 'string', 'max:255'],
            'policy_number' => ['nullable', 'string', 'max:255'],
            'sum_assured' => ['required', 'numeric', 'min:1000', 'max:9999999999999.99'],
            'premium_amount' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'premium_frequency' => ['required', Rule::in(['monthly', 'quarterly', 'annually'])],
            'in_trust' => ['required', 'boolean'],
            'beneficiaries' => ['nullable', 'string', 'max:1000'],
        ];

        // Conditional rules based on policy type
        $policyType = $this->input('policy_type');

        if ($policyType === 'decreasing_term') {
            // Decreasing policies require start value, decreasing rate, start date, and term
            $rules['start_value'] = ['required', 'numeric', 'min:1000', 'max:9999999999999.99'];
            $rules['decreasing_rate'] = ['required', 'numeric', 'min:0', 'max:1'];
            $rules['policy_start_date'] = ['required', 'date', 'before_or_equal:today'];
            $rules['policy_term_years'] = ['required', 'integer', 'min:1', 'max:50'];
        } elseif ($policyType === 'term' || $policyType === 'level_term' || $policyType === 'family_income_benefit') {
            // Term policies require start date and term, but not decreasing fields
            $rules['policy_start_date'] = ['required', 'date', 'before_or_equal:today'];
            $rules['policy_term_years'] = ['required', 'integer', 'min:1', 'max:50'];
            $rules['start_value'] = ['nullable'];
            $rules['decreasing_rate'] = ['nullable'];
        } elseif ($policyType === 'whole_of_life') {
            // Whole of life policies don't require dates or term
            $rules['policy_start_date'] = ['nullable', 'date', 'before_or_equal:today'];
            $rules['policy_term_years'] = ['nullable', 'integer', 'min:1', 'max:50'];
            $rules['start_value'] = ['nullable'];
            $rules['decreasing_rate'] = ['nullable'];
        }

        // Always allow indexation_rate regardless of policy type
        $rules['indexation_rate'] = ['nullable', 'numeric', 'min:0', 'max:0.10'];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'policy_type.required' => 'Policy type is required.',
            'policy_type.in' => 'Invalid policy type selected.',
            'provider.required' => 'Provider is required.',
            'sum_assured.required' => 'Sum assured is required.',
            'sum_assured.min' => 'Sum assured must be at least £1,000.',
            'premium_amount.required' => 'Premium amount is required.',
            'premium_frequency.required' => 'Premium frequency is required.',
            'policy_start_date.required' => 'Policy start date is required.',
            'policy_term_years.required' => 'Policy term is required.',
        ];
    }
}
