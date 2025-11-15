<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMortgageRequest extends FormRequest
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
            'lender_name' => ['required', 'string', 'max:255'],
            'mortgage_account_number' => ['nullable', 'string', 'max:50'],
            'mortgage_type' => ['required', Rule::in(['repayment', 'interest_only', 'mixed'])],
            'country' => ['nullable', 'string', 'max:255'],

            // Mixed mortgage type fields (repayment vs interest-only split)
            'repayment_percentage' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:mortgage_type,mixed'],
            'interest_only_percentage' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:mortgage_type,mixed'],

            // Loan details - outstanding_balance and monthly_payment required
            'original_loan_amount' => ['nullable', 'numeric', 'min:0'],
            'outstanding_balance' => ['required', 'numeric', 'min:0'],

            // Interest - optional, default to 0 if not provided
            'interest_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'rate_type' => ['nullable', Rule::in(['fixed', 'variable', 'tracker', 'discount', 'mixed'])],
            'rate_fix_end_date' => ['nullable', 'date'],

            // Mixed rate type fields (fixed vs variable split)
            'fixed_rate_percentage' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:rate_type,mixed'],
            'variable_rate_percentage' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:rate_type,mixed'],
            'fixed_interest_rate' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:rate_type,mixed'],
            'variable_interest_rate' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:rate_type,mixed'],

            // Payment
            'monthly_payment' => ['required', 'numeric', 'min:0'],

            // Dates
            'start_date' => ['nullable', 'date'],
            'maturity_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'remaining_term_months' => ['nullable', 'integer', 'min:0'],

            // Ownership
            'ownership_type' => ['nullable', Rule::in(['individual', 'joint'])],
            'joint_owner_id' => ['nullable', 'exists:users,id'],
            'joint_owner_name' => ['nullable', 'string', 'max:255'],

            // Notes
            'notes' => ['nullable', 'string', 'max:1000'],
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
