<?php

declare(strict_types=1);

namespace App\Http\Requests\Investment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for efficient frontier calculation requests
 */
class CalculateEfficientFrontierRequest extends FormRequest
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
            'risk_free_rate' => 'nullable|numeric|min:0|max:0.15',
            'num_points' => 'nullable|integer|min:10|max:100',
            'account_ids' => 'nullable|array',
            'account_ids.*' => 'integer|exists:investment_accounts,id',
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
            'risk_free_rate.min' => 'Risk-free rate cannot be negative',
            'risk_free_rate.max' => 'Risk-free rate seems unrealistic (max 15%)',
            'num_points.min' => 'Number of points must be at least 10',
            'num_points.max' => 'Number of points cannot exceed 100 (performance limit)',
            'account_ids.array' => 'Account IDs must be provided as an array',
            'account_ids.*.exists' => 'One or more investment accounts do not exist',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'risk_free_rate' => 'risk-free rate',
            'num_points' => 'number of frontier points',
            'account_ids' => 'account IDs',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // If account_ids provided, ensure user owns all accounts
            if ($this->has('account_ids')) {
                $accountIds = $this->input('account_ids');
                $userId = $this->user()->id;

                $ownedAccounts = \App\Models\Investment\InvestmentAccount::where('user_id', $userId)
                    ->whereIn('id', $accountIds)
                    ->pluck('id')
                    ->toArray();

                $invalidAccounts = array_diff($accountIds, $ownedAccounts);

                if (! empty($invalidAccounts)) {
                    $validator->errors()->add(
                        'account_ids',
                        'You do not have permission to access one or more of the specified accounts'
                    );
                }
            }
        });
    }

    /**
     * Get validated data with defaults applied
     *
     * @return array
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Apply defaults if not provided
        $validated['risk_free_rate'] = $validated['risk_free_rate'] ?? 0.045; // UK Gilts ~4.5%
        $validated['num_points'] = $validated['num_points'] ?? 50;

        return $validated;
    }
}
