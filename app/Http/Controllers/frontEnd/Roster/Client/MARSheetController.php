<?php

namespace App\Http\Controllers\frontEnd\Roster\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Services\Staff\MARSheetService;

class MARSheetController extends Controller
{
    protected $marSheetService;

    public function __construct(MARSheetService $marSheetService)
    {
        $this->marSheetService = $marSheetService;
    }

    private function getHomeId(): int
    {
        return (int) explode(',', Auth::user()->home_id)[0];
    }

    public function list(Request $request)
    {
        $request->validate([
            'client_id' => 'required|integer',
            'status' => 'nullable|in:active,discontinued,all',
        ]);

        try {
            $homeId = $this->getHomeId();
            $status = $request->input('status', 'all');
            $data = $this->marSheetService->list(
                (int) $request->input('client_id'),
                $homeId,
                $status === 'all' ? null : $status
            );

            return response()->json([
                'success' => true,
                'message' => 'MAR sheets loaded',
                'data' => $data->items(),
                'total' => $data->total(),
                'next_page_url' => $data->nextPageUrl(),
                'prev_page_url' => $data->previousPageUrl(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    public function save(Request $request)
    {
        $request->validate([
            'client_id'              => 'required|integer',
            'medication_name'        => 'required|string|max:255',
            'dosage'                 => 'nullable|string|max:100',
            'dose'                   => 'nullable|string|max:100',
            'route'                  => 'nullable|string|max:100',
            'frequency'              => 'nullable|string|max:255',
            'time_slots'             => 'nullable|array',
            'time_slots.*'           => 'string|max:10',
            'as_required'            => 'nullable|boolean',
            'prn_details'            => 'nullable|string|max:2000',
            'reason_for_medication'  => 'nullable|string|max:2000',
            'prescribed_by'          => 'nullable|string|max:255',
            'prescriber'             => 'nullable|string|max:255',
            'pharmacy'               => 'nullable|string|max:255',
            'start_date'             => 'nullable|date',
            'end_date'               => 'nullable|date|after_or_equal:start_date',
            'stock_level'            => 'nullable|integer|min:0',
            'reorder_level'          => 'nullable|integer|min:0',
            'quantity_received'      => 'nullable|integer|min:0',
            'quantity_carried_forward' => 'nullable|integer|min:0',
            'quantity_returned'      => 'nullable|integer|min:0',
            'storage_requirements'   => 'nullable|string|max:1000',
            'allergies_warnings'     => 'nullable|string|max:1000',
        ]);

        try {
            $homeId = $this->getHomeId();
            $userId = (int) Auth::user()->id;
            $data = $request->only([
                'client_id', 'medication_name', 'dosage', 'dose', 'route', 'frequency',
                'time_slots', 'as_required', 'prn_details', 'reason_for_medication',
                'prescribed_by', 'prescriber', 'pharmacy', 'start_date', 'end_date',
                'stock_level', 'reorder_level', 'quantity_received', 'quantity_carried_forward',
                'quantity_returned', 'storage_requirements', 'allergies_warnings',
            ]);

            $sheet = $this->marSheetService->store($data, $homeId, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Prescription saved successfully',
                'data' => $sheet,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'id'                     => 'required|integer',
            'medication_name'        => 'nullable|string|max:255',
            'dosage'                 => 'nullable|string|max:100',
            'dose'                   => 'nullable|string|max:100',
            'route'                  => 'nullable|string|max:100',
            'frequency'              => 'nullable|string|max:255',
            'time_slots'             => 'nullable|array',
            'time_slots.*'           => 'string|max:10',
            'as_required'            => 'nullable|boolean',
            'prn_details'            => 'nullable|string|max:2000',
            'reason_for_medication'  => 'nullable|string|max:2000',
            'prescribed_by'          => 'nullable|string|max:255',
            'prescriber'             => 'nullable|string|max:255',
            'pharmacy'               => 'nullable|string|max:255',
            'start_date'             => 'nullable|date',
            'end_date'               => 'nullable|date|after_or_equal:start_date',
            'stock_level'            => 'nullable|integer|min:0',
            'reorder_level'          => 'nullable|integer|min:0',
            'quantity_received'      => 'nullable|integer|min:0',
            'quantity_carried_forward' => 'nullable|integer|min:0',
            'quantity_returned'      => 'nullable|integer|min:0',
            'storage_requirements'   => 'nullable|string|max:1000',
            'allergies_warnings'     => 'nullable|string|max:1000',
        ]);

        try {
            $homeId = $this->getHomeId();
            $data = $request->only([
                'medication_name', 'dosage', 'dose', 'route', 'frequency',
                'time_slots', 'as_required', 'prn_details', 'reason_for_medication',
                'prescribed_by', 'prescriber', 'pharmacy', 'start_date', 'end_date',
                'stock_level', 'reorder_level', 'quantity_received', 'quantity_carried_forward',
                'quantity_returned', 'storage_requirements', 'allergies_warnings',
            ]);

            $sheet = $this->marSheetService->update((int) $request->input('id'), $data, $homeId);

            if (!$sheet) {
                return response()->json(['success' => false, 'message' => 'Prescription not found'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Prescription updated successfully',
                'data' => $sheet,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    public function details(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        try {
            $homeId = $this->getHomeId();
            $sheet = $this->marSheetService->details((int) $request->input('id'), $homeId);

            if (!$sheet) {
                return response()->json(['success' => false, 'message' => 'Prescription not found'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Prescription details loaded',
                'data' => $sheet,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        if (Auth::user()->user_type !== 'A') {
            return response()->json(['success' => false, 'message' => 'Only administrators can delete prescriptions'], 403);
        }

        try {
            $homeId = $this->getHomeId();
            $deleted = $this->marSheetService->delete((int) $request->input('id'), $homeId);

            if (!$deleted) {
                return response()->json(['success' => false, 'message' => 'Prescription not found'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Prescription deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    public function discontinue(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'discontinued_reason' => 'nullable|string|max:2000',
        ]);

        try {
            $homeId = $this->getHomeId();
            $data = $request->only(['discontinued_reason']);
            $sheet = $this->marSheetService->discontinue((int) $request->input('id'), $data, $homeId);

            if (!$sheet) {
                return response()->json(['success' => false, 'message' => 'Prescription not found or already discontinued'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Prescription discontinued successfully',
                'data' => $sheet,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    public function administer(Request $request)
    {
        $request->validate([
            'mar_sheet_id'  => 'required|integer',
            'date'          => 'required|date',
            'time_slot'     => 'required|string|max:10',
            'code'          => 'required|in:A,S,R,W,N,O',
            'dose_given'    => 'nullable|string|max:100',
            'witnessed_by'  => 'nullable|string|max:255',
            'notes'         => 'nullable|string|max:2000',
        ]);

        try {
            $homeId = $this->getHomeId();
            $userId = (int) Auth::user()->id;
            $data = $request->only(['date', 'time_slot', 'code', 'dose_given', 'witnessed_by', 'notes']);

            $admin = $this->marSheetService->administer(
                (int) $request->input('mar_sheet_id'),
                $data,
                $homeId,
                $userId
            );

            if (!$admin) {
                return response()->json(['success' => false, 'message' => 'Prescription not found'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Administration recorded successfully',
                'data' => $admin,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    public function monthlyGrid(Request $request)
    {
        $request->validate([
            'client_id' => 'required|integer',
            'year'      => 'required|integer|min:2020|max:2099',
            'month'     => 'required|integer|min:1|max:12',
        ]);

        try {
            $homeId = $this->getHomeId();
            $data = $this->marSheetService->getMonthlyGrid(
                (int) $request->input('client_id'),
                $homeId,
                (int) $request->input('year'),
                (int) $request->input('month')
            );

            return response()->json([
                'success' => true,
                'message' => 'Monthly grid loaded',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    public function updateStock(Request $request)
    {
        $request->validate([
            'id'                       => 'required|integer',
            'quantity_received'        => 'nullable|integer|min:0',
            'quantity_carried_forward' => 'nullable|integer|min:0',
            'quantity_returned'        => 'nullable|integer|min:0',
        ]);

        try {
            $homeId = $this->getHomeId();
            $data = $request->only(['quantity_received', 'quantity_carried_forward', 'quantity_returned']);
            $sheet = $this->marSheetService->updateStock((int) $request->input('id'), $data, $homeId);

            if (!$sheet) {
                return response()->json(['success' => false, 'message' => 'Prescription not found'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully',
                'data' => $sheet,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    public function printGrid(Request $request, $clientId, $year, $month)
    {
        $homeId = $this->getHomeId();

        $clientId = (int) $clientId;
        $year = (int) $year;
        $month = (int) $month;

        if ($year < 2020 || $year > 2099 || $month < 1 || $month > 12) {
            abort(404);
        }

        $client = \DB::table('service_user')
            ->where('id', $clientId)
            ->where('home_id', $homeId)
            ->first();

        if (!$client) {
            abort(404);
        }

        $gridData = $this->marSheetService->getMonthlyGrid($clientId, $homeId, $year, $month);

        return view('frontEnd.roster.client.mar_print', [
            'client' => $client,
            'gridData' => $gridData,
            'year' => $year,
            'month' => $month,
        ]);
    }

    public function administrationGrid(Request $request)
    {
        $request->validate([
            'client_id' => 'required|integer',
            'date'      => 'required|date',
        ]);

        try {
            $homeId = $this->getHomeId();
            $sheets = $this->marSheetService->getAdministrationsForDate(
                (int) $request->input('client_id'),
                $homeId,
                $request->input('date')
            );

            return response()->json([
                'success' => true,
                'message' => 'Administration grid loaded',
                'data' => $sheets,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
        }
    }
}
