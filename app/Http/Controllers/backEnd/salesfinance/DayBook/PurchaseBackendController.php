<?php

namespace App\Http\Controllers\backEnd\salesfinance\DayBook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Construction_tax_rate;
use App\Models\PurchaseExpenses;
use App\Http\Requests\Daybook\PurchaseDayBookRequest;
use App\Services\DayBook\PurchaseDayBookService;
use App\Home;
use App\Models\DayBook\PurchaseDayBook;

use Illuminate\Support\Facades\Log;
use App\Models\DayBook\SalesDayBook;
use Illuminate\Support\Facades\Session;

class PurchaseBackendController extends Controller
{

    protected $purchaseDayBookService;

    public function __construct(PurchaseDayBookService $purchaseService)
    {
        $this->purchaseDayBookService = $purchaseService;
    }

    // Purchase Expenses Type 
    public function purchase_type()
    {
        $data['page'] = "purchaseExpenese";
        $data['purchase_expenses'] = PurchaseExpenses::where('deleted_at', null)->get();
        return view('backEnd.salesFinance.DayBook.purchase_expenses', $data);
    }

    public function purchase_type_add()
    {
        $data['page'] = "purchaseExpenese";
        return view('backEnd.salesFinance.DayBook.purchase_expenses_form', $data);
    }

    public function save_purchase_expenses_type(Request $request)
    {
        // dd($request->all());
        $data['page'] = "purchaseExpenese";
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        $data = PurchaseExpenses::updateOrCreate(['id' => $request['purchase_expense_id']], $validatedData);

        if (empty($request['purchase_expense_id'])) {
            if ($data) {
                return redirect()->route('backend.purchase_expenses_type')->with('success', 'Purchase expenses type added successfully!');
            } else {
                return redirect()->route('backend.purchase_expenses_type_add')->with('error', 'Failed to save purchase expenses type');
            }
        } else {
            if ($data) {
                return redirect()->route('backend.purchase_expenses_type')->with('success', 'Purchase expenses type edited successfully!');
            } else {
                return redirect()->route('backend.purchase_expenses_type_add')->with('error', 'Failed to edit purchase expenses type');
            }
        }
    }
    public function changeStatus(Request $request)
    {
        $data = PurchaseExpenses::find($request->id);
        if ($data) {
            $data->status = !$data->status;
            $data->save();
            return response()->json(['success' => true, 'message' => 'Status changed successfully!']);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to change status']);
        }
    }
    public function deletePurchaseExpensesType($id)
    {
        try {
            $expense = PurchaseExpenses::findOrFail($id); // Ensure the record exists
            $expense->delete(); // Delete the record
            return redirect()->back()->with('success', 'Purchase expense type deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete purchase expense type.');
        }
    }
    public function editPurchaseExpensesType($id)
    {
        $data['page'] = "dayBook";
        $data['purchase_expenses'] = PurchaseExpenses::findOrFail($id);
        return view('backEnd.salesFinance.DayBook.purchase_expenses_form', $data);
    }




    // Puchase Day Book
    public function index(Request $request)
    {
        $data['page'] = "dayBook";
        return view('backEnd.salesFinance.DayBook.purchase_day_book', $data);
    }

    public function getPurchaseDayBook(Request $request)
    {

        $home_id = Session::get('scitsAdminSession')->home_id;
        $purchaseDayBooks = $this->purchaseDayBookService->getPurchaseDayBook($home_id, $request);

        return response()->json([
            'success' => (bool) $purchaseDayBooks,
            'data' => $purchaseDayBooks ? $purchaseDayBooks : 'No data'
        ]);
    }

    public function create()
    {
        $data['page'] = "dayBook";
        return view('backEnd.salesFinance.DayBook.purchase_day_book_form', $data);
    }

    public function edit($id)
    {
        $data['page'] = "dayBook";
        $data['purchase_day_book'] = PurchaseDayBook::findOrFail($id);
        return view('backEnd.salesFinance.DayBook.purchase_day_book_form', $data);
    }

    public function purchaseDayBookEdit($id)
    {
        $data['page'] = "dayBook";
        $data['purchase_day_book'] = PurchaseDayBook::findOrFail($id);
        return view('backEnd.salesFinance.DayBook.purchase_day_book_form', $data);
    }
    public function purchaseDayBookDelete($id)
    {
        $purchaseBook = PurchaseDayBook::findOrFail($id);
        $purchaseBook->deleted_at = now();
        $purchaseBook->save();

        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully!',
            'deleted_at' => $purchaseBook->deleted_at->format('Y-m-d H:i:s')
        ]);
    }

    public function getSupplierData()
    {
        $data = Supplier::getActiveSuppliers(Session::get('scitsAdminSession')->home_id, null);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data'
        ]);
    }

    public function getPurchaseExpense()
    {
        $data = PurchaseExpenses::where('deleted_at', null)->where('status', true)->get();
        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data'
        ]);
    }

    public function getActiveTaxRate()
    {
        $data = Construction_tax_rate::getAllTax_rate(Session::get('scitsAdminSession')->home_id, "Active");

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }

    public function store(PurchaseDayBookRequest $request)
    {

        $data = $request->validated();
        $data['home_id'] = Session::get('scitsAdminSession')->home_id;
        $data['page'] = "dayBook";
        try {
            $record = $this->purchaseDayBookService->save($data);
        } catch (\Exception $e) {
            Log::error('PurchaseDayBook save error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $data, // optional: log the input data
            ]);
            return redirect()->back()->withInput()->with('error', 'Unable to save purchase day book.');
        }

        return redirect()->route('backend.purchase_day_book.index')
            ->with('success', $record->wasRecentlyCreated ? 'Record Created!' : 'Record Updated!');
    }

    public function purchase_day_book_reclaim_per()
    {
        return Home::where('id', Session::get('scitsAdminSession')->home_id)->value('is_registered');
    }

    public function reclaimPercantage()
    {
        $excepmt = SalesDayBook::Where('Vat', 1)->whereNull('deleted_at')->sum('netAmount');
        $standard = SalesDayBook::Where('Vat', 2)->whereNull('deleted_at')->sum('netAmount');

        $residual = (($standard) / ($standard + $excepmt)) * 100;
        // $reclaim  = round($residual, 2); 

        return response()->json([
            'success' => (bool) $excepmt,
            'data' => $residual ? $residual : 0
        ]);
    }
}
