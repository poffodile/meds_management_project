<?php

namespace App\Http\Controllers\frontend\roster\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\ServiceUser;
use App\Models\suUserCourse;
use Auth,DB,Session;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function index()
    {
        $home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id = $ex_home_ids[0];
        $query = ServiceUser::select('id','home_id','earning_scheme_label_id','name','user_name','phone_no','date_of_birth','child_type','room_type','current_location','street','care_needs','suFundingType','status','is_deleted')
        ->where(['home_id'=>$home_id,'is_deleted'=>0]);
        $data['child'] = $query->get();
        $data['active_child_count']= (clone $query)->where('status',1)->get();
        $data['inactive_child_count'] = (clone $query)->where('status',0)->get();
        // echo "<pre>";print_r($data['active_child_count']);die;
        return view('frontEnd.roster.client.client',$data);
    }

    public function client_details($client_id)
    {
        $clientData = $this->child_courses($client_id);
        $responseData = $clientData->getData(true);
        $data['clientDetails'] = $responseData['data'];
        if($data['clientDetails']['status'] == 1){
            $status = 'Active';
        }else if($data['clientDetails']['status'] == 0){
            $status = 'Inactive';
        }else{
            $status = 'Archived';
        }
        $data['status'] = $status;
        // echo "<pre>";print_r($data['clientDetails']);die;
        return view('frontEnd.roster.client.client_details',$data);
    }
    public function child_courses($childId){
        // $all_courses = suUserCourse::where('su_user_id',$childId)->get();
        $all_courses = ServiceUser::with('courses')->where('id',$childId)->first();
        return response()->json(['success'=>true,'data'=>$all_courses]);
    }
    public function client_delete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'id'=>'required|exists:service_user,id',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        $user = ServiceUser::find($request->id);
        $user->is_deleted = 1;
        $user->save();
        Session::flash('success','Client deleted successfully done.');
        return [
                'success' => true,
                'message' => 'Client deleted successfully done.'
            ];
    }
    public function client_search(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id = $ex_home_ids[0];
        
        $query = ServiceUser::select('id','home_id','earning_scheme_label_id','name','user_name','phone_no','date_of_birth','child_type','room_type','current_location','street','care_needs','suFundingType','status','is_deleted')
         ->where(function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->user_name . '%')
            ->orWhere('user_name', 'like', '%' . $request->user_name . '%');
        })
        ->where(['home_id'=>$home_id,'is_deleted'=>0]);
          $query_AllSql =  (clone $query)->get();
          $query_ActiveSql =  (clone $query)->where('status',1)->get();
          $query_InactiveSql = ( clone $query)->where('status',0)->get();

        $allHtml_data = $this->html_data_prepaire($query_AllSql);
        $activeHtml_data = $this->html_data_prepaire($query_ActiveSql);
        $InactiveHtml_data = $this->html_data_prepaire($query_InactiveSql);
        return response()->json(['success'=>true,'allHtml_data'=>$allHtml_data,'activeHtml_data'=>$activeHtml_data,'InactiveHtml_data'=>$InactiveHtml_data,'query_AllSqlData'=>$query_AllSql,'query_ActiveSqlData'=>$query_ActiveSql,'query_InactiveSql'=>$query_InactiveSql]);
    }
    public function html_data_prepaire($query){
        $html_data='';
        foreach($query as $childVal){
            $html_data.= '
            <div class="col-md-4">                                 
                <div class="profile-card">
                    <div class="card-header">
                        <div class="user">
                            <div class="avatar">'.strtoupper(substr($childVal->name, 0, 1)).'</div>
                            <div class="info">
                                <div class="name"><a href="'.url("roster/client-details/".$childVal->id) .'"> '.$childVal->name.'</a></div>
                                <div class="role">'.$childVal->suFundingType.'</div>
                            </div>
                        </div>';
                        if($childVal->status == 1){
                        $html_data.= '<span class="status greenShowbtn">Active</span>';
                        }else{
                        $html_data.= '<span class="status radShowbtn">Inactive</span>';
                        }
                    $html_data.= '</div>
                    <div class="details">
                        <div class="item">
                            <i class="fa-solid fa-phone"></i> <span>'.$childVal->phone_no.'</span>
                        </div>
                        
                        <div class="item">
                            <i class="fa-solid fa-location-dot"></i> <span>'.$childVal->street.'</span>
                        </div>
                    </div>
                    <div class="section care-needs">
                        <div class="label">
                            <i class="fa-regular fa-heart"></i>
                            Care Needs:
                        </div>

                        <div class="sectionCarer">

                            <div class="tags">';
                            $moreNeeds=0;
                            if(!empty($childVal->care_needs)){
                                $ex=explode(',',$childVal->care_needs);
                                $moreNeeds=count($ex)-5;
                                for($i=0;$i<5;$i++){
                                    if(!empty($ex[$i])){
                                        $html_data.='<span>'.$ex[$i].'</span>';
                                    }
                                }
                            }
                            if($moreNeeds > 0){
                                $html_data.='<button class="care-more">+'.$moreNeeds.' more</button>';
                                }
                            $html_data.='</div>
                        </div>
                    </div>
                    
                    <div class="actions">
                        <button class="view" type="button" onclick="redirectLocation('.$childVal->id.')">
                            <i class="fa-regular fa-eye"></i>
                            View Details
                        </button>
                        <button class="edit" type="button" data-toggle="modal" data-target="#addServiceUserModal" data-child_id="'.$childVal->id.'">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                        <button class="delete client_delete" type="button" data-child_id="'.$childVal->id.'">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </div>
                </div>                                
            </div>';
        }
        return $html_data;
    }
    public function care_task_add(){
        return view('frontEnd.roster.client.care_task_form');
    }
   
}
