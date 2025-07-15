<?php

namespace App\Http\Controllers\frontEnd\salesFinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB,Auth,Log;
use Carbon\Carbon;
use App\Models\PreInvoiceVat;
use App\Models\PreInvoice;
use App\Models\PreSubsInvoice;
use App\Models\PreInvoiceAdditionalHour;
use App\Models\PreInvoiceExtrasWeekly;
use App\Models\PreInvoiceExtrasOneOff;

class PreInvoiceController extends Controller
{
    public function index(Request $request, $service_user_id){
        $data['service_user_id']=$service_user_id;
        $data['child'] = DB::table('service_user')->where('id', $service_user_id)->where('is_deleted', '0')->first();
        $data['PreInvoiceVat']=PreInvoiceVat::whereNull('deleted_at')->orderBy('id','desc')->first();
        $data['PreInvoiceList']=PreInvoice::where('child_id',$service_user_id)->whereNull('deleted_at')->get();
        // echo "<pre>";print_r($data['child']);die;
        return view('frontEnd.salesAndFinance.pre_invoice.pre_invoice',$data);
    }
    public function preinvoice_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $loggedUserId=Auth::user()->id;
        $home_id=Auth::user()->home_id;
        $child_id=$request->child_id;
        $validator = Validator::make($request->all(), [
            'currentRateStart_date' => 'required|array',
            'currentRateStart_date.*' => 'required|string',
            'currentRateEnd_date' => 'required|array',
            'currentRateEnd_date.*' => 'required|string',
            'currentRateNo_of_days' => 'required|array',
            'currentRateNo_of_days.*' => 'required|string',
            'currentRateWeekly_rate' => 'required|array',
            'currentRateWeekly_rate.*' => 'required|string',
            'currentRateTotalCost' => 'required|array',
            'currentRateTotalCost.*' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        if(isset($request->currentRateStart_date) && count($request->currentRateStart_date) > 0){
            for($i=0;$i<count($request->currentRateStart_date);$i++){
                $data1=[
                    'id'=>$request->current_week_id[$i] ?? null,
                    'loggedUserId'  =>  $loggedUserId,
                    'home_id'       =>  $home_id,
                    'child_id'      =>  $child_id,
                    'start_date'    =>  Carbon::createFromFormat('d-m-Y', $request->currentRateStart_date[$i])->format('Y-m-d'),
                    'end_date'      =>  Carbon::createFromFormat('d-m-Y', $request->currentRateEnd_date[$i])->format('Y-m-d'),
                    'no_of_days'    =>  $request->currentRateNo_of_days[$i],
                    'current_rate'  =>  $request->currentRateWeekly_rate[$i],
                    'total_cost'    =>  $request->currentRateTotalCost[$i],
                    'vat'           =>  $request->vat,
                ];
                try {
                    $PreInvoice1= PreInvoice::savePreInvoice($data1);
                    $PreInvoice2=$this->saveSubs($request->all(),$loggedUserId,$home_id,$child_id,$PreInvoice1->id);
                    $PreInvoice2responseData = $PreInvoice2->getData(true);
                    if (empty($PreInvoice2responseData['success']) || $PreInvoice2responseData['success'] === false) {
                        return response()->json(['success' => false,'message' => 'Something went wrong.','data' => $PreInvoice2responseData,]);
                    }
                    $PreInvoice3=$this->saveAdditionalHour($request->all(),$loggedUserId,$home_id,$child_id,$PreInvoice1->id);
                    $PreInvoice3responseData = $PreInvoice3->getData(true);
                    if (empty($PreInvoice3responseData['success']) || $PreInvoice3responseData['success'] === false) {
                        return response()->json(['success' => false,'message' => 'Something went wrong.','data' => $PreInvoice3responseData,]);
                    }
                    $PreInvoice4=$this->saveExtrasWeekly($request->all(),$loggedUserId,$home_id,$child_id,$PreInvoice1->id);
                    $PreInvoice4responseData = $PreInvoice4->getData(true);
                    if (empty($PreInvoice4responseData['success']) || $PreInvoice4responseData['success'] === false) {
                        return response()->json(['success' => false,'message' => 'Something went wrong.','data' => $PreInvoice4responseData,]);
                    }
                    $PreInvoice5=$this->saveExtrasOneOff($request->all(),$loggedUserId,$home_id,$child_id,1);
                    $PreInvoice5responseData = $PreInvoice5->getData(true);
                    if (empty($PreInvoice5responseData['success']) || $PreInvoice5responseData['success'] === false) {
                        return response()->json(['success' => false,'message' => 'Something went wrong.','data' => $PreInvoice5responseData,]);
                    }
                } catch (\Exception $e) {
                    return response()->json(['error' => $e->getMessage()], 500);
                }
            }
        }
        // return count($request->current_week_id);
        if (!empty(array_filter($request->current_week_id))) {
            return response()->json(['success' => true, 'message' => 'Pre-Invoice Updated Successfully']);
        } else {
            return response()->json(['success' => true, 'message' => 'Pre-Invoice Added Successfully']);
        }
    }
    public function saveSubs($data,$loggedUserId,$home_id,$child_id,$current_id){
        // echo "<pre>";print_r($data);die;
        if(isset($data['subsEnd_date']) && count($data['subsEnd_date']) > 0){
            $success=0;
            for($i2=0;$i2<count($data['subsEnd_date']);$i2++){
                $data2=[
                    'id'=>$data['subs_week_id'][$i2] ?? null,
                    'loggedUserId'  =>  $loggedUserId,
                    'home_id'       =>  $home_id,
                    'child_id'      =>  $child_id,
                    'current_id'    =>  $current_id,
                    'subs_start_date'    =>  Carbon::createFromFormat('d-m-Y', $data['subsStart_date'][$i2])->format('Y-m-d'),
                    'subs_end_date'      =>  Carbon::createFromFormat('d-m-Y', $data['subsEnd_date'][$i2])->format('Y-m-d'),
                    'subs_no_of_days'    =>  $data['subsNo_of_days'][$i2],
                    'subs_rate'          =>  $data['subsWeeklyRate'][$i2],
                    'subs_total_cost'    =>  $data['subsTotalCost'][$i2],
                ];
                // echo "<pre>";print_r($data2);die;
                try {
                    $PreInvoice2= PreSubsInvoice::savePreSubsInvoice($data2);
                    if ($PreInvoice2) {
                        $success++;
                    }
                } catch (\Exception $e) {
                    Log::error('Error saving PreInvoice Subs: ' . $e->getMessage());
                    return response()->json(['error' => $e->getMessage()], 500);
                }
            }
            if ($success === count($data['subsEnd_date'])) {
                return response()->json(['success' => true, 'message' => 'Pre-Invoice Subs saved successfully.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Some Pre-Invoice Subs could not be saved.']);
            }
        }
    }
    public function saveAdditionalHour($data,$loggedUserId,$home_id,$child_id,$current_id){
        if(isset($data['additionalHours_End_date']) && count($data['additionalHours_End_date']) > 0){
            $success=0;
            for($i3=0;$i3<count($data['additionalHours_End_date']);$i3++){
                $data3=[
                    'id'=>$data['additionalHours_id'][$i3] ?? null,
                    'loggedUserId'          =>  $loggedUserId,
                    'home_id'               =>  $home_id,
                    'child_id'              =>  $child_id,
                    'current_id'            =>  $current_id,
                    'addHour_start_date'    =>  Carbon::createFromFormat('d-m-Y', $data['additionalHours_Start_date'][$i3])->format('Y-m-d'),
                    'addHour_end_date'      =>  Carbon::createFromFormat('d-m-Y', $data['additionalHours_End_date'][$i3])->format('Y-m-d'),
                    'addHour_no_of_days'    =>  $data['additionalHours_No_of_days'][$i3],
                    'addHour_rate'          =>  $data['additionalHours_Hourly_rate'][$i3],
                    'addHour_total_cost'    =>  $data['additionalHours_TotalCost'][$i3],
                    'additional_per_week'   =>  $data['additionalHours_HoursPerWeek'][$i3],
                    'addHour_vat'   =>  $data['vat'],
                ];
                // echo "<pre>";print_r($data3);die;
                try {
                    $PreInvoice3= PreInvoiceAdditionalHour::savePreInvoiceAdditionalHour($data3);
                    if ($PreInvoice3) {
                        $success++;
                    }
                } catch (\Exception $e) {
                    Log::error('Error saving PreInvoice Additional hours: ' . $e->getMessage());
                    return response()->json(['error' => $e->getMessage()], 500);
                }
            }
            if ($success === count($data['additionalHours_End_date'])) {
                return response()->json(['success' => true, 'message' => 'Pre-Invoice Additional hours saved successfully.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Some Pre-Invoice Additional hours could not be saved.']);
            }
        }
    }
    public function saveExtrasWeekly($data,$loggedUserId,$home_id,$child_id,$current_id){
        // echo "<pre>";print_r($data);die;
        if(isset($data['additionalExtrasWeekly_End_date']) && count($data['additionalExtrasWeekly_End_date']) > 0){
            $success=0;
            for($i4=0;$i4<count($data['additionalExtrasWeekly_End_date']);$i4++){
                $data4=[
                    'id'=>$data['extras_weekly_id'][$i4] ?? null,
                    'loggedUserId'      =>  $loggedUserId,
                    'home_id'           =>  $home_id,
                    'child_id'          =>  $child_id,
                    'current_id'        => $current_id,
                    'extras_weekly_start_date'        =>  Carbon::createFromFormat('d-m-Y', $data['additionalExtrasWeekly_Start_date'][$i4])->format('Y-m-d'),
                    'extras_weekly_end_date'          =>  Carbon::createFromFormat('d-m-Y', $data['additionalExtrasWeekly_End_date'][$i4])->format('Y-m-d'),
                    'extras_weekly_no_of_days'        =>  $data['additionalExtrasWeekly_No_of_Days'][$i4],
                    'extras_weekly_amount'            =>  $data['additionalExtrasWeekly_Weekly_amount'][$i4],
                    'extras_weekly_total_cost'        =>  $data['additionalExtrasWeekly_Total_Cost'][$i4],
                    'extras_weekly_expenditure_type'  =>  $data['additionalExtrasWeekly_ExpenditureType'][$i4],
                ];
                // echo "<pre>";print_r($data4);die;
                try {
                    $PreInvoice4= PreInvoiceExtrasWeekly::savePreInvoiceExtrasWeekly($data4);
                    if ($PreInvoice4) {
                        $success++;
                    }
                } catch (\Exception $e) {
                    Log::error('Error saving PreInvoice extras weekly: ' . $e->getMessage());
                    return response()->json(['error' => $e->getMessage()], 500);
                }
            }
            if ($success === count($data['additionalExtrasWeekly_End_date'])) {
                return response()->json(['success' => true, 'message' => 'Pre-Invoice Extras weekly saved successfully.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Some Pre-Invoice Extras weekly could not be saved.']);
            }
        }
    }
    public function saveExtrasOneOff($data,$loggedUserId,$home_id,$child_id,$current_id){
        if(isset($data['additionalExtrasOneOff_Start_date']) && count($data['additionalExtrasOneOff_Start_date']) > 0){
            $success=0;
            for($i5=0;$i5<count($data['additionalExtrasOneOff_Start_date']);$i5++){
                $data5=[
                    'id'=>$data['oneoff_id'][$i5] ?? null,
                    'loggedUserId'      =>  $loggedUserId,
                    'home_id'           =>  $home_id,
                    'child_id'          =>  $child_id,
                    'current_id'        =>  $current_id,
                    'extras_oneoff_start_date'          =>  Carbon::createFromFormat('d-m-Y', $data['additionalExtrasOneOff_Start_date'][$i5])->format('Y-m-d'),
                    'extras_oneoff_amount'              =>  $data['additionalExtrasOneOff_Amount'][$i5],
                    'extras_oneoff_total_cost'          =>  $data['additionalExtrasOneOff_Total_cost'][$i5],
                    'extras_oneoff_expenditure_type'    =>  $data['additionalExtrasOneOff_Expediture_type'][$i5],
                ];
                // echo "<pre>";print_r($data5);die;
                try {
                    $PreInvoice5= PreInvoiceExtrasOneOff::savePreInvoiceExtrasOneOff($data5);
                    if ($PreInvoice5) {
                        $success++;
                    }
                } catch (\Exception $e) {
                    Log::error('Error saving PreInvoice extras one off: ' . $e->getMessage());
                    return response()->json(['error' => $e->getMessage()], 500);
                }
            }
            if ($success === count($data['additionalExtrasOneOff_Start_date'])) {
                return response()->json(['success' => true, 'message' => 'Pre-Invoice Extras one off saved successfully.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Some Pre-Invoice Extras one off could not be saved.']);
            }
        }
    }
    public function preview(){
        echo 12;
    }
    public function edit_PreInvoice(Request $request){
        // return response()->json(['success'=>true,'data'=>$request->all()]);
        $child_id=$request->child_id;
        $all_data=PreInvoice::with(['preInvoiceSubs','preInvoiceAdditionalHours','preInvoiceExtrasWeeklies','preInvoiceExtrasOneOffs'])->whereNull('deleted_at')->get();
        return response()->json(['success'=>true,'data'=>$all_data]);
    }
}
