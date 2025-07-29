<?php 
namespace App\Http\Controllers\frontEnd\StaffManagement;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Pagination\Paginator;
use App\Training, App\StaffTraining, App\User;
use Auth,View,Redirect,DB;
use Illuminate\Support\Facades\Mail;


class TrainingController extends Controller 
{
	public function index(){
		//list of training modules acc. to homeid
		if(isset($_GET['year'])){
			$year = $_GET['year'];
		} else{
			$year = date("Y");
		}
		$home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id=$ex_home_ids[0];
		$list_training = Training::where('home_id',$home_id)->where('training_year',$year)->where('is_deleted', '0')->orderBy('training_month','asc')->get()->toArray(); 
		if(!empty($list_training)){
			foreach ($list_training as $key => $training) {
				$trainings[$training['training_month']][$key]['id']		= $training['id'];
				$trainings[$training['training_month']][$key]['name']	= $training['training_name'];
			}
		} else{
			$trainings = '';
		}	
		return View::make('frontEnd.staffManagement.training_listing')->with('training',$trainings);
	}

	public function add(Request $r){
		$data = $r->input();
		$home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id=$ex_home_ids[0];
		$training 					= new Training;
		$training->home_id			= $home_id;
		$training->training_name	= $data['name'];
		$training->training_provider= $data['training_provider'];
		$training->training_desc	= $data['desc'];
		$training->training_month	= $data['month'];
		$training->training_year	= $data['year'];
		$training->status 			= 0;
		if($training->save()){
			return redirect()->back()->with('success','Training added successfully.');
		} else{
			return redirect()->back()->with('error',COMMON_ERROR);
		}
	}

	public function view($id=null){
		$home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id=$ex_home_ids[0];
		$completed_training = 	DB::table('user')
								->join('staff_training', 'staff_training.user_id', '=', 'user.id')
								->where('staff_training.training_id',$id)
								->where('staff_training.status',1)
								->select('user.name','staff_training.id')
								->paginate(7);
		
		$active_training 	= 	DB::table('user')
								->join('staff_training', 'staff_training.user_id', '=', 'user.id')
								->where('staff_training.training_id',$id)
								->where('staff_training.status',2)
								->select('user.name','staff_training.id')
								->paginate(5);

		$not_completed_training = DB::table('user')
								->join('staff_training', 'staff_training.user_id', '=', 'user.id')
								->where('staff_training.training_id',$id)
								->where('staff_training.status',0)
								->select('user.name','staff_training.id')
								->paginate(2);
								// $not_completed_training = DB::table('user')
								// ->paginate(1);

		$home_id = $home_id;
		// $home_users = ServiceUser::select('name','id')->where('home_id',$home_id)->get()->toArray();
		$home_users = User::select('name','id')->where('home_id',$home_id)->where('is_deleted','0')->get()->toArray();
		
		$training_id = $id;
		$training_name = Training::where('id',$id)->value('training_name');
		
		/*echo "<pre>";
		print_r($not_completed_training);
		die;*/
		return view('frontEnd.staffManagement.training_view',compact('completed_training','active_training','not_completed_training','training_id','training_name','home_users'));
	}

	public function completed_training($id=null){
		$completed_training = 	DB::table('user')
								->join('staff_training', 'staff_training.user_id', '=', 'user.id')
								->where('staff_training.training_id',$id)
								->where('staff_training.status',1)
								->select('user.name','staff_training.id')
								->paginate(1);
		
		foreach($completed_training as $complete){
            echo '<div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <a href="">'.$complete->name.'</a> <span class="color-green m-l-15"><i class="fa fa-check"></i></span>
                        </div>';
		}
        
        echo '<div class="col-md-12 col-sm-12 col-xs-12 clearfix completed">'.
                                $completed_training->links()
                            .'</div>';
        die;
	}

	public function active_training($id=null){
		$active_training 	= 	DB::table('user')
								->join('staff_training', 'staff_training.user_id', '=', 'user.id')
								->where('staff_training.training_id',$id)
								->where('staff_training.status',2)
								->select('user.name','staff_training.id')
								->paginate(1);
		echo '<div class="form-group col-md-12 col-sm-12 col-xs-12 cog-panel p-0">';
		foreach($active_training as $active){
            echo 	'<div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0">
                    	<div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <a href="">'.$active->name.'</a>
                            <span class="m-l-15 clr-blue settings setting-sze">
                            	<i class="fa fa-cog"></i>
                                <div class="pop-notifbox">
                                	<ul class="pop-notification" type="none">
                                    	<li> <a href="'.url('/staff/training/status/update').'/'.$active->id.'completed'.'"> <span class="color-green"> <i class="fa fa-check"></i> </span> Mark complete </a> </li>
                                        <li> <a href="'.url('/staff/training/status/update').'/'.$active->id.'/'.'notcompleted'.'"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Mark uncomplete </a> </li>
                                    </ul>
                                </div>
                            </span>
                        </div>
                    </div>';
		}
		echo '</div>
        		<div class="col-md-12 col-sm-12 col-xs-12 clearfix active_training">'.
                    $active_training->links()
                .'</div>';
        die;
	}

