<?php

namespace App\Http\Controllers\backEnd\generalAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompanyDepartment;

class DepartmentBackendController extends Controller
{
    public function index()
    {
        $data['page'] = "department";
        $data['departments'] = CompanyDepartment::where('deleted_at', null)->get();
        return view('backEnd.generalAdmin.department.department', $data);
    }

    public function create()
    {
        $data['page'] = "department";
        return view('backEnd.generalAdmin.department.department_form', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:company_departments,name,' . $request->id,
            'status' => 'boolean',
        ]);

        CompanyDepartment::updateOrCreate(
            ['id' => $request->id], // if exists, update; else create
            [
                'name' => $request->name,
                'status' => $request->status ?? true
            ]
        );

        return redirect()->route('generalAdmin.department.index')->with('success', $request->id ? 'Department updated successfully' : 'Department created successfully');
    }
    public function changeStatus(Request $request)
    {
        $data = CompanyDepartment::find($request->id);
        if ($data) {
            $data->status = !$data->status;
            $data->save();
            return response()->json(['success' => true, 'message' => 'Status changed successfully!']);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to change status']);
        }
    }

    public function destroy($id)
    {
        try {
            $expense = CompanyDepartment::findOrFail($id); // Ensure the record exists
            $expense->delete(); // Delete the record
            return redirect()->back()->with('success', 'Department deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete department type.');
        }
    }
    public function edit($id)
    {

        $data['page'] = "department";
        $data['department'] = CompanyDepartment::findOrFail($id);
        return view('backEnd.generalAdmin.department.department_form', $data);
    }
}
