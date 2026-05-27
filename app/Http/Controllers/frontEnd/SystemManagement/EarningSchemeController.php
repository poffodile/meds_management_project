<?php

namespace App\Http\Controllers\frontEnd\SystemManagement;
use App\Http\Controllers\frontEnd\SystemManagementController;
use Illuminate\Http\Request;
use App\EarningScheme, App\Incentive;
use DB, Auth;

class EarningSchemeController extends SystemManagementController
{
    public function index()
    {

        $home_id = Auth::user()->home_id;
        $earns = EarningScheme::with('incentives')->where('home_id',$home_id)->where('is_deleted','0')->where('status','1')->orderBy('id','desc')->get();
        $data = array();
    
        if(!empty($earns))  {

            $data['earning_cat_options'] = '';
            $data['list'] = '';

            foreach ($earns as $key => $value) {

                if($value->status == 1)  {
                    $earn_set_btn_class = "clr-blue";
                }
                else  {
                    $earn_set_btn_class = "clr-grey";
                }

                $data['earning_cat_options'] .= '<option value="'.$value->id.'">'.$value->title.'</option>';
                $data['list'] .= '
                <div class="earn-incentive-scheme" >
                    <div class="form-group col-md-12 col-sm-12 col-xs-12 cog-panel earn-row">
                        <div class="col-md-7 col-sm-7 col-xs-12 p-0">
                            <div class="input-group popovr">
                                    
                                <input type="text" name="edit_earn_title[]" class="form-control edit_earn_title_'.$value->id.' edit_earn" disabled="disabled" placeholder="" value="'.$value->title.'"/>
                                <input type="hidden" name="edit_earn_id[]" value="'.$value->id.'" disabled="disabled" class="edit_earn_id_'.$value->id.'" />

                                <input type="hidden" name="edit_earn_icon[]" value="'.$value->icon.'" disabled="disabled" class="edit_earn_icon_'.$value->id.' " />

                                <span class="input-group-addon cus-inpt-grp-addon '.$earn_set_btn_class.' earn-color icon-box-edit-earn" earn_id="'.$value->id.'"> <i class="'.$value->icon.' "></i> </span>
                                <span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
                                    <i class="fa fa-cog"></i>       
                                    <div class="pop-notifbox">
                                        <ul class="pop-notification" type="none">
                                            <li> <a href="#" earn_id="'.$value->id.'" class="edit_earn_btn"> <span> <i class="fa fa-pencil"></i> </span> Edit </a> </li>
                                            <li> <a href="delete" earn_id="'.$value->id.'" class="delete_earn_btn" > <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>
                                            <li> <a href="#" earn_id="'.$value->id.'" class="earn_status_change_btn"> <span class="color-yellow"> <i class="fa fa-bolt"></i> </span> Active/Inactive </a> </li>

                                        </ul>
                                    </div>
                                </span>
                            </div>
                        </div>
                    </div>';

                    foreach($value->incentives as $incentive){

                        if($incentive->status == 1){
                            $incentive_set_btn_class = "clr-blue";                  
                        }
                        else{
                            $incentive_set_btn_class = "clr-grey";   
                        }

                    $data['list'] .= '
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 cog-panel incentive-row">
                            <div class="col-md-7 col-sm-7 col-xs-12 p-0">
                                <div class="input-group popovr">
                                    <input type="text" value="'.$incentive->name.'" name="edit_incentive_name[]" class="form-control edit_incentive_name_'.$incentive->id.' edit_incentive" disabled="disabled"/>
                                    <input type="hidden" name="edit_incentive_id[]" value="'.$incentive->id.'" disabled="disabled" class="edit_incentive_id_'.$incentive->id.'" />
                                    <span class="input-group-addon cus-inpt-grp-addon '.$incentive_set_btn_class.' settings ">
                                        <i class="fa fa-cog"></i>
                                        <div class="pop-notifbox">
                                            <ul class="pop-notification" type="none">
                                                <li> <a href="#" incentive_id="'.$incentive->id.'" class="view_incentive_btn"> <span> <i class="fa fa-eye"></i></span> View </a> </li>
                                                <li> <a href="#" incentive_id="'.$incentive->id.'" class="edit-incentive-btn"> <span> <i class="fa fa-pencil"></i> </span> Edit </a> </li>
                                                <li> <a href="#" incentive_id="'.$incentive->id.'" class="delete_incentive_btn"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>
                                                <li> <a href="#" incentive_id="'.$incentive->id.'" class="status_incentive_btn"> <span class="color-yellow"> <i class="fa fa-bolt"></i> </span> Active/Inactive </a> </li>
                                            </ul>
                                        </div>
                                    </span>
                                </div>
                            </div>
                        </div>';
                    }
                $data['list'] .= '</div>';
            }
        }
        $result = json_encode($data);
        return $result;
        die;
    }

