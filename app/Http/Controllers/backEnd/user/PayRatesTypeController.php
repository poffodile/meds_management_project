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

            // Log the error (recommended)
            \Log::error('PayRateType Store Error: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $data['page'] = 'pay_rate_types';
        $data['rateType'] = PayRateType::findOrFail($id);

        return view('backEnd.user.pay_rates.pay_rate_type_form', $data);
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'type_name' => 'required|max:255'
            ]);

            $type = PayRateType::findOrFail($id);

            $type->update([
                'type_name' => $request->type_name,
            ]);

            return redirect()->route('payrates.types.index')
                ->with('success', 'Pay rate type updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function destroy($id)
    {
        $type = PayRateType::findOrFail($id);

        $type->update([
            'is_deleted' => 1,
            'status'     => 0
        ]);

        return redirect()->route('payrates.types.index')
            ->with('success', 'Pay rate type deleted successfully.');
    }
}
