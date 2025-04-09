<?php

namespace App\Http\Requests\Daybook;

use Illuminate\Foundation\Http\FormRequest;

class SalesDayBookRequest extends FormRequest
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
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'invoice_no' => 'required|string|max:255',
            'netAmount' => 'required|numeric|min:0',
            'vatAmount' => 'required|numeric|min:0',
            'Vat' => 'required|exists:construction_tax_rates,id',
            'grossAmount' => 'required|numeric|min:0',
            'sales_day_book_id' => 'nullable|numeric|min:0'
        ];
    }
}
