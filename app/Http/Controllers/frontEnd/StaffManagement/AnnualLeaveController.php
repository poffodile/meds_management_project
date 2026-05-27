<?php
namespace App\Http\Controllers\frontEnd\StaffManagement;
use App\Http\Controllers\frontEnd\StaffManagementController;
use Illuminate\Http\Request;
use App\User, App\StaffAnnualLeave;
use DB, Auth;

class AnnualLeaveController extends StaffManagementController
{   

    public function index($staff_member_id) {

        $sm_home_id = User::where('id',$staff_member_id)->value('home_id');
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        if($home_id != $sm_home_id){
            die; 
        }

        $sm_annual = StaffAnnualLeave::where('is_deleted','0')
                                    ->where('staff_member_id', $staff_member_id)
                                    ->orderBy('id','desc');

        $pagination = '';

        if(isset($_GET['search'])) {

            $sm_search_type =  $_GET['sm_search_type'];
            if($sm_search_type == 'title') {
                $sm_annual_form = $sm_annual->where('staff_annual_leave.title','like','%'.$_GET['search'].'%')->get();
            } else {
                $sm_annual_leave_date   = date('Y-m-d',strtotime($_GET['sm_annual_leave_date']));
                $sm_annual_form         = $sm_annual->where('staff_annual_leave.leave_date',$sm_annual_leave_date)
                                            ->get();                         
            }

        } else {

            $sm_annual_form  = $sm_annual->paginate(50);
            if($sm_annual_form->links() != '') {
                $pagination .= '<div class="m-l-15 position-botm">';
                $pagination .= $sm_annual_form->links();
                $pagination .= '</div>';
            }
        }

        foreach ($sm_annual_form as $key => $value) {        

            echo '  <div class="col-md-12 col-sm-12 col-xs-12 cog-panel remove-annual-rec-row">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                            <div class="col-md-12 col-sm-11 col-xs-12 r-p-0">
                                <div class="input-group popovr">
                                    <input class="form-control" name="" disabled="" value="'.$value->title.'" maxlength="255" type="text">
                                    <span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
                                        <i class="fa fa-cog"></i>
                                        <div class="pop-notifbox">
                                            <ul class="pop-notification" type="none">
                                            <li> <a href="#" sm_annual_leave_id='.$value->id.' data-dismiss="modal" aria-hidden="true" class="view-annual-leave-content"> <span> <i class="fa fa-eye"></i> </span> View </a> </li>
                                            <li> <a href="#" class="edit_annual_leave_content" sm_annual_leave_id='.$value->id.' data-dismiss="modal" aria-hidden="true"> <span> <i class="fa fa-pencil"></i> </span> Edit </a> </li>
                                            <li> <a href="#" class="delete_annual_record" sm_annual_leave_id='.$value->id.'> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>
                                            <li>  <a href="'.url('/system/calendar').'">  <span class="color-green"> <i class="fa fa-plus-circle"></i> </span> Add to calendar </a> </li>
                                            </ul>
                                        </div>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>' ;
        }
        echo $pagination;
    }

