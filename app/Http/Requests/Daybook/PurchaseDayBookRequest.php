<?php

namespace App\Http\Requests\Daybook;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseDayBookRequest extends FormRequest
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
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'netAmount' => 'required|numeric|min:0',
            'vatAmount' => 'required|numeric|min:0',
            'Vat' => 'required|exists:construction_tax_rates,id',
            'grossAmount' => 'required|numeric|min:0',
            'purchase_day_book_id' => 'nullable|numeric|min:0',
            'reclaim'       => 'nullable|numeric|min:0',
            'not_reclaim'   => 'nullable|numeric|min:0',
            'expense_type'  => 'nullable|numeric|min:0',
            'expense_amount'  => 'nullable|numeric|min:0',
        ];
    }
}
