<?php

namespace App\Http\Controllers\frontEnd\StaffManagement;

use App\Http\Controllers\frontEnd\StaffManagementController;
use Illuminate\Http\Request;
use App\User, App\StaffTaskAllocation;
use DB, Auth;

class TaskAllocationController extends StaffManagementController
{
    public function index($staff_member_id)
    {

        $sm_home_id = User::where('id', $staff_member_id)->value('home_id');
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        if ($home_id != $sm_home_id) {
            die;
        }

        //in search case editing start
        if (isset($_POST)) {
            $data = $_POST;
            $this->_edit($data);
        }


        $sm_task = StaffTaskAllocation::select('staff_task_allocation.*')
            ->where('is_deleted', '0')
            ->where('staff_member_id', $staff_member_id)
            ->orderBy('staff_task_allocation.id', 'desc')
            ->orderBy('staff_task_allocation.created_at', 'desc');
        //->get();

        $today = date('Y-m-d 00:0:00');
        $pagination = '';

        if (isset($_GET['logged'])) {
            $sm_task_allocation = $sm_task->where('staff_task_allocation.created_at', '<', $today);
            $sm_task_allocation = $sm_task_allocation->paginate(50);
            if ($sm_task_allocation->links() != '') {
                echo '<div class="m-l-15 position-botm">';
                echo $sm_task_allocation->links();
                echo '</div>';
            }
            $tick_btn_class = "sbt_edit_log_task";
        } elseif (isset($_GET['search'])) {

            $sm_search_type = $_GET['sm_search_type'];
            if ($sm_search_type == 'title') {

                $sm_task_allocation = $sm_task->where('staff_task_allocation.title', 'like', '%' . $_GET['search'] . '%')->get();
            } else {
                $search_date = date('Y-m-d', strtotime($_GET['sm_date'])) . ' 00:00:00';
                $search_date_next = date('Y-m-d', strtotime('+1 day', strtotime($_GET['sm_date']))) . ' 00:00:00';

                $sm_task_allocation = $sm_task->where('staff_task_allocation.created_at', '>', $search_date)
                    ->where('staff_task_allocation.created_at', '<', $search_date_next)
                    ->get();
            }
            $tick_btn_class = "search-task-alloc-btn";
        } else {
            $sm_task_allocation = $sm_task->where('staff_task_allocation.created_at', '>', $today)->get();
            $tick_btn_class = "sbt_edit_today_task";
        }

        // $sm_task_allocation_record  = $sm_task_allocation->paginate(3);

        if (!$sm_task_allocation->isEmpty()) {
            $pre_date = date('y-m-d', strtotime($sm_task_allocation['0']->created_at));
        }

        foreach ($sm_task_allocation as $key => $value) {

            if ($value->status == 1) {
                $record_set_btn_class = "clr-blue";
            } else {
                $record_set_btn_class = "clr_grey";
            }
            $isCompletedBtn = $value->is_completed == 1 ? "d-none" : "";

            $check = (!empty($value->details)) ? '<i class="fa fa-check"></i>' : '';

            $first = 0;

            if (isset($_GET['logged']) ||  isset($_GET['search'])) {
                $record_date = date('Y-m-d', strtotime($value->created_at));

                if ($record_date != $pre_date) {
                    $pre_date = $record_date;

                    echo '</div>
                    <div class="daily-rcd-head">
                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0 r-p-15 record_row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <a  class="date-tab">
                                    <span class="pull-left">
                                        ' . date('d F Y', strtotime($record_date)) . '
                                    </span>
                                    <i class="fa fa-angle-right pull-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="daily-rcd-content">';
                } else {
                }
            }

            echo '  <div class="delete-task-row cog-panel">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                            <!-- <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"></label> -->
                                <div class="col-md-12 col-sm-11 col-xs-12 r-p-0">
                                    <div class="input-group popovr">
                                        <input class="form-control edit_task_record edit_t_rcrd" name="" disabled="" value="' . $value->title . '" maxlength="255" type="text">';

            if (!empty($value->details)) {
                echo '<div class="input-plus color-green"> <i class="fa fa-plus"></i> </div>';
            }
            echo '<input type="hidden" name="edit_sm_task_id[]" value="' . $value->id . '" disabled class="edit_task_id_' . $value->id . '" />
                                        <span class="input-group-addon cus-inpt-grp-addon ' . $record_set_btn_class . ' settings">
                                            <i class="fa fa-cog"></i>
                                            <div class="pop-notifbox">
                                                <ul class="pop-notification" type="none">
                                                    <li class="' . $isCompletedBtn . '"> <a href="#" class="edit-task-detail" staff_m_task_id="' . $value->id . '"> <span> <i class="fa fa-pencil"></i> </span> Edit </a> </li>
                                                    <li class="' . $isCompletedBtn . '"> <a href="#" class="remove-task-record" staff_m_task_id="' . $value->id . '"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>
                                                    <li> <a href="#" class="task-detail" staff_m_task_id="' . $value->id . '"> <span class="color-red"> <i class="fa fa-plus color-green"></i> </span> Details</a></li>
                                                    <li class="' . $isCompletedBtn . '"> <a href="#" staff_m_task_id="' . $value->id . '" class="task_status_change"> <span class="color-yellow"> <i class="fa fa-bolt"></i> </span> Active/Inactive </a></li>
                                                    <li class="' . $isCompletedBtn . '"> <a href="#" staff_m_task_id="' . $value->id . '" class="task_is_completed"> <span class="color-yellow"> <i class="fa fa-check"></i> </span> Mark Completed </a></li>
                                                    <li class="' . $isCompletedBtn . '"> <a href="' . url('/system/calendar') . '">  <span class="color-green"> <i class="fa fa-plus-circle"></i> </span> Add to calendar </a> </li>
                                                </ul>
                                            </div>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Details textarea -->
                        <div class="col-md-12 col-sm-12 col-xs-12 input-plusbox form-group p-0 p-r-15 detail">
                            <label class="col-md-2 col-sm-2 col-xs-12 color-themecolor r-p-0"> Details: </label>
                            <div class="col-md-10 col-sm-10 col-xs-12 r-p-0 ">
                                <div class="input-group">
                                    <textarea class="form-control edit_task_record tick_text edit_t_rcrd txtarea edit_task_detail_' . $value->id . '" name="edit_task_details[]" disabled="" rows="5" value="" maxlength="1000">' . $value->details . '</textarea>
                                    <div class="input-group-addon cus-inpt-grp-addon sbt_tick_area"">
                                        <div class="tick_show sbt_btn_tick_div ' . $tick_btn_class . '">' . $check . '</div>
                                    </div>
                                  <!--   <span class="input-group-addon cus-inpt-grp-addon color-grey settings tick_show">' . $check . '</i>
                                    </span> -->
                                </div>
                            </div>
                        </div>
                    </div>';
        }
        //if(isset($_GET['logged'])) {
        // if($sm_task_allocation_record->links() != '') {
        //     echo '</div><div class="task_rec_paginate m-l-15 position-botm">';
        //     echo $sm_task_allocation_record->links();
        //     echo '</div>';
        // }
        //}

    }


    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            $data = $request->all();
            // $home_id               = $home_id;
            $task                  = new StaffTaskAllocation;
            $task->title           = $data['task_title'];
            $task->staff_member_id = $data['staff_member_id'];
            $task->status          = 1;
            $task->home_id         = $home_id;
            if ($task->save()) {
                $res = $this->index($data['staff_member_id']);
                echo $res;
            } else {
                echo '0';
            }
            die;
        }
    }


    public function delete($task_id)
    {

        $task_record = StaffTaskAllocation::find($task_id);

        if (!empty($task_record)) {

            $sm_home_id = User::where('id', $task_record->staff_member_id)->value('home_id');
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            if ($sm_home_id == $home_id) {

                $res = StaffTaskAllocation::where('id', $task_id)->update(['is_deleted' => '1']);
                echo $res;
            }
        }
        die;
    }

    public function edit(Request $request)
    {

        $data            = $request->all();
        $staff_member_id = $this->_edit($data);
        $res             = $this->index($staff_member_id);
        echo $res;
        die;
    }

    public function _edit($data = array())
    {

        $staff_member_id = '';

        if (isset($data['edit_sm_task_id'])) {
            $edit_sm_task_ids = $data['edit_sm_task_id'];

            if (!empty($edit_sm_task_ids)) {

                foreach ($edit_sm_task_ids as $key => $edit_sm_task_id) {

                    $sm_task = StaffTaskAllocation::find($edit_sm_task_id);
                    if (!empty($sm_task)) {
                        $staff_member_id = $sm_task->staff_member_id;
                        $sm_home_id      = User::where('id', $staff_member_id)->value('home_id');
                        $home_ids = Auth::user()->home_id;
                        $ex_home_ids = explode(',', $home_ids);
                        $home_id = $ex_home_ids[0];
                        if ($home_id == $sm_home_id) {

                            $sm_task->details = $data['edit_task_details'][$key];
                            $sm_task->save();
                        }
                    }
                }
            }
        }
        return $staff_member_id;
    }

    public function update_status($task_id = null)
    {

        $task_record = StaffTaskAllocation::where('id', $task_id)
            ->where('home_id', Auth::user()->home_id)
            ->where('is_deleted', '0')
            ->first();

        if (!empty($task_record)) {
            if ($task_record->status == '0') {
                $new_status = 1;
            } else {
                $new_status = 0;
            }
            //echo '$new_status='.$new_status;
            $task_record->status = $new_status;

            if ($task_record->save()) {
                echo true;
            } else {
                echo false;
            }
        } else {
            echo false;
        }
        die;
    }
    public function complete_update_status($task_id = null)
    {

        $task_record = StaffTaskAllocation::where('id', $task_id)
            ->where('home_id', Auth::user()->home_id)
            ->where('is_deleted', '0')
            ->first();

        if (!empty($task_record)) {
            if ($task_record->is_completed == 0) {
                $new_status = 1;
            } else {
                $new_status = 0;
            }
            //echo '$new_status='.$new_status;
            $task_record->is_completed = $new_status;

            if ($task_record->save()) {
                echo true;
            } else {
                echo false;
            }
        } else {
            echo false;
        }
        die;
    }
}
