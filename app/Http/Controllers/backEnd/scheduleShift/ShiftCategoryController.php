<?php

namespace App\Http\Controllers\backEnd\scheduleShift;

use App\Models\ShiftCategory;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShiftCategoryRequest;
use App\Http\Requests\UpdateShiftCategoryRequest;

class ShiftCategoryController extends Controller
{
    protected $home_id;

    public function __construct()
    {
        // Store home_id globally for this controller
        $this->middleware(function ($request, $next) {
            $this->home_id = Session::get('scitsAdminSession')->home_id;
            return $next($request);
        });
    }

    public function index()
    {
        $data['page'] = 'shift_category';
        $data['categories'] = ShiftCategory::getAllCategories($this->home_id);
        return view('backEnd.user.shift_category.shift_category_list', $data);
    }

    public function create()
    {
        $data['page'] = 'shift_category';
        return view('backEnd.user.shift_category.shift_category_form', $data);
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $data['page'] = 'shift_category';
        $request->validate([
            'name' => 'required|max:255',
            'color' => 'nullable|max:10'
        ]);

        try {
            ShiftCategory::create([
                'home_id' => $this->home_id,
                'name'    => $request->name,
                'color'   => $request->color,
                'status'  => 1,
                'is_deleted' => 0
            ]);

            return redirect()
                ->route('shiftcategory.index')
                ->with('success', 'Shift Category added successfully.');
        } catch (\Exception $e) {
            Log::error('ShiftCategory Store Error: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $data['page'] = 'shift_category';
        $data['category'] = ShiftCategory::findOrFail($id);

        return view('backEnd.user.shift_category.shift_category_form', $data);
    }

    public function update(\Illuminate\Http\Request $request, $id)
    {
        $data['page'] = 'shift_category';
        try {
            $request->validate([
                'name' => 'required|max:255',
                'color' => 'nullable|max:10'
            ]);

            $category = ShiftCategory::findOrFail($id);

            $category->update([
                'name'   => $request->name,
                'color'  => $request->color,
                'status' => $request->status,
            ]);

            return redirect()->route('shiftcategory.index')
                ->with('success', 'Shift Category updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function destroy($id)
    {
        $data['page'] = 'shift_category';
        $category = ShiftCategory::findOrFail($id);

        $category->update([
            'is_deleted' => 1,
            'status'     => 0
        ]);

        return redirect()->route('shiftcategory.index')
            ->with('success', 'Shift Category deleted successfully.');
    }
}