	public function not_completed_training($id=null){
		$not_completed_training = 	DB::table('user')
								->join('staff_training', 'staff_training.user_id', '=', 'user.id')
								->where('staff_training.training_id',$id)
								->where('staff_training.status',0)
								->select('user.name','staff_training.id')
								->paginate(1);
		foreach($not_completed_training as $not){
            echo 	'<div class="form-group col-md-12 col-sm-12 col-xs-12">
                        <a href="">'. $not->name .'</a>
                    	<span class="m-l-15 clr-blue settings setting-sze">
                            <i class="fa fa-cog"></i>
                            <div class="pop-notifbox">
                                <ul type="none" class="pop-notification">
                                    <li> <a href="'.url('/staff/training/status/update').'/'.$not->id.'/'.'activate'.'"> <span> <i class="fa fa-pencil"></i> </span> Mark Active </a> </li>
                                    <li> <a href="'.url('/staff/training/status/update').'/'.$not->id.'/'.'complete'.'"> <span class="color-green"> <i class="fa fa-check"></i> </span> Mark complete </a> </li>
                                </ul>
                            </div>
                        </span>
                    </div>';
		}
        
        echo '<div class="col-md-12 col-sm-12 col-xs-12 clearfix not-completed">'.
                                $not_completed_training->links()
                .'</div>';
        die;
	}

	public function status_update($training_id=null){

		$status = $_GET['status'];
 		// echo $training_id."<br>".$status; die;
		if($training_id != '' && $status != ""){
			if($status == "complete"){
				StaffTraining::where('id',$training_id)->update(['status'=>1]);
			} elseif($status == "activate"){
				StaffTraining::where('id',$training_id)->update(['status'=>2]);
			} elseif($status == "notcompleted"){
				StaffTraining::where('id',$training_id)->update(['status'=>0]);
			}else{
				return Redirect::back();
			}
			return Redirect::back();
		}
		
	}

	public function add_user_training(Request $r){
		$data = $r->input();
		$home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id=$ex_home_ids[0];

		if(isset($data['user_ids'])){
			foreach ($data['user_ids'] as $key => $value) {
				$train  					= new StaffTraining;
				$train->user_id				= $value;
				$train->training_id			= $data['training_id'];
				$train->status 				= 2;
				$train->save();
			}
			if($train){

				$training_id = $r->training_id;
				// $home_id     = Auth::User()->home_id;
				$training    = Training::where('home_id', $home_id)->where('id', $training_id)->first();
				
				$trainee_id  = $train->user_id;
				$trainee     = User::select('name','id','email')->where('home_id', $home_id)->where('id',$trainee_id)->where('is_deleted','0')->first();
				$name              = $trainee->name;
				$email             = $trainee->email;
				$training_name     = $training->training_name;
				$training_provider = $training->training_provider;
				$training_month    = $training->training_month;
				$training_year     = $training->training_year;
				$training_desc     = $training->training_desc;
				// echo $email;die;
				if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {

                            Mail::send('emails.selected_trainee', ['name'=> $name,'training_name'=>$training_name,'training_provider'=>$training_provider,'training_month'=>$training_month,'training_year'=>$training_year,'training_desc'=>$training_desc], function($message) use ($email)
                            {
                                $message->to($email)->subject('Scits Training Mail');
                            });    
                }
				
				return redirect()->back()->with('success','Training has been added for staff members successfully.');
			} else {

				return redirect()->back()->with('error',COMMON_ERROR);
			}			
		} else {
			
			return redirect()->back()->with('error','No Staff Member is selected.');
		}
	}
	
	public function view_fields(Request $request, $training_id)	{
		$home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id=$ex_home_ids[0];
		// $home_id = Auth::User()->home_id;
		$training = Training::where('home_id', $home_id)->where('id', $training_id)->first();

		if(!empty($training)) {

			if($home_id != $training->home_id) {

				return redirect()->back()->with('error', UNAUTHORIZE_ERR);
			} else {
				
				$result['response']     	= true;
				$result['training_id']     	= $training->id;
                $result['training_name']   	= $training->training_name;
                $result['training_provider']= $training->training_provider;
                $result['training_month']   = $training->training_month;
                $result['training_year'] 	= $training->training_year;
                $result['training_desc']	= $training->training_desc;
			}
		}	else {

			$result['response'] = false;
		}
		return $result;
	}

	public function edit_fields(Request $request)	{

		$data = $request->input();
		$home_id = Training::where('id', $data['training_id'])->value('home_id');

		if($data['home_id'] == $home_id){
			
			$training = Training::find($data['training_id']);

			$training->training_name 	 = $data['name'];
			$training->training_provider = $data['training_provider'];
			$training->training_desc 	= $data['desc'];
			$training->training_month 	= $data['month'];
			$training->training_year 	= $data['year'];

			if($training->save()) {

				return redirect()->back()->with('success', 'Staff Training updated successfully' );
			} else {
				return redirect()->back()->with('error', 'Some Error Occured, Try Again Later' );
			}
		} else {
				return redirect()->back()->with('error', UNAUTHORIZE_ERR);
		}
	}

	public function delete($training_id) {
		$home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id=$ex_home_ids[0];
		$delete_record = Training::where('id', $training_id)->where('home_id',$home_id)->update(['is_deleted' => '1']);
		if($delete_record) {
			return redirect()->back()->with('success', 'Training record deleted successfully.');
		} else {
			return redirect()->back()->with('error', UNAUTHORIZE_ERR);
		}
	}


	// public function add_user_training(Request $r){
	// 	$data = $r->input();
	// 	foreach ($data['user_ids'] as $key => $value) {
	// 		$train  					= new StaffTraining;
	// 		$train->user_id		= $value;
	// 		$train->training_id			= $data['training_id'];
	// 		$train->status 				= 0;
	// 		$train->save();
	// 	}
	// 	if($train){
	// 		return Redirect::back();
	// 	} else{
	// 		return Redirect::back();
	// 	}
	// }
	



}


?>