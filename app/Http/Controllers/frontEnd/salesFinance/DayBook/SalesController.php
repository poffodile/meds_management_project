<?php

namespace App\Http\Controllers\frontend\salesFinance\DayBook;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Construction_tax_rate;
use App\Models\DayBook\SalesDayBook;
use Illuminate\Support\Carbon;

use App\Http\Requests\Daybook\SalesDayBookRequest;


class SalesController extends Controller
{
    public function index()
    {
        $data['customers'] = Customer::get_customer_list_Attribute(Auth::user()->home_id, 'ACTIVE');
        $data['taxRates'] = Construction_tax_rate::getAllTax_rate(Auth::user()->home_id, "Active");
        $data['salesDayBooks'] = SalesDayBook::join('customers', 'customers.id', '=', 'sales_day_books.customer_id')
            ->join('construction_tax_rates', 'construction_tax_rates.id', '=', 'sales_day_books.Vat')
            // ->where('sales_day_books.home_id', Auth::user()->home_id)
            ->select('sales_day_books.*', 'customers.name as customer_name', 'construction_tax_rates.name as tax_rate_name')
            ->whereNull('sales_day_books.deleted_at')
            ->orderBy('sales_day_books.created_at', 'desc')
            ->get();
        return view('frontEnd.salesAndFinance.sales.sales_day_book', $data);
    }

    public function getSalesDayBook(Request $request)
    {
        $query = SalesDayBook::join('customers', 'customers.id', '=', 'sales_day_books.customer_id')
            ->join('construction_tax_rates', 'construction_tax_rates.id', '=', 'sales_day_books.Vat')
            ->where('sales_day_books.home_id', Auth::user()->home_id)
            ->select('sales_day_books.*', 'customers.name as customer_name', 'construction_tax_rates.name as tax_rate_name')
            ->whereNull('sales_day_books.deleted_at')
            ->orderBy('sales_day_books.created_at', 'desc');

        if ($request->has('tax_rate') && $request->tax_rate != 0) {
            $query->where('sales_day_books.Vat', $request->tax_rate);
        }

        $salesDayBooks = $query->get();

        return response()->json([
            'success' => (bool)$salesDayBooks,
            'data' => $salesDayBooks ? $salesDayBooks : 'No data'
        ]);
    }

    public function store(SalesDayBookRequest $request)
    {
        $data = $request->validated();
        $convertedDate = Carbon::createFromFormat('d-m-Y', $data['date'])->format('Y-m-d');
        $data['date'] = $convertedDate;
        $data['home_id'] = Auth::user()->home_id;
        $response = SalesDayBook::updateOrCreate(['id' => $data['sales_day_book_id'] ?? null],  $data);

        if ($response->wasRecentlyCreated) {
            return response()->json([  'success' => true, 'message' => 'Sales day book record created successfully!', 'data' => $response], 201);
        } elseif ($response->wasChanged()) {
            return response()->json([  'success' => true, 'message' => 'Sales day book record updated successfully!', 'data' => $response], 200);
        } else {
            return response()->json([  'success' => false, 'message' => 'No changes made.', 'data' => $response], 200);
        }
    }

    public function deleteSalesDayBook($id)
    {
        $salesBook = SalesDayBook::findOrFail($id);
        $salesBook->deleted_at = Carbon::now(); // Update deleted_at with current timestamp
        $salesBook->save();

        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully!',
            'deleted_at' => $salesBook->deleted_at->format('Y-m-d H:i:s')
        ]);
    }

    // public function editSalesDayBook($id){
    //     $data['salesBook'] = SalesDayBook::findOrFail($id);
    //     $data['customers'] = Customer::get_customer_list_Attribute(Auth::user()->home_id, 'ACTIVE');
    //     $data['taxRates'] = Construction_tax_rate::getAllTax_rate(Auth::user()->home_id, "Active");
    //     return view('frontEnd.salesAndFinance.sales.sales_day_book_form', $data);
    // }

    
    // public function create()
    // {
    //     $data['page'] = "dayBook";
    //     $data['customers'] = Customer::get_customer_list_Attribute(Auth::user()->home_id, 'ACTIVE');
    //     $data['taxRates'] = Construction_tax_rate::getAllTax_rate(Auth::user()->home_id, "Active");
    //     return view('frontEnd.salesAndFinance.sales.sales_day_book_form', $data);
    // }

}
