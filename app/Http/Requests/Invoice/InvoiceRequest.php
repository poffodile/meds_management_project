<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
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
            'quote_id' => 'required|integer|exists:quotes,id',
            'customer_id' => 'required|integer|exists:customers,id',
            'invoice_date' => 'required|date_format:d/m/Y',
            'due_date' => 'required|date',
            'line_item' => 'required|string',
            'description' => 'required|string',
            'deposit_percentage' => 'required|integer',
            'sub_total' => 'required|numeric',
            'VAT_id' => 'required|integer',
            'VAT_amount' => 'required|numeric',
            'total' => 'required|numeric'
        ];
    }
}
