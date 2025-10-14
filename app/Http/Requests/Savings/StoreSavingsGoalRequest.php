<?php

declare(strict_types=1);

namespace App\Http\Requests\Savings;

use Illuminate\Foundation\Http\FormRequest;

class StoreSavingsGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'goal_name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'current_saved' => 'nullable|numeric|min:0',
            'target_date' => 'required|date|after:today',
            'priority' => 'required|in:high,medium,low',
            'linked_account_id' => 'nullable|exists:savings_accounts,id',
            'auto_transfer_amount' => 'nullable|numeric|min:0',
        ];
    }
}