    public function add(Request $request){
    
        if($request->isMethod('post')){

            $data = $request->all();
            // $home_id = Auth::user()->home_id;
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];

            $earning  = new EarningScheme;
            $earning->title = $data['title'];
            $earning->status = 1;
            $earning->icon = $data['earning_icon'];
            $earning->home_id = $home_id;
            
            if($earning->save()){

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

    public function delete($earn_id){
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        Incentive::where('earning_category_id',$earn_id)->update(['is_deleted'=>'1']);
        $earn_deleted = EarningScheme::where('id',$earn_id)->where('home_id',$home_id)->update(['is_deleted'=>'1']);
        
        $data['response'] = $earn_deleted;

        $earns = EarningScheme::with('incentives')->where('home_id', $home_id)->where('is_deleted','0')->orderBy('id','desc')->get();
        $data['earning_cat_options'] = '';
      
        if(!empty($earns)){
            foreach ($earns as $key => $value) {
                $data['earning_cat_options'] .= '<option value="'.$value->id.'">'.$value->title.'</option>';
            }           
        }
        return $data;
    }

    public function update_status($earn_id = null){

        $earning = EarningScheme::where('id', $earn_id)->where('home_id',Auth::user()->home_id)->first(); 
        if(!empty($earning)){
            if($earning->status == '0'){
                $new_status = 1;
            }
            else{
                $new_status = 0;
            }

            $earning->status = $new_status;
            if($earning->save()){
                echo '1';
            }
            else{
                echo '0';
            }
            die;
        }
    }

    public function edit(Request $request){

        $data = $request->all();
        
        if(isset($data['edit_earn_id'])){
            $earn_ids = $data['edit_earn_id'];
            if(!empty($earn_ids)){
                foreach ($earn_ids as $key => $earn_id) {
                    $earn_scheme = EarningScheme::where('id',$earn_id)->where('home_id',Auth::user()->home_id)->first();
                    
                    if(!empty($earn_scheme)){
                        $earn_scheme->title = $data['edit_earn_title'][$key];
                        $earn_scheme->icon  = $data['edit_earn_icon'][$key];
                        $earn_scheme->save();
                    }
                }
            }
        }

        if(isset($data['edit_incentive_id'])){
            $incentive_ids = $data['edit_incentive_id'];
            if(!empty($incentive_ids)){
                foreach ($incentive_ids as $key => $incentive_id) {
                    $incentive_id = Incentive::find($incentive_id);
                    $incentive_id->name = $data['edit_incentive_name'][$key];
                    $incentive_id->save();            
                }
            }
        }

        $res = $this->index();
        echo $res;  die;
    }

    public function add_incentive(Request $request){
       
        if($request->isMethod('post')){
        
            $data = $request->all();
            $earn_scheme = EarningScheme::where('id',$data['earning_category'])->where('home_id',Auth::user()->home_id)->first();
            if(!empty($earn_scheme)){

                $earning                        = new Incentive;
                $earning->name                  = $data['incentive_name'];
                $earning->earning_category_id   = $data['earning_category'];
                $earning->status                = 1;
                
                if($earning->save()){

                    $rep = $this->index();
                    echo $rep;
                }
                else {
                    echo '0';
                }
            }
            else {
                echo '0';
            }
            die;
        }
    }
    
    public function delete_incentive($incentive_id = null){

        $incentive = Incentive::where('id', $incentive_id)->first();
        
        if(!empty($incentive)){
            $earn_home_id = EarningScheme::where('id',$incentive->earning_category_id)->value('home_id');
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            if($earn_home_id != $home_id){
                echo 'AUTH_ERR'; 
            
            } else{
                $incentive = Incentive::where('id', $incentive_id)->update(['is_deleted'=>'1']);
                echo $incentive;
            }
        }
        die;
    }

    public function update_incentive_status($incentive_id = null){

        $incentive = Incentive::where('id', $incentive_id)->first();
        
        if(!empty($incentive)){

            $earn_home_id = EarningScheme::where('id',$incentive->earning_category_id)->value('home_id');
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            if($earn_home_id != $home_id){
                echo 'AUTH_ERR'; 
            
            } else{
                
                if($incentive->status == 0){
                    $new_status = 1;
                }
                else{
                    $new_status = 0;
                }

                if($update_status->save()){
                    echo true;
                } else{
                    echo false;
                }
            }
        }
        die;
    }

    public function view_incentive($incentive_id) {

        // $home_id = Auth::user()->home_id;
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $incentive = Incentive::select('id','earning_category_id','name','stars','details','url')
                                ->where('id', $incentive_id)
                                ->orderBy('id','desc')
                                ->first();
        
        if(!empty($incentive)) {

            //home check
            $earn_home_id = EarningScheme::where('id',$incentive->earning_category_id)->value('home_id');
            if($earn_home_id != $home_id){
                $result['response']         = false;
                //echo 'AUTH_ERR'; 
            }  else{
                $result['name']         = $incentive->name;
                $result['stars']        = $incentive->stars;
                $result['details']      = $incentive->details;
                $result['url']          = $incentive->url;
                $result['response']     = true;
            }

        }  else {
            $result['response']         = false;
        }
        return $result;
    } 

    public function edit_incentive(Request $request) {
        
        $data = $request->all();
        if(isset($data['incentive_id']))  {
            $incentive_ids = $data['incentive_id'];
            if(!empty($incentive_ids)){
                
                $view_incentive = Incentive::find($incentive_ids);
                
                if(!empty($view_incentive)){
                
                    $earn_home_id = EarningScheme::where('id',$view_incentive->earning_category_id)->value('home_id');
                    $home_ids = Auth::user()->home_id;
                    $ex_home_ids = explode(',', $home_ids);
                    $home_id = $ex_home_ids[0];
                    if($earn_home_id == $home_id){
                    
                        $view_incentive->stars   = $data['incentive_stars'];
                        $view_incentive->details = $data['incentive_detail'];
                        $view_incentive->url     = $data['incentive_url'];
                        if($view_incentive->save()) {
                            echo true;
                        }  else {
                            echo false;
                        }   
                    } 
                }
            }
        }
        die;
    }
}