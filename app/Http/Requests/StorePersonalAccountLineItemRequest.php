<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePersonalAccountLineItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_type' => ['required', Rule::in(['profit_and_loss', 'cashflow', 'balance_sheet'])],
            'period_start' => ['required_if:account_type,profit_and_loss,cashflow', 'nullable', 'date'],
            'period_end' => ['required_if:account_type,profit_and_loss,cashflow', 'nullable', 'date', 'after_or_equal:period_start'],
            'line_item' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['income', 'expense', 'asset', 'liability', 'equity', 'cash_inflow', 'cash_outflow'])],
            'amount' => ['required', 'numeric', 'min:-9999999999.99', 'max:9999999999.99'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
