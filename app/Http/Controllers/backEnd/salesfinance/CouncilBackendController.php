<?php

namespace App\Http\Controllers\backEnd\salesfinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CouncilTaxRequests;
use App\Services\Finance\CouncilTaxService;

class CouncilBackendController extends Controller
{
    protected $councilTaxService;

    // Inject the service into the controller
    public function __construct(CouncilTaxService $councilTaxService)
    {
        $this->councilTaxService = $councilTaxService;
    }

    public function index()
    {
        $data['page'] = "council_tax";
        $data['council_tax'] = $this->councilTaxService->getCouncilTax();
        return view('backEnd.salesFinance.council_tax.council_tax', $data);
    }

    public function create()
    {
        $data['page'] = "council_tax";
        return view('backEnd.salesFinance.council_tax.council_tax_form', $data);
    }

    public function store(CouncilTaxRequests $req)
    {
        $data = $req->validated();

        try {
            // Save or update the record using your service
            $response = $this->councilTaxService->saveCouncilTaxData($data);
        } catch (\Exception $e) {
            // Log complete exception details
            \Log::error('SalesDayBook save error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $data, // optional: log the input data
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'An error occurred while saving the Council Tax record. Please try again.');
        }

        return redirect()->route('backend.council_tax.index')
            ->with('success', $response->wasRecentlyCreated ? 'Record Created!' : 'Record Updated!');
    }

    public function councilTaxDelete($id)
    {
        try {
            $this->councilTaxService->deleteCouncilTax($id);
            return redirect()->back()->with('success', 'Council tax deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete council tax.');
        }

    }

    public function councilTaxEdit($id)
    {
        $data['page'] = "council_tax";
        $data['council_tax'] = $this->councilTaxService->getCouncilTaxById($id);
        return view('backEnd.salesFinance.council_tax.council_tax_form', $data);
    }
}
