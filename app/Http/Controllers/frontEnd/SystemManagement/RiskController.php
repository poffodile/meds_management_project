<?php

namespace App\Http\Controllers\frontEnd\SystemManagement;
use App\Http\Controllers\frontEnd\SystemManagementController;
use Illuminate\Http\Request;
use App\Risk;
use DB, Auth;

class RiskController extends SystemManagementController
{
    public function index() {

        $home_id = Auth::user()->home_id;
        $risks = DB::table('risk')->where('home_id',$home_id)->where('is_deleted','0')->where('status','1')->orderBy('id','desc')->get();

        foreach ($risks as $key => $value) {
            
            if($value->status ==1)  {
                $risk_set_btn_class = "clr-blue";
            }
            else{
                $risk_set_btn_class = "clr-grey";
            }
        
            echo'<div class="col-md-12 col-sm-12 col-xs-12 cog-panel risk-row">

                    <div class="form-group col-md-7 col-sm-7 col-xs-12 p-0">
                        <div class="input-group popovr">
                            <span class="input-group-addon cus-inpt-grp-addon"> <label> <input type="checkbox" value="'.$value->id.'"/> </label> </span>
                            <input type="text" name="edit_risk_desc[]" class="form-control edit_risk_desc_'.$value->id.' edit_risk" disabled placeholder="" value="'.$value->description.'"/>
                            <input type="hidden" name="edit_risk_id[]" value="'.$value->id.'" disabled="disabled" class="edit_risk_id_'.$value->id.'" />
                            <input type="hidden" name="edit_risk_icon[]" value="'.$value->icon.'" disabled="disabled" class="edit_risk_icon_'.$value->id.' " />

                            <span class="input-group-addon cus-inpt-grp-addon '.$risk_set_btn_class.' risk-color icon-box-edit-risk " risk_id="'.$value->id.'"> <i class="'.$value->icon.' "></i> </span>

                            <span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
                                <i class="fa fa-cog"></i>
                                <div class="pop-notifbox">
                                    <ul class="pop-notification" type="none">
                                        <li> <a href="#" risk_id="'.$value->id.'" class="edit_risk_btn"> <span> <i class="fa fa-pencil"></i> </span> Edit </a> </li>
                                        <li> <a href="delete" risk_id="'.$value->id.'" class="delete_risk_btn" > <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>
                                        <li> <a href="#" risk_id="'.$value->id.'" class="risk_status_change_btn"> <span class="color-yellow"> <i class="fa fa-bolt"></i> </span> Active/Inactive </a> </li>
                                    </ul>
                                </div>
                            </span>
                        </div>
                    </div>
                </div>';
        }
        die;
    }

    public function add(Request $request){
        
        if($request->isMethod('post')){

            $data = $request->all();
            // $home_id = Auth::user()->home_id;
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
           
            $risk  = new Risk;
            $risk->description = $data['risk_description'];
            $risk->status      = 1;
            $risk->icon        = $data['icon_list'];
            $risk->home_id     = $home_id;
            
            if($risk->save()){

                //return redirect()->back();
                $rep = $this->index();
                echo $rep;
            }
            else {
                echo '0';
            }
            die;
        }
    }

    public function delete($risk_id){
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $risk = Risk::where('id', $risk_id)->where('home_id',$home_id)->update(['is_deleted'=>'1']);
        echo $risk;       
    }

    public function update_status($risk_id = null){
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $risks = Risk::where('id', $risk_id)->where('home_id',$home_id)->first();

        if($risks->status == '0'){
            $new_status = 1;
        }
        else{
            $new_status = 0;
        }

        $risks->status = $new_status;
        if($risks->save()){
          echo '1';
        }
        else{
          echo '0';
        }
        die;
    }

    public function edit(Request $request){

        $data = $request->all();
      
        $risk_ids = $data['edit_risk_id'];
        
        if(!empty($risk_ids))   {
            foreach ($risk_ids as $key => $risk_id) {
              
                $risk = Risk::where('id',$risk_id)->where('home_id',Auth::user()->home_id)->first();
                if(!empty($risk)){
                    $risk->description  = $data['edit_risk_desc'][$key];
                    $risk->icon         = $data['edit_risk_icon'][$key];
                    $risk->save();
                }
            }
        }
        $res = $this->index();
        echo $res;  die;
    }

    public function risk_delete(Request $request){
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $risk = Risk::whereIn('id', $request->risk_id)->where('home_id',$home_id)->update(['is_deleted'=>'1']);
        if($risk){
            echo '1';
        }else{
            echo "2";
        }
    }
}