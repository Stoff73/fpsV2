<?php

declare(strict_types=1);

namespace App\Http\Requests\Retirement;

use Illuminate\Foundation\Http\FormRequest;

class StoreDCPensionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // For POST (create), authorization is handled by middleware
        if ($this->isMethod('POST')) {
            return true;
        }

        // For PUT/PATCH (update), check if user owns the pension
        $pensionId = $this->route('id');
        if ($pensionId) {
            $pension = \App\Models\DCPension::find($pensionId);
            if ($pension && $pension->user_id !== $this->user()->id) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'scheme_name' => ['required', 'string', 'max:255'],
            'scheme_type' => ['required', 'in:workplace,sipp,personal'],
            'provider' => ['required', 'string', 'max:255'],
            'member_number' => ['nullable', 'string', 'max:255'],
            'current_fund_value' => ['required', 'numeric', 'min:0'],
            'annual_salary' => ['nullable', 'numeric', 'min:0'],
            'employee_contribution_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'employer_contribution_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'monthly_contribution_amount' => ['nullable', 'numeric', 'min:0'],
            'investment_strategy' => ['nullable', 'string', 'max:255'],
            'platform_fee_percent' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'retirement_age' => ['nullable', 'integer', 'min:55', 'max:75'],
            'projected_value_at_retirement' => ['nullable', 'numeric', 'min:0'],
        ];

        // For workplace pensions, require annual_salary if percentage contributions are provided
        if ($this->input('scheme_type') === 'workplace') {
            if ($this->filled('employee_contribution_percent') || $this->filled('employer_contribution_percent')) {
                $rules['annual_salary'][] = 'required';
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'scheme_name.required' => 'Please provide a scheme name.',
            'scheme_type.required' => 'Please select a scheme type.',
            'scheme_type.in' => 'Invalid scheme type. Must be workplace, SIPP, or personal.',
            'provider.required' => 'Please provide the pension provider name.',
            'current_fund_value.required' => 'Please enter the current fund value.',
            'annual_salary.required' => 'Annual salary is required for workplace pensions with percentage contributions.',
            'retirement_age.min' => 'Minimum retirement age is 55.',
            'retirement_age.max' => 'Maximum retirement age is 75.',
        ];
    }
}
