<?php

namespace App\Http\Controllers\frontEnd\salesFinance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;

use App\Http\Requests\QuoteRequest;
use App\Http\Requests\Quotes\CallBackRequest;
use App\Http\Requests\Quotes\QuoteTaskRequest;
use App\Http\Requests\Quotes\CustomerDepositRequest;
use App\Http\Requests\Invoice\InvoiceRequest;


use App\Services\Quotes\QuoteService;
use App\Services\Quotes\QuoteProductService;
use App\Services\Quotes\AttachmentTypeService;
use App\Services\Invoice\InvoiceService;

use App\Models\QuoteType;
// use App\Models\Quotes\CustomerDepositInvoice;
use App\Models\Quote;
use App\Models\QuoteSource;
use App\Models\Quotes\QuoteRejectType;
use App\Models\Customer_type;
use App\Models\Region;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Product_category;
use App\Models\AttachmentType;
use App\Models\Payment_type;
use App\Models\Task_type;
use App\Models\Tag;
use App\User;


class QuoteController extends Controller
{

    protected $quoteService;
    protected $itemService;
    protected $attachmentService;
    protected $invoiceService;

    public function __construct(QuoteService $quoteService, QuoteProductService $itemService, AttachmentTypeService $attachmentService, InvoiceService $invoiceService)
    {
        $this->quoteService = $quoteService;
        $this->itemService = $itemService;
        $this->attachmentService =  $attachmentService;
        $this->invoiceService = $invoiceService;
    }

    public function dashboard()
    {
        $data['page'] = "quotes";
        return view('frontEnd.salesAndFinance.quote.dashboard', $data);
    }
    public function create()
    {
        $data['page'] = "quotes";
        $data['quoteSource'] = QuoteSource::getAllQuoteSourcesHome(Auth::user()->home_id);
        $data['countries'] = Country::getCountriesNameCode();
        $data['product_categories'] = Product_category::activeProductCategory(Auth::user()->home_id);

        return view('frontEnd.salesAndFinance.quote.quote_form', $data);
    }
    public function index(Request $request)
    {
        $data['page'] = "quotes";
        $path = $request->path();
        $segments = explode('/', $path);
        $lastSegment = end($segments);
        $data['lastSegment'] = $lastSegment;
        $data['quotes'] = $this->quoteService->getQuoteData($lastSegment, Auth::user()->home_id);
        $data['draftCount'] = Quote::getDraftCount(Auth::user()->home_id);
        $data['callbackCount'] = Quote::getCallBackCount(Auth::user()->home_id);
        $data['acceptedCount'] = Quote::getAcceptedCount(Auth::user()->home_id);
        $data['actionedCount'] = Quote::getActionedCount(Auth::user()->home_id);
        $data['rejectedCount'] = Quote::getRejectedCount(Auth::user()->home_id);
        $data['class'] = in_array($lastSegment, ['draft', 'accepted', 'actioned', 'rejected']) ? "page-btn-selected" : null;
        // dd($data);
        if ($lastSegment == "draft") {
            $text = "Draft";
        } elseif ($lastSegment == "accepted") {
            $text = "Accepted";
        } elseif ($lastSegment == "actioned") {
            $text = "Actioned";
        } elseif ($lastSegment == "rejected") {
            $text = "Rejected";
        } else {
            $text = '';
        }
        $data['text'] = $text;

        if ($lastSegment == "rejected") {
            return view('frontEnd.salesAndFinance.quote.rejected', $data);
        }
        return view('frontEnd.salesAndFinance.quote.draft', $data);
    }

    // Quote Type
    public function quote_type()
    {
        $data['page'] = "setting";
        $data['quote_type'] = QuoteType::getAllQuoteType(Auth::user()->home_id);
        return view('frontEnd.salesAndFinance.quote.quote_type', $data);
    }

