<?php

namespace App\Services\DayBook;

use App\Models\DayBook\PurchaseDayBook;
use Illuminate\Support\Carbon;

class PurchaseDayBookService
{
    public function save(array $data)
    {
        $data['date'] = Carbon::createFromFormat('d-m-Y', $data['date'])->format('Y-m-d');
        $response = PurchaseDayBook::updateOrCreate(['id' => $data['purchase_day_book_id'] ?? null], $data);
        return $response;
    }

    public function getPurchaseDayBook($home_id, $request)
    {

        $query = PurchaseDayBook::join('suppliers', 'suppliers.id', '=', 'purchase_day_books.supplier_id')
            ->join('construction_tax_rates', 'construction_tax_rates.id', '=', 'purchase_day_books.Vat')
            ->leftjoin('purchase_expenses', 'purchase_expenses.id', '=', 'purchase_day_books.expense_type')
            ->where('purchase_day_books.home_id', $home_id)
            ->select('purchase_day_books.*', 'suppliers.name as customer_name', 'construction_tax_rates.name as tax_rate_name', 'purchase_expenses.title')
            ->whereNull('purchase_day_books.deleted_at')
            ->orderBy('purchase_day_books.created_at', 'desc');

        if ($request->has('tax_rate') && $request->tax_rate != 0) {
            $query->where('purchase_day_books.Vat', $request->tax_rate);
        }

        $purchaseDayBooks = $query->get();
        return $purchaseDayBooks;
    }
}
