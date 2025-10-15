<?php

declare(strict_types=1);

namespace App\Http\Requests\Savings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSavingsAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_type' => 'sometimes|string|max:255',
            'institution' => 'sometimes|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'current_balance' => 'sometimes|numeric|min:0',
            'interest_rate' => 'sometimes|numeric|min:0|max:20',
            'access_type' => 'sometimes|in:immediate,notice,fixed',
            'notice_period_days' => 'nullable|integer|min:0',
            'maturity_date' => 'nullable|date',
            'is_isa' => 'sometimes|boolean',
            'isa_type' => 'nullable|in:cash,stocks_shares,LISA',
            'isa_subscription_year' => 'nullable|string',
            'isa_subscription_amount' => 'nullable|numeric|min:0',
        ];
    }
}
