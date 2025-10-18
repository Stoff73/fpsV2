<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyRequest extends FormRequest
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
            'trust_id' => ['nullable', 'exists:trusts,id'],
            'property_type' => ['required', Rule::in(['main_residence', 'second_home', 'buy_to_let', 'commercial', 'land'])],
            'ownership_type' => ['required', Rule::in(['sole', 'joint', 'trust'])],
            'ownership_percentage' => ['required', 'numeric', 'min:0', 'max:100'],

            // Address
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'county' => ['nullable', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'regex:/^[A-Z]{1,2}[0-9]{1,2}[A-Z]?\s?[0-9][A-Z]{2}$/i'],

            // Financial
            'purchase_date' => ['required', 'date'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'current_value' => ['required', 'numeric', 'min:0'],
            'valuation_date' => ['nullable', 'date'],
            'sdlt_paid' => ['nullable', 'numeric', 'min:0'],

            // Rental (required for BTL)
            'monthly_rental_income' => ['nullable', 'numeric', 'min:0'],
            'annual_rental_income' => ['nullable', 'numeric', 'min:0'],
            'occupancy_rate_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'tenant_name' => ['nullable', 'string', 'max:255'],
            'lease_start_date' => ['nullable', 'date'],
            'lease_end_date' => ['nullable', 'date', 'after_or_equal:lease_start_date'],

            // Costs
            'annual_service_charge' => ['nullable', 'numeric', 'min:0'],
            'annual_ground_rent' => ['nullable', 'numeric', 'min:0'],
            'annual_insurance' => ['nullable', 'numeric', 'min:0'],
            'annual_maintenance_reserve' => ['nullable', 'numeric', 'min:0'],
            'other_annual_costs' => ['nullable', 'numeric', 'min:0'],

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
            'sdlt_paid' => 'SDLT paid',
            'annual_service_charge' => 'annual service charge',
            'annual_ground_rent' => 'annual ground rent',
            'annual_insurance' => 'annual insurance',
            'annual_maintenance_reserve' => 'annual maintenance reserve',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'postcode.regex' => 'The postcode must be a valid UK postcode.',
            'lease_end_date.after_or_equal' => 'The lease end date must be after or equal to the lease start date.',
        ];
    }
}
