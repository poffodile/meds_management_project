<?php

namespace App\Http\Controllers\frontEnd;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\PlanBuilder, App\ServiceUserCalendarEvent, App\ServiceUserCalendarNote,App\ServiceUser, App\Notification, App\MandatoryLeave;
use DB, Auth;

class CalendarEntryController extends Controller //appointment controller
{
	public function display_form(Request $request, $plan_id = null){
		
		$plan = PlanBuilder::where('id',$plan_id)->where('home_id',Auth::user()->home_id)->first();
		if(!empty($plan)){

			$plan->pattern = json_decode($plan->pattern);
			/*echo "<pre>";
			print_r($plan->pattern);
			die;*/
			$formdata = '';

			$total_fields = 0;
			foreach($plan->pattern as $key => $value){
				$total_fields++;
				$field_label = $value->label;
				$column_name = $value->column_name;
				$field_type = $value->column_type;	
				$field_value = '';	

				if($field_type == 'Textbox'){
					$formdata .= '
								<div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
		                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
		                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> '.$field_label.': </label>
		                                <div class="col-md-10 col-sm-10 col-xs-12 r-p-0">
		                                    <div class="input-group popovr">
		                                        <input name="formdata['.$column_name.']" value="'.$field_value.'" type="text" class="form-control"  />
		                                    </div>
		                                </div>
		                            </div>
		                        </div>';
				
				} else if($field_type == 'Selectbox'){
		            $options = '';
		            $option_name = '';
		            $option_value = '';
		            $opt = '';
		            $j = 0;
		            ///alert(option_count);
		            //for($i=1; $i <= $option_count; $i++){
		            foreach($value->select_options as $select_option){
		                //echo '<pre>'; print_r($select_option);
		                $select_option = (array)$select_option;
		                //print_r($se['option_name']); die;

	                    $option_name 	= $select_option['option_name'];
	                    //$option_value   = $select_option['option_value'];
	                    
                        $options .= '<option value="'.$option_name.'">'.$option_name.'</option>';

                        $opt .= '<input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_name]" value="'.$option_name.'">';
                        $j++;

	                    /* FOR BOTH OPTION AND VALUE
	                    $option_name 	= $select_option['option_name'];
	                    $option_value   = $select_option['option_value'];
	                    
	                    //if( ($option_name !== '') && ($option_value !== '') ) {            
	                        $options .= '<option value="'.$option_value.'">'.$option_name.'</option>';

	                        $opt .= '<input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_name]" value="'.$option_name.'"> <input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_value]" value="'.$option_value.'">';
	                        $j++;
	                    //}*/
		            }

		          	$formdata .= '
		          		<div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
			          		<div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
	                            <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> '.$field_label.': </label>
	                            <div class="col-md-10 col-sm-10 col-xs-10">
	                                <div class="select-style">
	                                    <select name="formdata['.$column_name.']" >
	                                        '.$options.'
	                                    </select>
	                                </div>
	                            </div>
                            </div>
                        </div>'; 

		        } else if($field_type == 'Textarea'){
		        	$formdata .='
		        		<div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> '.$field_label.': </label>
                                <div class="col-md-10 col-sm-10 col-xs-12 r-p-0">
                                    <div class="input-group popovr">
                                        <textarea name="formdata['.$column_name.']" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>';

		        } else if($field_type == 'Date'){
		        	$formdata .='
		        		<div class="col-md-12 col-sm-12 col-xs-12 cog-panel datepicker-sttng">      
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> '.$field_label.': </label>
                                <div class="col-md-10 col-sm-10 col-xs-12 r-p-0">
                                    <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date=""  class="input-group date dpYears">
                                        <input name="formdata['.$column_name.']" type="text" value="" size="16" class="form-control">
                                        <span class="input-group-btn add-on">
                                            <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>';

		        } else{
		            $formdata .='';
		        }
			}
			
			$result['response'] 	= true;
			
			//$result['total_fields'] = $total_fields;
			$result['plan_id'] 		= $plan->id;
			$result['title'] 		= $plan->title;
			//$result['icon'] 		= $plan->icon;
			//$result['detail']		= $plan->detail;
			$result['formdata'] 	= $formdata;

		} else{
			$result['response'] 	= false;
		}
		return $result;		
	}

