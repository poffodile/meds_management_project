<?php

namespace App\Http\Controllers\backEnd\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AccessLevel, App\Models\HomeManagement\PayRate;
use Illuminate\Support\Facades\Session;
use App\Models\HomeManagement\PayRateType;

class PayRatesController extends Controller
{
    /**
     * Display a listing of the resource.
     */

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
        $data['page'] = 'pay_rates';
        $data['payRates'] = PayRate::getAllPayRates($this->home_id);
        return view('backEnd.user.pay_rates.pay_rates', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['page'] = 'pay_rates';
        $data['accesslevel'] = AccessLevel::getAccessLevelList();
        $data['rateType'] = PayRateType::getActiveTypes($this->home_id);
        return view('backEnd.user.pay_rates.pay_rates_form', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $id = null)
    {
        $request->validate([
            'access_level_id' => 'required',
            'pay_rate'        => 'required|numeric',
            'rate_type_id'    => 'required',
        ]);

        // Check if already exists for same home, access level, and rate type
        $exists = PayRate::where('home_id', $this->home_id)
            ->where('access_level_id', $request->access_level_id)
            ->where('rate_type_id', $request->rate_type_id)
            ->where('id', '!=', $request->id)  // allow editing same record
            ->where('is_deleted', 0)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Pay Rate already exists for this Access Level and Rate Type.');
        }

        try {

            $data = PayRate::updateOrCreate(
                ['id' => $id],
                [
                    'home_id'         => $this->home_id,
                    'access_level_id' => $request->access_level_id,
                    'pay_rate'        => $request->pay_rate,
                    'rate_type_id'    => $request->rate_type_id,
                    'status'          => 1,
                    'is_deleted'      => 0,
                ]
            );

            $data['page'] = 'pay_rates';
             return redirect()
                ->route('payrates.index')
                ->with('success', 'Pay rate added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data['page'] = 'pay_rates';
        $data['payrate'] = PayRate::findOrFail($id);
        $data['accesslevel'] = AccessLevel::getAccessLevelList();
        $data['rateType'] = PayRateType::getActiveTypes($this->home_id);
        return view('backEnd.user.pay_rates.pay_rates_form', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'access_level_id' => 'required',
                'pay_rate'        => 'required|numeric',
                'rate_type_id'    => 'required',
            ]);

            $type = PayRate::findOrFail($id);

            $type->update([
                'access_level_id' => $request->access_level_id,
                'pay_rate'        => $request->pay_rate,
                'rate_type_id'    => $request->rate_type_id,
                'status'          => $request->status,
            ]);
            $data['page'] = 'pay_rates';
            return redirect()->route('payrates.index')
                ->with('success', 'Pay rate updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $type = PayRate::findOrFail($id);

        $type->update([
            'is_deleted' => 1,
            'status'     => 0
        ]);
        $data['page'] = 'pay_rates';
        return redirect()->route('payrates.index')
            ->with('success', 'Pay rate deleted successfully.');
    }
}
