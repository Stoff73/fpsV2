<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMortgageRequest extends FormRequest
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
            'lender_name' => ['sometimes', 'string', 'max:255'],
            'mortgage_account_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'mortgage_type' => ['sometimes', Rule::in(['repayment', 'interest_only', 'part_and_part', 'mixed'])],
            'country' => ['sometimes', 'nullable', 'string', 'max:255'],

            // Mixed mortgage type fields (repayment vs interest-only split)
            'repayment_percentage' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'interest_only_percentage' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],

            // Loan details
            'original_loan_amount' => ['sometimes', 'numeric', 'min:0'],
            'outstanding_balance' => ['sometimes', 'numeric', 'min:0'],

            // Interest
            'interest_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'rate_type' => ['sometimes', Rule::in(['fixed', 'variable', 'tracker', 'discount', 'mixed'])],
            'rate_fix_end_date' => ['sometimes', 'nullable', 'date'],

            // Mixed rate type fields (fixed vs variable split)
            'fixed_rate_percentage' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'variable_rate_percentage' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'fixed_interest_rate' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'variable_interest_rate' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],

            // Payment
            'monthly_payment' => ['sometimes', 'numeric', 'min:0'],

            // Dates
            'start_date' => ['sometimes', 'date'],
            'maturity_date' => ['sometimes', 'date', 'after:start_date'],
            'remaining_term_months' => ['sometimes', 'nullable', 'integer', 'min:0'],

            // Notes
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'lender_name' => 'lender name',
            'rate_fix_end_date' => 'rate fix end date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'maturity_date.after' => 'The maturity date must be after the start date.',
        ];
    }
}
