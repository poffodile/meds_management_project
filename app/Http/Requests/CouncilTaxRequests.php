<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CouncilTaxRequests extends FormRequest
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
            'council_tax_id' => 'nullable|integer',
            'flat_number' => 'nullable|string|max:255',
            'address' => 'required|string|max:255',
            'post_code' => 'required|string|max:255',
            'council' => 'required|string|max:255',
            'no_of_bedrooms' => 'nullable|string|max:255',
            'owned_by_omega' => 'required|boolean',
            'occupancy' => 'nullable|string|max:255',
            'exempt' => 'required|boolean',
            'account_number' => 'required|string|max:255',
            'last_bill_date' => 'nullable|date',
            'bill_period_start_date' => 'nullable|date',
            'bill_period_end_date' => 'nullable|date|after:bill_period_start_date',
            'amount_paid' => 'nullable|string|max:255',
            'additional' => 'nullable|string|max:255',
        ];
    }
}
