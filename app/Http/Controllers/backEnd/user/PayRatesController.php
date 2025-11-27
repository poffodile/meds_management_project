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
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'access_level_id' => 'required',
            'pay_rate'        => 'required|numeric',
            'rate_type_id'    => 'required',
        ]);

        try {
            $data = PayRate::updateOrCreate(
                ['id' => $request->id],
                [
                    'home_id'         => $this->home_id,
                    'access_level_id' => $request->access_level_id,
                    'pay_rate'        => $request->pay_rate,
                    'rate_type_id'    => $request->rate_type_id,
                    'status'          => 1,
                    'is_deleted'      => 0,
                ]
            );

            // return redirect()->back()->with('success', 'Pay Rate saved successfully');
            $data['page'] = 'pay_rates';
            $data['payRates'] = PayRate::getAllPayRates($this->home_id);
            return view('backEnd.user.pay_rates.pay_rates', $data);
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
        $payrate = PayRate::findOrFail($id);
        return view('backEnd.user.pay_rates.pay_rates_form', compact('payrate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
