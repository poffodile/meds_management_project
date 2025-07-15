<?php

namespace App\Http\Controllers\backEnd\salesfinance\DayBook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DayBook\SalesDayBookService;
use App\Http\Requests\Daybook\SalesDayBookRequest;
use Illuminate\Support\Facades\Session;

class SalesBackendController extends Controller
{

    protected $salesDayBookService;

    public function __construct(SalesDayBookService $salesService)
    {
        $this->salesDayBookService = $salesService;
    }


    public function index(){
        $data['page'] = "salesDayBook";
        $data['salesDayBooks'] = $this->salesDayBookService->getSalesDayBook(Session::get('scitsAdminSession')->home_id, request());
        return view('backEnd.salesFinance.DayBook.sales.sales_day_book', $data);   
    }
    public function create(){
        $data['page'] = "salesDayBook";
        return view('backEnd.salesFinance.DayBook.sales.sales_day_book_form', $data);
    }

    
    public function store(SalesDayBookRequest $request)
    {
        $data = $request->validated();
        $data['home_id'] = Session::get('scitsAdminSession')->home_id;

        try {
            $record = $this->salesDayBookService->save($data);
        } catch (\Exception $e) {
            \Log::error('SalesDayBook save error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $data, // optional: log the input data
            ]);
            return redirect()->back()->withInput()->with('error', 'Unable to save purchase day book.', $e->getMessage());
        }

        return redirect()->route('backend.sales_day_book.index')
            ->with('success', $record->wasRecentlyCreated ? 'Record Created!' : 'Record Updated!');
    }

    public function salesDayBookDelete($id)
    {
        $deletedAt = $this->salesDayBookService->deleteSalesDayBook($id);

        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully!',
            'deleted_at' => $deletedAt 
        ]);
    }

    public function getSalesDayBook()
    {
        $home_id = Session::get('scitsAdminSession')->home_id;
        $salesDayBooks = $this->salesDayBookService->getSalesDayBook($home_id, request());

        return response()->json([
            'success' => (bool) $salesDayBooks,
            'data' => $salesDayBooks ? $salesDayBooks : 'No data'
        ]);

    }
    
  
    public function salesDayBookEdit($id)
    {
        $data['page'] = "salesDayBook";
        $data['sales_day_book'] = $this->salesDayBookService->getSalesDayBookById($id);
        // dd($data['salesDayBook']);
        return view('backEnd.salesFinance.DayBook.sales.sales_day_book_form', $data);
    }

}
