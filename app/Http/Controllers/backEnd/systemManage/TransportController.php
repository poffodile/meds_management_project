<?php

namespace App\Http\Controllers\backEnd\systemManage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session, DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Transport;

class TransportController extends Controller
{
    public function index(Request $request)
    {
        $home_id = Session::get('scitsAdminSession')->home_id;
        if (empty($home_id)) {
            return redirect('admin/')->with('error', NO_HOME_ERR);
        }

        $transports = Transport::select('id', 'home_id', 'name', 'created_at', 'status')
            ->orderBy('id', 'desc');

        $search = '';

        if (isset($request->limit)) {
            $limit = $request->limit;
            Session::put('page_record_limit', $limit);
        } else {
            if (Session::has('page_record_limit')) {
                $limit = Session::get('page_record_limit');
            } else {
                $limit = 20;
            }
        }

        if (isset($request->search)) {
            $search = trim($request->search);
            $transports = $transports->where('name', 'like', '%' . $search . '%');
        }

        $transports = $transports->paginate($limit);
        $page = 'transport';
        return view('backEnd/systemManage/transport/index', compact('page', 'limit', 'transports', 'search'));
    }

    public function save(Request $request)
    {
        $rules = [
            'name' => 'required',
            'status' => 'required|boolean',
        ];

        if (!empty($request->id)) {
            $rules['id'] = 'required|exists:transports,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }

        try {
            DB::beginTransaction();
            $data = $request->all();
            $data['home_id'] = Session::get('scitsAdminSession')->home_id;
            Transport::updateOrCreate(['id' => $request->id ?? null], $data);
            DB::commit();

            Session::flash('success', 'Saved successfully.');
            return response()->json([
                'success' => true,
                'message' => "Saved successfully.",
                'data' => (object)[]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Transport: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error saving transport: ' . $e->getMessage(),
            ];
        }
    }

    public function delete($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:transports,id',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }

        try {
            DB::beginTransaction();
            $transport = Transport::find($id);
            $transport->delete();
            DB::commit();

            Session::flash('success', 'Deleted successfully.');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Transport delete: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error deleting transport: ' . $e->getMessage(),
            ];
        }
    }

    public function status_change(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:transports,id',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }

        try {
            DB::beginTransaction();
            $transport = Transport::find($request->id);
            $transport->status = $request->status;
            $transport->save();
            DB::commit();

            Session::flash('success', 'Status changed successfully.');
            return response()->json([
                'success' => true,
                'message' => "Status changed successfully.",
                'data' => (object)[]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Transport status change: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error changing transport status: ' . $e->getMessage(),
            ];
        }
    }
}