	public function add(Request $request){

		if($request->isMethod('post')){
			$data = $request->input();
			// echo '<pre>'; print_r($request->input()); die;
			$home_ids = Auth::user()->home_id;
			$ex_home_ids = explode(',', $home_ids);
			$home_id=$ex_home_ids[0];

            $su_home_id = ServiceUser::where('id',$data['service_user_ids'])->value('home_id');
            if($su_home_id != $home_id){
                return redirect('/')->with('error',UNAUTHORIZE_ERR); 
            }

			if(isset($data['formdata'])){
				//echo 'empty'; die;
				$formdata = json_encode($data['formdata']);
			} else{
				$formdata = '';
			}

			$entry 						= new ServiceUserCalendarEvent;
			$entry->home_id 			= $home_id;
			$entry->service_user_id 	= $data['service_user_ids'];
			$entry->plan_builder_id 	= $data['plan_builder_id'];
			$entry->title 				= $data['entry_title'];
			$entry->user_id  			= $data['staff_user_id'];
			//$entry->date 				= date('y-m-d H:i:s',strtotime($data['date']));
			$entry->formdata 			= $formdata;

			if($entry->save()){
				
				//saving notification start
                $notification                                  = new Notification;
                $notification->service_user_id                 = $data['service_user_ids'];
                $notification->event_id                        = $entry->id;
                $notification->notification_event_type_id      = '19';
                $notification->event_action                    = 'ADD';      
                $notification->home_id                         = $home_id;
                $notification->user_id                         = Auth::user()->id;        
                $notification->save();
                //saving notification end
				
				return redirect()->back()->with('success','Event added successfully');
				/*$result['list'] = "<div class='external-event label label-event' event_id='".$entry->id."' event_type='E'>".$entry->title."</div>";
				$result['response'] = true;*/
			} else{ 
				return redirect()->back()->with('error','Some error occured, Please try again  after some time.');
				// $result['response'] = false;
			}
			// return $result;
		} 
		/*Note:
			plan == appointment == event all are same
		*/
	}

	public function add_note(Request $request)
	{
		if($request->isMethod('post')) {
			$data = $request->input();
			
			$service_user_id = $data['su_id'];
			$home_ids = Auth::user()->home_id;
			$ex_home_ids = explode(',', $home_ids);
			// $usr_home_id  = Auth::user()->home_id;
			$usr_home_id  = $ex_home_ids[0];
			
			$su_home_id   = ServiceUser::where('id',$service_user_id)->value('home_id');
            if($su_home_id != $usr_home_id){
                return redirect('/')->with('error',UNAUTHORIZE_ERR); 
            }

			$notes = new ServiceUserCalendarNote;
			$notes->service_user_id = $service_user_id;
			$notes->title           = $data['note_title'];
			$notes->note 			= $data['note'];
			$notes->home_id 		= $usr_home_id;

			if($notes->save()) {
				return redirect()->back()->with('success', 'Note Added successfully.');
			} else {
				return redirect()->back()->with('error', 'Some error occured, Please try again  after some time.');
			}

		} 
	}

	public function add_mandatory_leave(Request $request){
	
		if($request->isMethod('post')) {
			$data = $request->input();
			$service_user_id = $data['su_id_ml'];
			$home_ids = Auth::user()->home_id;
			$ex_home_ids = explode(',', $home_ids);
			$usr_home_id  = $ex_home_ids[0];
			$su_home_id   = ServiceUser::where('id',$service_user_id)->value('home_id');
			if($su_home_id != $usr_home_id){
                return redirect('/')->with('error', UNAUTHORIZE_ERR); 
            }

			$notes = new MandatoryLeave;
			$notes->home_id = $usr_home_id;
			$notes->user_id = Auth::user()->id;
			$notes->service_user_id = $service_user_id;
			$notes->title   = $data['ml_title'];
			// $notes->date 	= $data['ml_date'];
			$notes->notes 	= $data['ml_note'];

			if($notes->save()) {
				return redirect()->back()->with('success', 'Mandatory leave Added successfully.');
			} else {
				return redirect()->back()->with('error', 'Some error occured, Please try again after some time.');
			}
		} 
	}

}