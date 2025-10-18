<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
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
            'trust_id' => ['sometimes', 'nullable', 'exists:trusts,id'],
            'property_type' => ['sometimes', Rule::in(['main_residence', 'second_home', 'buy_to_let', 'commercial', 'land'])],
            'ownership_type' => ['sometimes', Rule::in(['sole', 'joint', 'trust'])],
            'ownership_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],

            // Address
            'address_line_1' => ['sometimes', 'string', 'max:255'],
            'address_line_2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:255'],
            'county' => ['sometimes', 'nullable', 'string', 'max:255'],
            'postcode' => ['sometimes', 'string', 'regex:/^[A-Z]{1,2}[0-9]{1,2}[A-Z]?\s?[0-9][A-Z]{2}$/i'],

            // Financial
            'purchase_date' => ['sometimes', 'date'],
            'purchase_price' => ['sometimes', 'numeric', 'min:0'],
            'current_value' => ['sometimes', 'numeric', 'min:0'],
            'valuation_date' => ['sometimes', 'nullable', 'date'],
            'sdlt_paid' => ['sometimes', 'nullable', 'numeric', 'min:0'],

            // Rental
            'monthly_rental_income' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'annual_rental_income' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'occupancy_rate_percent' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'tenant_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'lease_start_date' => ['sometimes', 'nullable', 'date'],
            'lease_end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:lease_start_date'],

            // Costs
            'annual_service_charge' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'annual_ground_rent' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'annual_insurance' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'annual_maintenance_reserve' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'other_annual_costs' => ['sometimes', 'nullable', 'numeric', 'min:0'],

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
