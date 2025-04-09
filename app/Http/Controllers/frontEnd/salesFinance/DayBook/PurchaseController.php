<?php

namespace App\Http\Controllers\frontend\salesFinance\DayBook;

use App\Http\Controllers\Controller;
use App\Models\Construction_tax_rate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Supplier;
use Illuminate\Support\Carbon;
use App\Models\PurchaseExpenses;

use App\Http\Requests\Daybook\PurchaseDayBookRequest;
use App\Models\DayBook\PurchaseDayBook;
use App\Models\DayBook\SalesDayBook;
use App\Home;

class PurchaseController extends Controller
{
    public function index(){
        $data['page'] = "dayBook";
        $data['purchaseDayBook'] = PurchaseDayBook::join('suppliers', 'suppliers.id', '=', 'purchase_day_books.supplier_id')
        ->join('construction_tax_rates', 'construction_tax_rates.id', '=', 'purchase_day_books.Vat')
        ->leftjoin('purchase_expenses', 'purchase_expenses.id', '=', 'purchase_day_books.expense_type')
        ->where('purchase_day_books.home_id', Auth::user()->home_id)
        ->select('purchase_day_books.*', 'suppliers.name as customer_name', 'construction_tax_rates.name as tax_rate_name', 'purchase_expenses.title')
        ->whereNull('purchase_day_books.deleted_at')
        ->orderBy('purchase_day_books.created_at', 'desc')
        ->get();
        return view('frontEnd.salesAndFinance.purchase.purchase_day_book', $data);
    }

    public function create(){
        $data['page'] = "dayBook";
        $data['taxRates'] = Construction_tax_rate::getAllTax_rate(Auth::user()->home_id, "Active");
        $data['suppliers'] = Supplier::getActiveSuppliers(Auth::user()->home_id, Auth::user()->id);
        $data['purchase_expenses'] = PurchaseExpenses::where('deleted_at', null)->where('status', true)->get();
        // dd($data);
        return view('frontEnd.salesAndFinance.purchase.purchase_day_book_form', $data);
    }

    public function store(PurchaseDayBookRequest $request)
    {
        $data = $request->validated();

        $data['page'] = "dayBook";
        $response = PurchaseDayBook::updateOrCreate(['id' => $data['purchase_day_book_id'] ?? null],  array_merge($data, ['home_id' => Auth::user()->home_id]));

        if (isset($response->id)) {
            return redirect()->Route('purchase.purchaseDayBook');
        } else {
            return redirect()->Route('sales.puchaseDayBookCreate');
        }
    }

    public function deletePurchaseDayBook($id, ){
        $purchaseBook = PurchaseDayBook::findOrFail($id);
        $purchaseBook->deleted_at = Carbon::now(); // Update deleted_at with current timestamp
        $purchaseBook->save();

        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully!',
            'deleted_at' => $purchaseBook->deleted_at->format('Y-m-d H:i:s')
        ]);
    }

    public function editPurchaseDayBook($id){
        $data['purchaseBook'] = PurchaseDayBook::findOrFail($id);
        // dd($data);
        $data['suppliers'] = Supplier::getActiveSuppliers(Auth::user()->home_id, Auth::user()->id);
        $data['taxRates'] = Construction_tax_rate::getAllTax_rate(Auth::user()->home_id, "Active");
        $data['purchase_expenses'] = PurchaseExpenses::where('deleted_at', null)->where('status', true)->get();
        return view('frontEnd.salesAndFinance.purchase.purchase_day_book_form', $data);
    }

    public function purchase_expenses(){

        $data['purchase_expenses'] = PurchaseExpenses::where('deleted_at',  null)->get();

        return view('frontEnd.salesAndFinance.purchase.purchase_expenses', $data);
    }

    public function save_purchase_expenses(Request $request){

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $data = PurchaseExpenses::updateOrCreate(['id' => $request['purchase_expense_id']], $validatedData);

        if(empty($request['purchase_expense_id'])){
            return response()->json([
                'success' => (bool) $data,
                'message' => $data ? "Purchase expenses added successfully! " : 'Failed to save purchase expenses'
            ]);
        } else {
            return response()->json([
                'success' => (bool) $data,
                'message' => $data ? "Purchase expenses edited successfully! " : 'Failed to edit purchase expenses'
            ]);
        }
    }

    public function purchase_day_book_reclaim_per(){
        return Home::where('id', Auth::user()->home_id)->value('is_registered');
    }

    public function reclaimPercantage(){

        $excepmt = SalesDayBook::Where('Vat', 1)->whereNull('deleted_at')->sum('netAmount');
        $standard = SalesDayBook::Where('Vat', 2)->whereNull('deleted_at')->sum('netAmount');

        $residual = (($standard) / ($standard + $excepmt))*100;
        // $reclaim  = round($residual, 2); 

        return response()->json([
            'success' => (bool) $excepmt,
            'data' => $residual ? $residual : 0
        ]);
    }   

}


