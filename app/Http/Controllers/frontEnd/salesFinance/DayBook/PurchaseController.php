<?php

namespace App\Http\Controllers\frontend\salesFinance\DayBook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Supplier;
use Illuminate\Support\Carbon;
use App\Models\PurchaseExpenses;

use App\Services\DayBook\PurchaseDayBookService;

use App\Http\Requests\Daybook\PurchaseDayBookRequest;
use App\Models\DayBook\PurchaseDayBook;
use App\Models\DayBook\SalesDayBook;
use App\Home;

class PurchaseController extends Controller
{
    
    protected $purchaseDayBookService;

    public function __construct(PurchaseDayBookService $purchaseService)
    {
        $this->purchaseDayBookService = $purchaseService;
    }

    public function index(){
        $data['page'] = "dayBook";
    
        return view('frontEnd.salesAndFinance.purchase.purchase_day_book', $data);
    }

    public function getPurchaseDayBook(Request $request){
        $home_id = Auth::user()->home_id;
        $purchaseDayBooks = $this->purchaseDayBookService->getPurchaseDayBook($home_id, $request);

        return response()->json([
            'success' => (bool) $purchaseDayBooks,
            'data' => $purchaseDayBooks ? $purchaseDayBooks : 'No data'
        ]);
    }

    // public function create(){
    //     $data['page'] = "dayBook";
    //     $data['taxRates'] = Construction_tax_rate::getAllTax_rate(Auth::user()->home_id, "Active");
    //     $data['suppliers'] = Supplier::getActiveSuppliers(Auth::user()->home_id, Auth::user()->id);
    //     $data['purchase_expenses'] = PurchaseExpenses::where('deleted_at', null)->where('status', true)->get();
    //     // dd($data);
    //     return view('frontEnd.salesAndFinance.purchase.purchase_day_book_form', $data);
    // }

    public function store(PurchaseDayBookRequest $request, PurchaseDayBookService $service)
    {

        $data = $request->validated();
        $data['home_id'] = Auth::user()->home_id;    
       
        try {
            $response = $service->save($data);
        } catch (\Exception $e) {
            return response()->json([  'success' => false, 'message' => 'Unable to save purchase day book.', 'data' => $e->getMessage()], 200);
        }

        if ($response->wasRecentlyCreated) {
            return response()->json([  'success' => true, 'message' => 'Purchase day book record created successfully!', 'data' => $response], 201);
        } elseif ($response->wasChanged()) {
            return response()->json([  'success' => true, 'message' => 'Purchase day book record updated successfully!', 'data' => $response], 200);
        } else {
            return response()->json([  'success' => false, 'message' => 'No changes made.', 'data' => $response], 200);
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

    
    public function deletePurchaseExpenses($id ){
        $purchaseExpenses = PurchaseExpenses::findOrFail($id);
        $purchaseExpenses->deleted_at = Carbon::now(); // Update deleted_at with current timestamp
        $purchaseExpenses->save();

        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully!',
            'deleted_at' => $purchaseExpenses->deleted_at->format('Y-m-d H:i:s')
        ]);
    }

    

    // public function editPurchaseDayBook($id){
    //     $data['purchaseBook'] = PurchaseDayBook::findOrFail($id);
    //     // dd($data);
    //     $data['suppliers'] = Supplier::getActiveSuppliers(Auth::user()->home_id, Auth::user()->id);
    //     $data['taxRates'] = Construction_tax_rate::getAllTax_rate(Auth::user()->home_id, "Active");
    //     $data['purchase_expenses'] = PurchaseExpenses::where('deleted_at', null)->where('status', true)->get();
    //     return view('frontEnd.salesAndFinance.purchase.purchase_day_book_form', $data);
    // }

    public function purchase_type(){

        $data['purchase_expenses'] = PurchaseExpenses::where('deleted_at',  null)->get();

        return view('frontEnd.salesAndFinance.purchase.purchase_expenses', $data);
    }

    public function save_purchase_expenses(Request $request){

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        $data = PurchaseExpenses::updateOrCreate(['id' => $request['purchase_expense_id']], $validatedData);

        if(empty($request['purchase_expense_id'])){
            return response()->json([
                'success' => (bool) $data,
                'message' => $data ? "Purchase expenses type added successfully! " : 'Failed to save purchase expenses type'
            ]);
        } else {
            return response()->json([
                'success' => (bool) $data,
                'message' => $data ? "Purchase expenses type edited successfully! " : 'Failed to edit purchase expenses type'
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

    public function getSupplierData(){
        $data = Supplier::getActiveSuppliers(Auth::user()->home_id, Auth::user()->id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data'
        ]);
    }

    public function getPurchaseExpense(){
        $data = PurchaseExpenses::where('deleted_at', null)->where('status', true)->get();
        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data'
        ]);
    }

}


