<?php

namespace App\Http\Controllers\frontEnd\Roster\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceUserManagement\ServiceUserExpense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClientExpenseController extends Controller
{
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_user_id' => 'required|exists:service_user,id',
            'expense_date' => 'required|date',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            $home_id = explode(',', Auth::user()->home_id)[0];
            $data = $request->only(['service_user_id', 'expense_date', 'title', 'amount', 'notes']);
            $data['home_id'] = $home_id;

            if ($request->id) {
                $expense = ServiceUserExpense::updateOrCreate(['id' => $request->id], $data);
                $message = "Expense updated successfully";
            } else {
                $expense = ServiceUserExpense::create($data);
                $message = "Expense added successfully";
            }

            return response()->json(['success' => true, 'message' => $message, 'data' => $expense]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Something went wrong: " . $e->getMessage()]);
        }
    }

    public function list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_user_id' => 'required|exists:service_user,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $home_id = explode(',', Auth::user()->home_id)[0];
            $expenses = ServiceUserExpense::where('service_user_id', $request->service_user_id)
                ->where('home_id', $home_id)
                ->orderBy('expense_date', 'desc')
                ->get();

            return response()->json(['success' => true, 'data' => $expenses]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Something went wrong: " . $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:service_user_expenses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            ServiceUserExpense::destroy($request->id);
            return response()->json(['success' => true, 'message' => "Expense deleted successfully"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Something went wrong: " . $e->getMessage()]);
        }
    }
}
