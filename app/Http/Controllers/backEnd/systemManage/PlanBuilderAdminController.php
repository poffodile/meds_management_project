<?php

namespace App\Http\Controllers\backEnd\systemManage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use App\PlanBuilder;

class PlanBuilderAdminController extends Controller
{
    public function index(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $page='appointmen_plans';
        $limit = $request->limit ?? 10;
        $home_id = Session::get('scitsAdminSession')->home_id;
        $appointments = PlanBuilder::where('home_id',$home_id)->where('is_deleted','0');
        $search = '';
        if(isset($request['search']))	{  
            //editing the records
            $search = $request['search'];
            
            $plan_builder = $appointments->where('title','like','%'.$request['search'].'%')->orderBy('id','desc')->get();  

        } else  {
            $plan_builder = $appointments->orderBy('id','desc')->get();
        }
        // echo "<pre>";print_r($plan_builder);die;
        return view('backEnd.systemManage.appointment_plan.appointment_plan',compact('page','limit','search','plan_builder'));
    }
    public function plan_add(){
        $page='appointmen_plans';
        return view('backEnd.systemManage.appointment_plan.appointment_plan_form',compact('page'));
    }
    public function store(Request $request){
        // echo "<pre>";print_r($request->all());die;
        if($request->isMethod('post'))	{
			$data = $request->input();
            if(isset($data['formdata']))	{
				$data['formdata'] = array_values($data['formdata']);
			} else  {
				echo 'empty'; die;
			}
			// echo '<pre>'; print_r($data['formdata']); die;
            if($data['id'] ==''){
                $plan 			= new PlanBuilder;
                $plan->home_id 	= Session::get('scitsAdminSession')->home_id;
                $plan->title 	= $data['plan_title'];
                $plan->pattern 	= json_encode($data['formdata']);
                $plan->icon 	= $data['plan_icon'];
                $plan->detail   = $data['plan_detail'];
                if($plan->save()){
                    echo 'true';
                } else{
                    echo 'false';
                }
            }else{
                $plan_id 		= $data['id'];
                $plan 			= PlanBuilder::where('id',$plan_id)->first();
                if(!empty($plan)){
                    $plan->title 	= $data['plan_title'];
                    $plan->pattern 	= json_encode($data['formdata']);
                    $plan->icon 	= $data['plan_icon'];
                    $plan->detail   = $data['plan_detail'];
                    if($plan->save()){
                        echo 'true';
                    } else{
                        echo 'false';
                    }
                } else{
                        echo 'false';
                }
            }
		} 
    }
    public function edit($id){
        $page='appointmen_plans';
        return view('backEnd.systemManage.appointment_plan.appointment_plan_form',compact('page','id'));
    }
    public function view(Request $request, $plan_id = null)	{

		$plan = PlanBuilder::where('id',$plan_id)->where('home_id',Session::get('scitsAdminSession')->home_id)->first();
		if(!empty($plan))	{
			$plan->pattern = json_decode($plan->pattern);
			//echo '<pre>'; print_r($plan->pattern); die;
			$formdata = '';

			$total_fields = 0;
			foreach($plan->pattern as $key => $value){
				$total_fields++;
				$field_name = $value->label;
				$field_type = $value->column_type;
				$column_name = $value->column_name;
				$column_type = $value->column_type;	

				if($field_type == 'Textbox'){
					$formdata .= '<div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 cus-field "> <label class="col-md-2 col-sm-2 col-xs-12 p-t-7" type="'.$field_type.'"> '.$field_name.': </label> <div class="col-md-8 col-sm-8 col-xs-12 p-l-0"> <input type="text" name="'.$column_name.'" class="form-control trans" readonly>  <input type="hidden" name="formdata['.$key.'][label]" value="'.$field_name.'"> <input type="hidden" name="formdata['.$key.'][column_name]" value="'.$column_name.'"> <input type="hidden" name="formdata['.$key.'][column_type]" value="'.$field_type.'"> </div> <div class="col-md-1 col-sm-1 col-xs-1 p-0"> <button class="btn group-ico field-remove-btn" type="button"> <i class="fa fa-minus"></i> </button> </div> <div class="col-md-1 col-sm-1 col-xs-1 m-t-5 p-l-10"><span class="sort-sp"><i class="fa fa-sort"></i></span></div> </div>';
				
				} else if($field_type == 'Selectbox')	{
		            $options = '';
		            $option_name = '';
		            $option_value = '';
		            $opt = '';
		            $j = 0;
		            ///alert(option_count);
		            //for($i=1; $i <= $option_count; $i++){
		            foreach($value->select_options as $select_option)	{
		                
		                $select_option = (array)$select_option;
		                
	                    $option_name 	= $select_option['option_name'];

                        $options .= '<option value="'.$option_name.'">'.$option_name.'</option>';

                        $opt .= '<input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_name]" value="'.$option_name.'"> ';
                        $j++;

	                    /* for option name and value
	                    $option_value   = $select_option['option_value'];
	                    
	                    //if( ($option_name !== '') && ($option_value !== '') ) {            
	                        $options .= '<option value="'.$option_value.'">'.$option_name.'</option>';

	                        $opt .= '<input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_name]" value="'.$option_name.'"> <input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_value]" value="'.$option_value.'">';
	                        $j++;
	                    //}*/
		            }
		          	
		            $formdata .='<div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 cus-field "> <label class="col-md-2 col-sm-2 col-xs-12 p-t-7" type="'.$field_type.'"> '.$field_name.' </label> <div class="col-md-8 col-sm-8 col-xs-12 p-l-0"> <div class="select-style"> <select name="'.$column_name.'"  class="trans form-control"> '.$options.' </select>  <input type="hidden" name="formdata['.$key.'][label]" value="'.$field_name.'"> <input type="hidden" name="formdata['.$key.'][column_name]" value="'.$column_name.'"> <input type="hidden" name="formdata['.$key.'][column_type]" value="'.$field_type.'"> '.$opt.' </div> </div> <div class="col-md-1 col-sm-1 col-xs-1 p-0"> <button class="btn group-ico field-remove-btn" type="button"> <i class="fa fa-minus"></i> </button> </div> <div class="col-md-1 col-sm-1 col-xs-1 m-t-5 p-l-10"><span class="sort-sp"><i class="fa fa-sort"></i></span></div> </div>';    

		        } else if($field_type == 'Textarea') {
		            $formdata .='<div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 cus-field "> <label class="col-md-2 col-sm-2 col-xs-12 p-t-7" type="'.$field_type.'"> '.$field_name.' </label> <div class="col-md-8 col-sm-8 col-xs-12 p-l-0">  <textarea name="'.$column_name.'" class="form-control trans" readonly></textarea> </div> <input type="hidden" name="formdata['.$key.'][label]" value="'.$field_name.'"> <input type="hidden" name="formdata['.$key.'][column_name]" value="'.$column_name.'">  <input type="hidden" name="formdata['.$key.'][column_type]" value="'.$field_type.'">  <div class="col-md-1 col-sm-1 col-xs-1 p-0"> <button class="btn group-ico field-remove-btn" type="button"> <i class="fa fa-minus"></i> </button> </div> <div class="col-md-1 col-sm-1 col-xs-1 m-t-5 p-l-10"><span class="sort-sp"><i class="fa fa-sort"></i></span></div> </div>'; 

		        } else if($field_type == 'Date') {
		            $formdata .='<div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 cus-field "> <label class="col-md-2 col-sm-2 col-xs-12 p-t-7" type="'.$field_type.'"> '.$field_name.' </label>  <div class="col-md-8 col-sm-8 col-xs-12 p-l-0">  <div data-date-format="dd-mm-yyyy" data-date="12-02-2012" class="input-group date dpYears">  <input name="date" readonly value="" size="16" class="form-control trans" type="text"> <input type="hidden" name="formdata['.$key.'][label]" value="'.$field_name.'"> <input type="hidden" name="formdata['.$key.'][column_name]" value="'.$column_name.'"> <input type="hidden" name="formdata['.$key.'][column_type]" value="'.$field_type.'"> <span class="input-group-btn "> <button class="btn btn-primary calndr-btn" type="button"><i class="fa fa-calendar"></i></button> </span>  </div> </div> <div class="col-md-1 col-sm-1 col-xs-1 p-0"> <button class="btn group-ico field-remove-btn" type="button"> <i class="fa fa-minus"></i> </button>  </div> <div class="col-md-1 col-sm-1 col-xs-1 m-t-5 p-l-10"><span class="sort-sp"><i class="fa fa-sort"></i></span></div> </div>';

		        } else{
		            $formdata .='';
		        }
			}
			
			$result['response'] = true;
			
			$result['total_fields'] = $total_fields;
			$result['plan_id'] 		= $plan->id;
			$result['title'] 		= $plan->title;
			$result['icon'] 		= $plan->icon;
			$result['detail']		= $plan->detail;
			$result['formdata'] 	= $formdata;

		} else{
			$result['response'] = false;
		}
		return $result;		
	}
    public function delete($plan_id) {
		$plan = PlanBuilder::where('id', $plan_id)->update(['is_deleted'=>'1']);
        Session::flash('success','Plan Deleted Successfully Done');
		return redirect()->back();
	}
}
