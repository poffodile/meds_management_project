<?php

namespace App\Http\Controllers\backEnd\homeManage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\LeaveType;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $data['page'] = "leaves";
        $data['leave_types'] = LeaveType::orderBy('id', 'DESC')->get();
        return view('backEnd.homeManage.leaves.leave_type', $data);
    }

    public function create()
    {
        $data['page'] = "leaves";
        return view('backEnd.homeManage.leaves.leave_type_form', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'leave_name' => 'required|unique:leave_type,leave_name',
            // 'max_days' => 'required|integer|min:0',
        ]);

        LeaveType::create($request->all());

        return redirect()->route('admin.leaveType.index')
            ->with('success', 'Leave Type created successfully.');
    }

    // Edit form
    public function edit($id)
    {
        $data['page'] = "leaves";
        $data['leaveType'] = LeaveType::findOrFail($id);
        return view('backEnd.homeManage.leaves.leave_type_form', $data);
    }

    // Update leave type
    public function update(Request $request, $id)
    {
        $leaveType = LeaveType::findOrFail($id);

        $request->validate([
            'leave_name' => 'required|unique:leave_type,leave_name,' . $id,
            // 'max_days' => 'required|integer|min:0',
        ]);

        $leaveType->update($request->all());

        return redirect()->route('admin.leaveType.index')
            ->with('success', 'Leave Type updated successfully.');
    }

    // Delete leave type
    public function destroy($id)
    {
        LeaveType::findOrFail($id)->delete();

        return redirect()->route('admin.leaveType.index')
            ->with('success', 'Leave Type deleted successfully.');
    }
}