    public function saveQuoteType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        QuoteType::updateOrCreate(['id' => $request->quote_type_id],  array_merge($request->all(), ['home_id' => Auth::user()->home_id]));
        if (isset($request->quote_type_id)) {
            return response()->json(['success' => true, 'message' => 'Quote Type updated successfully']);
        } else {
            return response()->json(['success' => true, 'message' => 'Quote Type added successfully.']);
        }
    }

    public function deleteQuoteType(Request $request)
    {
        $record = QuoteType::find($request->id);
        if ($record) {
            $record->deleted_at = now();
            $record->save();  // Save the updated record
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => 'Record not found']);
        }
    }

    // Quote Sources
    public function quote_sources()
    {
        $data['page'] = "setting";
        $data['quote_sources'] = QuoteSource::getAllQuoteSources();
        return view('frontEnd.salesAndFinance.quote.quote_sources', $data);
    }

    public function saveQuoteSources(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        QuoteSource::updateOrCreate(['id' => $request->quote_source_id],  array_merge($request->all(), ['home_id' => Auth::user()->home_id]));
        if (isset($request->quote_source_id)) {
            return response()->json(['success' => true, 'message' => 'Quote Sources updated successfully.']);
        } else {
            return response()->json(['success' => true, 'message' => 'Quote Sources added successfully.']);
        }
    }

    public function deleteQuoteSource(Request $request)
    {
        $record = QuoteSource::find($request->id);
        if ($record) {
            $record->deleted_at = now();
            $record->save();  // Save the updated record
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => 'Record not found']);
        }
    }


    // Quote Reject Type
    public function quote_reject_type()
    {
        $data['page'] = "setting";
        $data['quote_reject_type'] = QuoteRejectType::getAllQuoteRejectType(Auth::user()->home_id);
        return view('frontEnd.salesAndFinance.quote.quote_reject_type', $data);
    }

    public function saveQuoteRejectType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        QuoteRejectType::updateOrCreate(['id' => $request->quote_reject_type_id],  array_merge($request->all(), ['home_id' => Auth::user()->home_id]));
        if (isset($request->quote_reject_type_id)) {
            return response()->json(['success' => true, 'message' => 'Quote Reject Type updated successfully.']);
        } else {
            return response()->json(['success' => true, 'message' => 'Quote Reject Type added successfully.']);
        }
    }

    public function deleteQuoteRejectType(Request $request)
    {
        $record = QuoteRejectType::find($request->id);
        if ($record) {
            $record->deleted_at = now();
            $record->save();  // Save the updated record
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => 'Record not found']);
        }
    }

    public function saveCustomerType(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $saveData = Customer_type::create(array_merge($request->all(), ['home_id' => Auth::user()->home_id]));
        if ($saveData) {
            return response()->json(['success' => true, 'message' => 'Customer Type added successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Error in customer Creation.']);
        }
    }

    public function getCustomerType()
    {
        $data = Customer_type::getCustomerType(Auth::user()->home_id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data'
        ]);
    }

    public function saveRegion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $saveData = Region::updateOrCreate(['id' => $request->id ?? null], array_merge($request->all(), ['home_id' => Auth::user()->home_id]));

        return response()->json([
            'success' => (bool) $saveData,
            'message' => $saveData ? 'Region added successfully.' : 'Error in region add.'
        ]);
    }

    public function getRegions()
    {
        $data = Region::getRegions(Auth::user()->home_id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data'
        ]);
    }

    public function getCurrencyData()
    {
        $data = Currency::getCurrencyData();

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data'
        ]);
    }

    public function getQuoteTypes()
    {
        $data = QuoteType::getActiveQuoteType(Auth::user()->home_id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data'
        ]);
    }

    public function store(QuoteRequest $request)
    {
        try {

            $qutRef = $request->quote_ref ?? $this->quoteService->generateQuoteRef();
            $quote = $this->quoteService->saveQuoteData($request->all(), $qutRef, Auth::user()->home_id, Auth::user()->id);

            if ($request->has('products')) {
                $item = $this->itemService->saveItems($request->input('products'), $quote->id);
            }

            if ($quote->wasRecentlyCreated) {
                $message =  'Quote created successfully.';
                return redirect()->route('quote.edit', ['id' => $quote->id])->with('success', $message);
            } else {
                $message =  'Quote updated successfully.';
                return redirect()->route('quote.edit', ['id' => $quote->id])->with('success', $message);
            }

            Log::info('This is an informational message.', [$quote]);
            $data = array();
            return view('frontEnd.salesAndFinance.quote.draft', $data);
        } catch (QueryException $e) {
            // Handle database error
            Log::error('This is an error message from db.', [$e->getMessage()]);
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            // Handle general errors
            Log::error('This is an error message.', [$e->getMessage()]);
            return response()->json(['error' => 'An error occurred: ' . $e], 500);
        }
    }

    public function edit($id)
    {
        $data['page'] = "quotes";
        $data['quoteSource'] = QuoteSource::getAllQuoteSourcesHome(Auth::user()->home_id);
        $data['countries'] = Country::getCountriesNameCode();
        $data['product_categories'] = Product_category::activeProductCategory(Auth::user()->home_id);
        $data['quoteData'] = $this->quoteService->getQuoteDataOnId($id);
        $data['attachment_type'] = AttachmentType::getActiveAttachmentType(Auth::user()->home_id);
        $data['users'] = User::getHomeUsers(Auth::user()->home_id);
        $data['loginCustomer'] = Auth::user()->id;
        $data['paymentType'] = Payment_type::getActivePaymentType(Auth::user()->home_id);
        // dd($data['attachment_type']);
        $data['type'] = 1;
        $data['taskType'] = Task_type::getAllAciveTask_type(Auth::user()->home_id);
        return view('frontEnd.salesAndFinance.quote.quote_edit', $data);
    }
    public function getHomeUsers()
    {
        $data = User::getHomeUsers(Auth::user()->home_id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data'
        ]);
    }
    public function details()
    {
        return view('frontEnd.salesAndFinance.quote.add_quote');
    }

    public function saveAttachmentData(Request $request)
    {
        // dd($request);
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,pdf|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $this->attachmentService->saveAttachmentType($request->all(), $request->file('image'));

        return response()->json([
            'success' => (bool) $data,
            'id' => $data->id,
            'data' => $data ? "Attachment saved successfully!" : 'Error in saving file'
        ]);
    }

    public function getAttachmentData(Request $request)
    {
        // dd($request);
        $validator = Validator::make($request->all(), [
            'attachment_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $this->attachmentService->getAttachmentType($request->attachment_id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data'
        ]);
    }

    public function add_multi_attachment(Request $request)
    {
        $data['quoteId'] = $request->query('quote_id');
        $data['quote_ref'] = Quote::where('id',  $request->query('quote_id'))->value('quote_ref');
        return view('frontEnd.salesAndFinance.quote.multi_file_uploader', $data);
    }

    public function getAttachmentList()
    {
        $data = AttachmentType::getActiveAttachmentType(Auth::user()->home_id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data'
        ]);
    }

    public function saveQuoteAttachments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        foreach ($request->file('image') as $index => $file) {

            $quote_id = $request->quote_id[$index];
            // Create an attachment entry
            $attachmentData = [
                'quote_id' => $request->quote_id[$index], // Replace with actual quote_id field
                'attachment_type' => $request->attachment_type[$index] ?? null,
                'title' => $request->title[$index] ?? null,
                'description' => $request->description[$index] ?? null,
                'mobile_user_visible' => $request->mobile_user_visible[$index] ?? null,
            ];

            $savedAttachment = $this->attachmentService->saveAttachmentType($attachmentData, $file);

            if (!$savedAttachment) {
                return response()->json(['success' => false,  'redirect_url' => '',  'data' => 'Failed to save attachment'], 500);
            }
        }
        // return redirect()->route('quote.edit', ['id' => $quote_id])->with('success', 'Quote saved successfully!');
        return response()->json(['success' => true,  'redirect_url' => route('quote.edit', ['id' => $quote_id]), 'data' => 'Quote attachments saved successfully!']);
    }

    public function getAttachmentDataOnQuoteId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quote_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $this->attachmentService->getAllAttachmentTypeOnQuote($request->quote_id);
        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data'
        ]);
    }

    public function deleteAttachment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $ids = $request->input('ids');

        $data = $this->attachmentService->deleteAttachment($ids);
        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? "Attachment deleted successfully !" : 'Failed to delete the row .'
        ]);
    }

    public function editQuoteDetails($id)
    {
        $data['page'] = "quotes";
        $data['quoteSource'] = QuoteSource::getAllQuoteSourcesHome(Auth::user()->home_id);
        $data['countries'] = Country::getCountriesNameCode();
        $data['product_categories'] = Product_category::activeProductCategory(Auth::user()->home_id);
        $data['quoteData'] = $this->quoteService->getQuoteDataOnId($id);
        // dd($data['quoteData']);
        
        $data['attachment_type'] = AttachmentType::getActiveAttachmentType(Auth::user()->home_id);
        $data['paymentType'] = Payment_type::getActivePaymentType(Auth::user()->home_id);
        $data['type'] = 2;
        $data['users'] = User::getHomeUsers(Auth::user()->home_id);
        $data['loginCustomer'] = Auth::user()->id;
        // dd($data);
        return view('frontEnd.salesAndFinance.quote.quote_edit', $data);
    }

    public function getQuoteProductList(Request $request)
    {
        // dd($request);
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $this->itemService->getQuoteProductData($request->id);
        // dd($data);
        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data'
        ]);
    }

    public function storeCallBackData(CallBackRequest $request)
    {
        $data = $this->quoteService->saveCallBack($request);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? "Quote Status changed to Call Back !" : 'Error in satus changed.'
        ]);
    }

    public function callBack(Request $request)
    {
        $data['page'] = "quotes";
        $path = $request->path();
        $segments = explode('/', $path);
        $lastSegment = end($segments);
        $data['lastSegment'] = $lastSegment;
        $data['quotes'] = $this->quoteService->getQuoteCallBack($lastSegment, Auth::user()->home_id);
        $data['draftCount'] = Quote::getDraftCount(Auth::user()->home_id);
        $data['callbackCount'] = Quote::getCallBackCount(Auth::user()->home_id);
        $data['acceptedCount'] = Quote::getAcceptedCount(Auth::user()->home_id);
        $data['actionedCount'] = Quote::getActionedCount(Auth::user()->home_id);
        $data['rejectedCount'] = Quote::getRejectedCount(Auth::user()->home_id);

        return view('frontEnd.salesAndFinance.quote.call_back', $data);
    }

    // QuoteTaskRequest
    public function saveQuoteTask(QuoteTaskRequest $request)
    {
        $validatedData = $request->validated();

        $quoteTask = $this->quoteService->saveQuoteTaskData($validatedData);

        // Update the record if it exists, otherwise create a new one
        // $quoteTask = QuoteTask::updateOrCreate(['id' => $request->edit_quote_task_id], $validatedData);

        Log::info('User logged in', ['user_id' => $quoteTask]);

        return response()->json([
            'success' => (bool) $quoteTask,
            'data' => $quoteTask ? "Task added successfully!" : 'Failed to save task.'
        ]);
    }
    public function getQuoteTaskList(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'quote_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $this->quoteService->getQuoteTaskList($request->quote_id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data'
        ]);
    }

    public function searchQuote(Request $request)
    {
        $data['page'] = "quotes";
        $path = $request->path();
        $segments = explode('/', $path);
        $lastSegment = end($segments);
        $data['lastSegment'] = $lastSegment;
        $data['quotes'] = $this->quoteService->getQuoteCallBack($lastSegment, Auth::user()->home_id);
        $data['draftCount'] = Quote::getDraftCount(Auth::user()->home_id);
        $data['callbackCount'] = Quote::getCallBackCount(Auth::user()->home_id);
        $data['acceptedCount'] = Quote::getAcceptedCount(Auth::user()->home_id);
        $data['actionedCount'] = Quote::getActionedCount(Auth::user()->home_id);
        $data['rejectedCount'] = Quote::getRejectedCount(Auth::user()->home_id);
        $data['users'] = User::getHomeUsers(Auth::user()->home_id);
        $data['quoteSources'] = QuoteSource::getAllQuoteSourcesHome(Auth::user()->home_id);
        $data['regions'] = Region::getRegions(Auth::user()->home_id);
        $data['tags'] = Tag::getActivetags(Auth::user()->home_id);
        $data['quoteTypes'] = QuoteType::getActiveQuoteType(Auth::user()->home_id);
        return view('frontEnd.salesAndFinance.quote.search_quote', $data);
    }

    public function statusChange(Request $request)
    {
        // dd($request);
        $validator = Validator::make($request->all(), [
            'quote_id' => 'required|integer',
            'status' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $this->quoteService->updateQuoteStatus($request->quote_id, $request->status);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'Error'
        ]);
    }

    public function getActiveRejectType()
    {

        $data = QuoteRejectType::getActiveQuoteRejectType(Auth::user()->home_id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'Error'
        ]);
    }

    public function saveQuoteRejectReasonsType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quote_id' => 'required|integer',
            'reject_reasons' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $data = $this->quoteService->saveQuoteRejectReasons($request->all());

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? 'Quote rejected' : 'Error in quote rejection'
        ]);
    }

    // CustomerDepositRequest
    public function saveQuoteDeposite(CustomerDepositRequest $request)
    {
        // dd($request->all());
        $validatedData = $request->validated();

        $data = $this->quoteService->saveQuoteCredit($validatedData);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? 'Quote deposite created' : 'Error in quote deposit'
        ]);
    }

    public function getDepositeData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quote_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data =  $this->quoteService->getDepositeData($request);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No Data'
        ]);
    }

    public function saveInvoiceDeposite(InvoiceRequest $request){

        $quote = Quote::where('id', $request->quote_id)->get();
        $quote = $quote->first();

        $validatedData = $request->validated();

        $invoice = $this->invoiceService->saveInvoiceData($quote, $validatedData, Auth::user()->home_id);

        // dd($invoice);
        $data = $this->quoteService->saveCustomerInvoiceDeposit($request, $invoice->id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? "save record" : 'No Data'
        ]);

    }

    public function getQuoteInvoiceDeposit(Request $request){

        $validator = Validator::make($request->all(), [
            'quote_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $this->quoteService->getCustomerInvoiceDeposit($request->quote_id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No Data'
        ]);
    }

    public function searchQuoteData(Request $request){
        // dd($request);

        $data = $this->quoteService->getSearchQuoteList($request);
        // dd($data);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No Data'
        ]);

    }
}
    