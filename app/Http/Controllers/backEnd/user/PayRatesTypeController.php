<?php

namespace App\Http\Controllers\backEnd\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HomeManagement\PayRateType;
use Illuminate\Support\Facades\Session;

class PayRatesTypeController extends Controller
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
        $data['page'] = 'pay_rates_type';
        $data['rateTypes'] = PayRateType::getAllTypes($this->home_id);
        return view('backEnd.user.pay_rates.pay_rates_type', $data);
    }

    public function create()
    {
        $data['page'] = 'pay_rates_type';
        return view('backEnd.user.pay_rates.pay_rate_type_form', $data);
    }

    public function store(Request $request)
    {
        $data['page'] = 'pay_rates_type';
        $request->validate([
            'type_name' => 'required|max:255'
        ]);

        try {
            $exists = PayRateType::where('type_name', $request->type_name)
                ->where(function ($query) {
                    $query->where('home_id', $this->home_id)
                        ->orWhere('home_id', 0);
                })
                ->where('is_deleted', 0)
                ->exists();

            if ($exists) {
                return redirect()->back()->withInput()->with('error', 'Pay rate type with this name already exists.');
            }

            PayRateType::create([
                'home_id'   => $this->home_id,
                'type_name' => $request->type_name,
                'status'    => 1,
                'is_deleted' => 0
            ]);

            return redirect()
                ->route('payrates.types.index')
                ->with('success', 'Pay rate type added successfully.');
        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $data['page'] = 'pay_rates_type';
        $data['rateType'] = PayRateType::findOrFail($id);

        if ($data['rateType']->home_id == 0) {
            return redirect()->back()->with('error', 'You are not allowed to edit this record.');
        }

        return view('backEnd.user.pay_rates.pay_rate_type_form', $data);
    }

    public function update(Request $request, $id)
    {
        $data['page'] = 'pay_rates_type';
        try {
            $request->validate([
                'type_name' => 'required|max:255'
            ]);

            $type = PayRateType::findOrFail($id);

            if ($type->home_id == 0) {
                return redirect()->back()->with('error', 'You are not allowed to update this record.');
            }

            $exists = PayRateType::where('type_name', $request->type_name)
                ->where(function ($query) {
                    $query->where('home_id', $this->home_id)
                        ->orWhere('home_id', 0);
                })
                ->where('is_deleted', 0)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return redirect()->back()->withInput()->with('error', 'Pay rate type with this name already exists.');
            }

            $type->update([
                'type_name' => $request->type_name,
                'status'    => $request->status,
            ]);

            return redirect()->route('payrates.types.index')
                ->with('success', 'Pay rate type updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function destroy($id)
    {
        $data['page'] = 'pay_rates_type';
        $type = PayRateType::findOrFail($id);

        if ($type->home_id == 0) {
            return redirect()->back()->with('error', 'You are not allowed to delete this record.');
        }

        $type->update([
            'is_deleted' => 1,
            'status'     => 0
        ]);

        return redirect()->route('payrates.types.index')
            ->with('success', 'Pay rate type deleted successfully.');
    }
}
