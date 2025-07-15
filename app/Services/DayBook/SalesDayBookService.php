<?php

namespace App\Services\DayBook;

use App\Models\DayBook\SalesDayBook;
use Illuminate\Support\Carbon;

class SalesDayBookService
{
    public function save($data)
    {
        $data['date'] = Carbon::createFromFormat('d-m-Y', $data['date'])->format('Y-m-d');
        return  SalesDayBook::updateOrCreate(['id' => $data['sales_day_book_id'] ?? null], $data);
         
    }

    public function getSalesDayBook($home_id, $request)
    {
        $query = SalesDayBook::join('customers', 'customers.id', '=', 'sales_day_books.customer_id')
            ->join('construction_tax_rates', 'construction_tax_rates.id', '=', 'sales_day_books.Vat')
            ->where('sales_day_books.home_id', $home_id)
            ->select('sales_day_books.*', 'customers.name as customer_name', 'construction_tax_rates.name as tax_rate_name')
            ->whereNull('sales_day_books.deleted_at')
            ->orderBy('sales_day_books.created_at', 'desc');

        if ($request->has('tax_rate') && $request->tax_rate != 0) {
            $query->where('sales_day_books.Vat', $request->tax_rate);
        }

        $salesDayBooks = $query->get();
        return $salesDayBooks;
    }
    public function getSalesDayBookById($id)
    {
        return SalesDayBook::find($id);
    }
    public function deleteSalesDayBook($id)
    {
        $salesBook = SalesDayBook::findOrFail($id);
        $salesBook->deleted_at = now(); // Update deleted_at with current timestamp
        $salesBook->save();

      return $salesBook->deleted_at->format('Y-m-d H:i:s');
    }
}