    public function add(Request $request) {

        $data = $request->all();

        if($request->isMethod('post')) {

            // $home_id = Auth::user()->home_id;
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];

            $annual                  = new StaffAnnualLeave;
            $annual->home_id         = $home_id;
            $annual->staff_member_id = $data['staff_member_id'];
            $annual->title           = $data['annual_title'];
            $annual->leave_date      = date('Y-m-d', strtotime($data['annual_leave_date']));
            $annual->reason          = $data['annual_leave_reason'];
            //$annual->no_of_days      = $data['leave_days'];
            $annual->comments        = $data['annual_leave_comment'];

            if($annual->save()) {
                $result['response'] = '1';
            } else {
                $result['response'] = '0';
            }
            return $result;
        }
    } 

    public function view_annual_record($annual_leave_id = null) {

        // $home_id  = Auth::user()->home_id;
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];

        $annual_record = StaffAnnualLeave::select('staff_annual_leave.*')
                                    ->where('staff_annual_leave.id', $annual_leave_id)
                                    ->where('staff_annual_leave.home_id', $home_id)
                                    ->where('staff_annual_leave.is_deleted', '0')
                                    ->get();
        
        foreach ($annual_record as $key => $value) {
                
                $leave_date = date('d-m-Y', strtotime($value->leave_date));

            echo '<div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                    <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                        <label class="col-md-1 col-sm-1 col-xs-12"> Title: </label>
                        <div class="col-md-11 col-sm-11 col-xs-12 r-p-0">
                            <div class="input-group popovr">
                                <input name="v_annual_title" value="'.$value->title.'" disabled="" type="text" class="form-control edit_annual" maxlength="255"/>
                                <input type="hidden" name="staff_annual_leave_id" value="'.$value->id.'"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-12 col-sm-12 col-xs-12 datepicker-sttng date-sttng cog-panel">
                    <label class="col-md-1 col-sm-1 col-xs-12">Leave Date:</label>
                    <div class="col-md-11 col-sm-11 col-xs-12 r-p-0">
                        <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date"> 
                           <input name="v_annual_date" type="text" value="'.$leave_date.'" readonly="" size="16" class="form-control edit-date-annual edit_annual" disabled="">
                            <span class="input-group-btn add-on datetime-picker2">
                                <input type="text" value="" name="" id="edit-annual-date" class="form-control date-btn2">
                                <button class="btn btn-primary" type="button"><span class="glyphicon glyphicon-calendar"></span></button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                    <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                        <label class="col-md-1 col-sm-1 col-xs-12"> Reason: </label>
                        <div class="col-md-11 col-sm-11 col-xs-12 r-p-0">
                            <div class="input-group popovr">
                                <textarea name="v_annual_reason" value="" rows="3" type="text" class="form-control edit_annual" maxlength="1000" disabled="">'.$value->reason.'</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                    <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                        <label class="col-md-1 col-sm-1 col-xs-12"> Comment: </label>
                        <div class="col-md-11 col-sm-11 col-xs-12 r-p-0">
                            <div class="input-group popovr">
                                <textarea name="v_annual_comment" value="" rows="3" type="text" class="form-control edit_annual" maxlength="1000" disabled="">'.$value->comments.'</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer m-t-0 m-b-15 modal-bttm" id="">
                    <input type="hidden" name="_token" value="'.csrf_token().'">
                    <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                    <button class="btn btn-warning sbt-edit-annual-leave-form edit_annual" type="submit" disabled=""> Confirm </button>
                </div>';
        } 
        //die;
        /*<div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                    <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                        <label class="col-md-1 col-sm-1 col-xs-12"> No. of days: </label>
                        <div class="col-md-11 col-sm-11 col-xs-12 r-p-0">
                            <div class="input-group popovr">
                                <input name="v_annual_days" disabled="" value="'.$value->no_of_days.'" type="text" class="form-control edit_annual" maxlength="255"/>
                            </div>
                        </div>
                    </div>
                </div>*/
    }


    public function edit(Request $request) {

        $data = $request->all();
                
        $staff_annual_leave_id = $data['staff_annual_leave_id'];
        // $home_id  = Auth::user()->home_id;
         $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $edit_record = StaffAnnualLeave::find($staff_annual_leave_id);
        if(!empty($edit_record)) {
            $sm_home_id = User::where('id', $edit_record->staff_member_id)->value('home_id');
            if($home_id == $sm_home_id) {
                $edit_record->title      = $data['v_annual_title'];
                $edit_record->leave_date = date('Y-m-d', strtotime($data['v_annual_date']));
                $edit_record->reason     = $data['v_annual_reason'];
                //$edit_record->no_of_days = $data['v_annual_days'];
                $edit_record->comments   = $data['v_annual_comment'];
                if($edit_record->save()) {
                    echo "true";
                } else{
                    echo "false";
                }
            } else {
                echo UNAUTHORIZE_ERR;
            }

        }
    }

    public function delete($annual_leave_id = null) {

        $annual_record = StaffAnnualLeave::find($annual_leave_id);

        if(!empty($annual_record)) {
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];

            $sm_home_id = User::where('id',$annual_record->staff_member_id)->value('home_id');

            if($sm_home_id == $home_id){
        
                $res = StaffAnnualLeave::where('id', $annual_leave_id)->update(['is_deleted' => '1']);
                echo $res;            
            }
        }
        die;

    }


}
