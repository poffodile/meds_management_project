@extends('frontEnd.layouts.master')
@section('title','System Calendar ')
@section('content')

<?php
if (isset($system_calendar)) {
    $remove = url('/system/calendar/event/remove');
} else {
    $remove = url('/service/calendar/event/remove');
}
?>

<?php //echo "<pre>"; print_r($service_users); die; 
?>
<style type="text/css">
    .ttl-Headr {
        margin-right: 200px;
    }

    .panel-heading>form {
        display: inline-block;
        margin: 0 20px;
    }

    .slctAuto .select-style select {
        width: 150px;
        font-size: 15px;
        line-height: 0;
        padding: 0px 10px 10px 0;
        position: relative;
        top: -3px;
    }

    .slctAuto .select-style {
        margin: 0 40px;
    }

    .cus-calendar .external-event {
        position: relative;
    }

    .external-event .fa {
        color: #fff;
        /* background-color: #fff; */
        /* height: 19px; */
        /* width: 19px; */
        text-align: center;
        /* line-height: 1.4; */
        border-radius: 100%;
        cursor: pointer;
        position: absolute;
        right: 8px;
        top: 25%;
    }

    .calenderScroll {
        height: 500px;
        overflow: unset !important;
    }


    .backIcon i {
        color: #1f88b5;
        font-size: 20px;
    }

    .backIcon {
        text-align: right;
        margin-bottom: 25px;
    }

    .label-mandatory {
        background-color: #7f2222b0;
    }

    .panel {
        padding: 20px;
    }
</style>
<?php //echo 'selected_user_id='.$selected_user_id; die; 
?>
<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-md-12">
                <div class="backIcon"> <a href="{{ url('/') }}"><i class="fa fa-arrow-right" aria-hidden="true"></i></a> </div>
            </div>
        </div>
        <section class="panel cus-calendar">
            <header class="panel-heading slctAuto">
                <!-- All young person's --> <span class="ttl-Headr">Event Calendar</span>
                <!-- <span class="selctSpan"> -->

                <form method="GET" action="{{ url('/system/calendar') }}" id="select_user">
                    <input type="hidden" name="user_type" class="sel_user_type" value="{{ isset($user_type) ? $user_type : '' }}">
                    <div class="row">

                        <div class="col-md-6">
                            <select class="select-style form-control" name="sel_user_type" id="sel_user_type">
                                <option value="ALL">ALL</option>
                                <option value="STAFF" <?php if (isset($user_type)) {
                                                            if ($user_type == 'STAFF') {
                                                                echo "selected";
                                                            }
                                                        } ?>>Staff</option>
                                <option value="SU" <?php if (isset($user_type)) {
                                                        if ($user_type == 'SU') {
                                                            echo "selected";
                                                        }
                                                    } ?>>Child</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <?php //echo 'selected_user_id='.$selected_user_id; die; 
                            ?>
                            @if(!empty($selected_user_id))
                            <select class="sel_users select-style form-control" name="user_id">
                                <option value="All">ALL</option>
                                @foreach($members as $member)
                                <option value="{{ $member['id'] }}" <?php if (isset($selected_user_id)) {
                                                                        if ($selected_user_id == $member['id']) {
                                                                            echo "selected";
                                                                        }
                                                                    } ?>>{{ ucfirst($member['name']) }}</option>
                                @endforeach
                            </select>
                            @else
                            <select class="sel_users select-style form-control" name="user_id">
                                <option>Select User</option>
                            </select>
                            @endif
                        </div>
                    </div>
                </form>
                <!-- </span> -->
                <span class="tools pull-right">
                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                    <!-- <a href="javascript:;" class="fa fa-cog"></a> -->
                    <a href="javascript:;" class="fa fa-times"></a>
                </span>
            </header>
            <div class="panel-body">
                <!-- page start-->
                <div class="row">
                    <aside class="col-lg-9">
                        <div class="outer-cal-part2">
                            <div id="calendar" class="has-toolbar"></div>
                            <div class="col-lg-3 col-md-4 col-sm-3 col-xs-12 p-0">
                                <div class="top-stats-panel">
                                    <ul class="bars" type="none">
                                        <li><span class="bars-pointer label-health"></span> {{ $labels['health_record']['label'] }} </li>
                                        <li><span class="bars-pointer label-incentive"></span> {{ $labels['earning_scheme']['label'] }} </li>
                                        <li><span class="bars-pointer label-sick"></span> Staff Sick Leave</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-3 col-xs-12 p-0">
                                <div class="top-stats-panel">
                                    <ul class="bars" type="none">
                                        <li><span class="bars-pointer label-daily"></span> {{ $labels['daily_record']['label'] }} </li>
                                        <li><span class="bars-pointer label-event"></span> Appointment Plan </li>
                                        <li><span class="bars-pointer label-task"></span> Staff Task Allocation </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-4 col-sm-3 col-xs-12 p-0">
                                <div class="top-stats-panel">
                                    <ul class="bars" type="none">
                                        <li><span class="bars-pointer label-living"></span> {{ $labels['living_skill']['label'] }}</li>
                                        <li><span class="bars-pointer label-note"></span> Note</li>
                                        <li><span class="bars-pointer  label-log-book"></span> Log Book</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-4 col-sm-3 col-xs-12 p-0">
                                <div class="top-stats-panel">
                                    <ul class="bars" type="none">
                                        <li><span class="bars-pointer label-education"></span> {{ $labels['education_record']['label'] }} </li>
                                        <li><span class="bars-pointer label-annual"></span> Staff Annual Leave</li>
                                        <li><span class="bars-pointer label-mandatory"></span> Mandatory Leave</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </aside>
                    <aside class="col-lg-3">
                        <div class="calenderScroll">
                            <h4 class="drg-event-title"> Events need booking in </h4>
                            <div id='external-events'>

                                <!-- Health Record Scroller -->
                                <div class="scroller_hr1 cal_evnt_scroller">
                                    <?php
                                    $pre_exist = 'N';
                                    if (!empty($service_users)) { ?>
                                        <?php
                                        foreach ($service_users as $su_key => $service_user) {
                                            $su_id   = $service_user['id'];
                                            $su_name = $service_user['name'];

                                            if (isset($service_user['health_records'])) {
                                                foreach ($service_user['health_records'] as $key => $value) {
                                                    if (empty($value['calendar_id'])) {
                                                        $pre_exist = 'Y';  ?>
                                                        <div class='external-event label label-health' event_id="{{ $value['health_record_id'] }}" event_type="{{ $value['event_type'] }}" su_id="{{ $service_user['id'] }}" rec_type='health_record'>{{ $su_name }}
                                                            <span class="fa fa-close pull-right del_hel_rec dele"></span>
                                                        </div>

                                    <?php }
                                                }
                                            }
                                        }
                                    } ?>
                                </div>

                                <?php if ($pre_exist == 'Y') { ?>
                                    <div class="bordr-div"></div>
                                <?php }
                                $pre_exist = 'N'; ?>

                                <!-- Daily Record Scroller -->
                                <div class="scroller_dr1 cal_evnt_scroller">
                                    <?php
                                    $pre_exist = 'N';
                                    if (!empty($service_users)) {
                                        foreach ($service_users as $su_key => $service_user) {
                                            $su_id   = $service_user['id'];
                                            $su_name = $service_user['name'];

                                            if (isset($service_user['daily_records'])) {
                                                foreach ($service_user['daily_records'] as $key => $value) {
                                                    if (empty($value['calendar_id'])) {
                                                        $pre_exist = 'Y';  ?>
                                                        <div class='external-event label label-daily del_daily_rec' event_id="{{ $value['su_daily_record_id'] }}" event_type="{{ $value['event_type'] }}" su_id="{{ $service_user['id'] }}" rec_type='daily_records'>{{ $su_name.' - '.$value['description'] }}
                                                            <span class="fa fa-close pull-right del_daily_rec dele"></span>
                                                        </div>

                                    <?php }
                                                }
                                            }
                                        }
                                    } ?>

                                </div>

                                <?php if ($pre_exist == 'Y') { ?>
                                    <div class="bordr-div"></div>
                                <?php }
                                $pre_exist = 'N'; ?>

                                <!-- Living Skill Scroller -->
                                <div class="scroller_ls1 cal_evnt_scroller">
                                    <?php
                                    $pre_exist = 'N';
                                    if (!empty($service_users)) {
                                        foreach ($service_users as $su_key => $service_user) {
                                            $su_id   = $service_user['id'];
                                            $su_name = $service_user['name'];

                                            if (isset($service_user['living_skills'])) {
                                                foreach ($service_user['living_skills'] as $key => $value) {
                                                    if (empty($value['calendar_id'])) {
                                                        $pre_exist = 'Y';  ?>
                                                        <div class='external-event label label-living del_skill_rec' event_id="{{ $value['su_living_skill_id'] }}" event_type="{{ $value['event_type'] }}" su_id="{{ $service_user['id'] }}" rec_type='living_skills'>{{ $su_name.' - '.$value['description'] }}
                                                            <span class="fa fa-close pull-right del_skill_rec dele"></span>
                                                        </div>

                                    <?php }
                                                }
                                            }
                                        }
                                    } ?>

                                </div>

                                <?php if ($pre_exist == 'Y') { ?>
                                    <div class="bordr-div"></div>
                                <?php }
                                $pre_exist = 'N'; ?>

                                <!-- Education Record Scroller -->
                                <div class="scroller_edu1 cal_evnt_scroller">
                                    <?php
                                    $pre_exist = 'N';
                                    if (!empty($service_users)) {
                                        foreach ($service_users as $su_key => $service_user) {
                                            $su_id   = $service_user['id'];
                                            $su_name = $service_user['name'];

                                            if (isset($service_user['education_records'])) {
                                                foreach ($service_user['education_records'] as $key => $value) {
                                                    if (empty($value['calendar_id'])) {
                                                        $pre_exist = 'Y'; ?>
                                                        <div class='external-event label label-education del_edu_rec' event_id="{{ $value['su_education_record_id'] }}" event_type="{{ $value['event_type'] }}" su_id="{{ $service_user['id'] }}" rec_type='education_records'>{{ $su_name.' - '.$value['description'] }}
                                                            <span class="fa fa-close pull-right del_edu_rec dele"></span>
                                                        </div>

                                    <?php }
                                                }
                                            }
                                        }
                                    } ?>

                                </div>

                                <?php if ($pre_exist == 'Y') { ?>
                                    <div class="bordr-div"></div>
                                <?php }
                                $pre_exist = 'N'; ?>

                                <!-- Earning Scheme Scroller -->
                                <div class="scroller_ern1 cal_evnt_scroller">
                                    <?php
                                    $pre_exist = 'N';
                                    if (!empty($service_users)) {
                                        foreach ($service_users as $su_key => $service_user) {

                                            $su_name = $service_user['name'];
                                            if (isset($service_user['earn_incentives'])) {

                                                foreach ($service_user['earn_incentives'] as $key => $value) {
                                                    if (empty($value['calendar_id'])) {
                                                        $pre_exist = 'Y'; ?>

                                                        <div class="external-event label label-incentive del_incentive_rec" event_id="{{ $value['su_ern_inc_id'] }}" event_type="{{ $value['event_type'] }}" su_id="{{ $service_user['id'] }}" rec_type='earn_incentives'>{{ $su_name.' - '.$value['name'] }}
                                                            <span class="fa fa-close pull-right del_incentive_rec dele"></span>
                                                        </div>
                                    <?php  }
                                                }
                                            }
                                        }
                                    }  ?>
                                </div>

                                <?php if ($pre_exist == 'Y') { ?>
                                    <div class="bordr-div"></div>
                                <?php }
                                $pre_exist = 'N'; ?>

                                <!-- Calendar Event Scroller -->
                                <div class="scroller_cal_evnt1 cal_evnt_scroller m-b-5">
                                    <?php
                                    $pre_exist = 'N';
                                    if (!empty($service_users)) {
                                        foreach ($service_users as $su_key => $service_user) {
                                            $su_name = $service_user['name'];
                                            if (isset($service_user['event_records'])) {
                                                foreach ($service_user['event_records'] as $key => $value) {
                                                    if (empty($value['calendar_id'])) {
                                                        $pre_exist = 'Y'; ?>
                                                        <div class='external-event label label-event del_event_rec' event_id="{{ $value['su_calendar_event_id'] }}" event_type="{{ $value['event_type'] }}" su_id="{{ $service_user['id'] }}" rec_type='event_records'>{{ $su_name.' - '.$value['title'] }}
                                                            <span class="fa fa-close pull-right del_event_rec dele"></span>
                                                        </div>
                                    <?php }
                                                }
                                            }
                                        }
                                    } ?>
                                </div>
                                <!-- external-events div close -->
                            </div>
                            @if(!empty($service_users))
                            <div class="form-group col-md-8 col-sm-8 col-xs-8 p-0" data-toggle="modal" href="#calndraddEntryModal">
                                <div class="input-group popovr">
                                    <div class='add-event label label-event'> Add Events</div>
                                    <span class="input-group-addon cus-inpt-grp-addon color-green">
                                        <i class="fa fa-plus"></i>
                                    </span>
                                </div>
                            </div>
                            @endif
                            <?php if ($pre_exist == 'Y') { ?>
                                <div class="bordr-div"></div>
                            <?php }
                            $pre_exist = 'N'; ?>

                            <div id="external-events">

                                <!-- Note Scroller -->
                                <div class="scroller_note1 cal_evnt_scroller m-b-5">
                                    <?php
                                    $pre_exist = 'N';
                                    if (!empty($service_users)) {
                                        foreach ($service_users as $su_key => $service_user) {
                                            $su_name = $service_user['name'];
                                            if (isset($service_user['calender_notes'])) {
                                                foreach ($service_user['calender_notes'] as $key => $value) {
                                                    if (empty($value['calendar_id'])) {
                                                        $pre_exist = 'Y'; ?>
                                                        <div class='external-event label label-note del_cal_rec' event_id="{{ $value['id'] }}" event_type="{{ $value['event_type'] }}" su_id="{{ $service_user['id'] }}" rec_type='calender_notes'> {{ $su_name.' - '.$value['note_title'] }}
                                                            <span class="fa fa-close pull-right del_cal_rec dele"></span>
                                                        </div>
                                    <?php }
                                                }
                                            }
                                        }
                                    } ?>
                                </div>
                            </div>
                            @if(!empty($service_users))
                            <div class="form-group col-md-8 col-sm-8 col-xs-8 p-0" data-toggle="modal" href="#calndraddNoteModal">
                                <div class="input-group popovr">
                                    <div class='add-event label label-note'> Add Notes </div>
                                    <span class="input-group-addon cus-inpt-grp-addon color-green">
                                        <i class="fa fa-plus"></i>
                                    </span>
                                </div>
                            </div>


                            <div class="bordr-div"></div>
                            @endif
                            <!-- Staff Annual Leave -->
                            <div id="external-events">
                                <!-- Staff Annual Leave Scroller -->
                                <div class="scroller_annual_leave1 cal_evnt_scroller">
                                    <?php
                                    $pre_exist = 'N';
                                    if (!empty($staff['annual_leaves'])) {
                                        foreach ($staff['annual_leaves'] as $key => $annual_leave) {
                                            $user_name = $annual_leave['staff_name'];
                                            if (empty($annual_leave['calendar_id'])) {
                                                $pre_exist = 'Y'; ?>
                                                <div class='external-event label label-annual del_annual_lev_rec' event_id="{{ $annual_leave['id'] }}" event_type="{{ $annual_leave['event_type'] }}" su_id="{{ $annual_leave['staff_member_id'] }}" rec_type="annual_leaves"> {{ $user_name.' - '.$annual_leave['annual_title'] }}
                                                    <span class="fa fa-close pull-right del_annual_lev_rec dele"></span>
                                                </div>
                                    <?php }
                                        }
                                    } ?>
                                </div>
                            </div>
                            <!-- Staff Annual Leave End -->

                            <?php if ($pre_exist == 'Y') { ?>
                                <div class="bordr-div"></div>
                            <?php }
                            $pre_exist = 'N'; ?>

                            <!-- Staff Sick Leave -->
                            <div id="external-events">
                                <!-- Staff Annual Leave Scroller -->
                                <div class="scroller_sick_leave1 cal_evnt_scroller">
                                    <?php
                                    $pre_exist = 'N';
                                    if (!empty($staff['sick_leaves'])) {
                                        foreach ($staff['sick_leaves'] as $key => $sick_leave) {
                                            $user_name = $sick_leave['staff_name'];
                                            if (empty($sick_leave['calendar_id'])) {
                                                $pre_exist = 'Y'; ?>
                                                <div class='external-event label label-sick del_sick_lev_rec' event_id="{{ $sick_leave['id'] }}" event_type="{{ $sick_leave['event_type'] }}" su_id="{{ $sick_leave['staff_member_id'] }}" rec_type="sick_leaves"> {{ $user_name.' - '.$sick_leave['sick_title'] }}
                                                    <span class="fa fa-close pull-right del_sick_lev_rec dele"></span>
                                                </div>
                                    <?php }
                                        }
                                    } ?>
                                </div>
                            </div>
                            <!-- Staff Sick Leave End -->

                            <?php if ($pre_exist == 'Y') { ?>
                                <div class="bordr-div"></div>
                            <?php }
                            $pre_exist = 'N'; ?>

                            <!-- Staff Task Allocation -->
                            <div id="external-events">
                                <!-- Staff Task Allocation Scroller -->
                                <div class="scroller_task_alloc1 cal_evnt_scroller">
                                    <?php
                                    $pre_exist = 'N';
                                    if (!empty($staff['task_allocs'])) {
                                        foreach ($staff['task_allocs'] as $key => $task_alloc) {
                                            $user_name = $task_alloc['staff_name'];
                                            if (empty($task_alloc['calendar_id'])) {
                                                $pre_exist = 'Y'; ?>
                                                <div class='external-event label label-task del_task_rec' event_id="{{ $task_alloc['id'] }}" event_type="{{ $task_alloc['event_type'] }}" su_id="{{ $task_alloc['staff_member_id'] }}" rec_type="task_allocs"> {{ $user_name.' - '.$task_alloc['task_title'] }}
                                                    <span class="fa fa-close pull-right del_task_rec dele"></span>
                                                </div>
                                    <?php }
                                        }
                                    } ?>
                                </div>
                            </div>
                            <!-- Staff Task Allocation -->
                            <!-- <div class="bordr-div"></div> -->
                            <div id="external-events">
                                <!-- Staff Task Allocation Scroller -->
                                <div class="scroller_log_book1 cal_evnt_scroller">
                                    <?php
                                    $pre_exist = 'N';
                                    if (!empty($log['log_book'])) {
                                        foreach ($log['log_book'] as $key => $log_book) {
                                            // $user_name = $log_book['staff_name'];
                                            if (empty($log_book['calendar_id'])) {
                                                $pre_exist = 'Y'; ?>
                                                <div class='external-event label label-log-book del_log_bok_rec' event_id="{{ $log_book['id'] }}" event_type="{{ $log_book['event_type'] }}" su_id="0" rec_type="log_book"> {{ $log['title'] ?? "" }}
                                                    <span class="fa fa-close pull-right del_log_bok_rec dele"></span>
                                                </div>
                                    <?php }
                                        }
                                    } ?>
                                </div>
                            </div>

                            <!-- Mandatory Leave Scroller -->
                            <div id="external-events">
                                <div class="scroller_cal_evnt1 cal_evnt_scroller m-b-5">
                                    <?php
                                    $pre_exist = 'N';
                                    if (!empty($service_users)) {
                                        // print_r($ml_record);
                                        if (isset($ml_record)) {
                                            foreach ($ml_record as  $value) {
                                                if (empty($value['calendar_id'])) {
                                                    $pre_exist = 'Y';
                                                    $su_name = App\ServiceUser::where('id', $value['su_id'])->where('home_id', Auth::user()->home_id)->value('name');
                                    ?>
                                                    <div class='external-event label label-mandatory del_event_rec' event_id="{{ $value['mandatory_leaves_id'] }}" event_type="{{ $value['event_type'] }}" su_id="{{ $value['su_id'] }}" rec_type='mandatory_leave'>{{ $value['title'] }}
                                                        <span class="fa fa-close pull-right del_mandatory_leave_rec dele"></span>
                                                    </div>
                                    <?php   }
                                            }
                                        }
                                    } ?>
                                </div>
                            </div>
                            <!-- external-events div close -->

                            @if(!empty($service_users))
                            <div class="form-group col-md-8 col-sm-8 col-xs-8 p-0" data-toggle="modal" href="#calndrAddMandatoryLeave">
                                <div class="input-group popovr">
                                    <div class='add-event label label-mandatory'> Add Mandatory Leave </div>
                                    <span class="input-group-addon cus-inpt-grp-addon color-green">
                                        <i class="fa fa-plus"></i>
                                    </span>
                                </div>
                            </div>
                            @endif
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        </div>
                    </aside>
                </div>
                <!-- page end-->
            </div>
        </section>
        <!-- page end-->
    </section>
</section>
<!--  -->


@if(!empty($service_users))
@include('frontEnd.serviceUserManagement.elements.calender_add_entry')
@include('frontEnd.serviceUserManagement.elements.calender_mandatory_leave')
@include('frontEnd.serviceUserManagement.elements.calender_add_notes')
@endif
<?php //echo "333333333"; die; 
?>
@include('frontEnd.serviceUserManagement.elements.calender_su_detail')
<script>
    //scroller 
    /*event_count = $('.scroller_hr .external-event').length;
    if(event_count > 4){
        $('.scroller_hr').slimScroll({height:'140px'});            
    }

    event_count = $('.scroller_dr .external-event').length;
    if(event_count > 4){
        $('.scroller_dr').slimScroll({height:'140px'});            
    }

    event_count = $('.scroller_ls .external-event').length;
    if(event_count > 4){
        $('.scroller_ls').slimScroll({height:'140px'});            
    }
    event_count = $('.scroller_edu .external-event').length;
    if(event_count > 4){
        $('.scroller_edu').slimScroll({height:'140px'});            
    }
    
   
    event_count = $('.scroller_ern .external-event').length;
    if(event_count > 4){
        $('.scroller_ern').slimScroll({height:'140px'});            
    }

    event_count = $('.scroller_cal_evnt .external-event').length;
    if(event_count > 4){
        $('.scroller_cal_evnt').slimScroll({height:'140px'});            
    }

    event_count = $('.scroller_note .external-event').length;
    if(event_count > 4){
        $('.scroller_note').slimScroll({height:'140px'});            
    }
    event_count = $('.scroller_annual_leave .external-event').length;
    if(event_count > 4){
        $('.scroller_annual_leave').slimScroll({height:'140px'});            
    }
    event_count = $('.scroller_sick_leave .external-event').length;
    if(event_count > 4){
        $('.scroller_sick_leave').slimScroll({height:'140px'});            
    }
    event_count = $('.scroller_task_alloc .external-event').length;
    if(event_count > 4){
        $('.scroller_task_alloc').slimScroll({height:'140px'});            
    }
    event_count = $('.scroller_log_book .external-event').length;
    if(event_count > 4){
        $('.scroller_log_book').slimScroll({height:'140px'});            
    }*/
</script>

<script>
    autosize($("textarea"));

    /* initialize the external events
     -----------------------------------------------------------------*/

    $('#external-events div.external-event').each(function() {

        // create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
        // it doesn't need to have a start or end
        var eventObject = {
            title: $.trim($(this).text()), // use the element's text as the event title
            color: $(this).css("background-color"),
            event_id: $(this).attr('event_id'),
            event_type: $(this).attr('event_type'),
            su_id: $(this).attr('su_id'),
            //calender_id: 0
        };
        //alert(eventObject.id); //return false;
        // store the Event Object in the DOM element so we can get to it later
        var object = $(this).data('eventObject', eventObject);
        //alert(object); return false;

        // make the event draggable using jQuery UI
        $(this).draggable({
            /*containment: "window",*/
            revert: true,
            /*scroll: false,*/
            zIndex: 999999,
            helper: 'clone',
            revertDuration: 0,
            /*zIndex: 999,
            revert: true,      // will cause the event to go back to its
            revertDuration: 0  //  original position after the drag*/
        });

    });

    /* initialize the calendar
     -----------------------------------------------------------------*/
    var date = new Date();
    var d = date.getDate();
    var m = date.getMonth();
    var y = date.getFullYear();

    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,basicWeek,basicDay'
        },
        editable: true,
        droppable: true, // this allows things to be dropped onto the calendar !!!
        drop: function(date, allDay) { // this function is called when something is dropped

            $('.loader').show();
            // retrieve the dropped element's stored Event Object
            var originalEventObject = $(this).data('eventObject');
            var selected_event = $(this);
            console.log(originalEventObject);

            var event_type = originalEventObject.event_type;
            var event_id = originalEventObject.event_id;
            var service_user_id = originalEventObject.su_id;
            var token = "{{ csrf_token() }}";
            var event_year = date.getFullYear();
            var event_month = date.getMonth() + 1;
            var event_day = date.getDate();
            var event_date = event_year + '-' + event_month + '-' + event_day;

            $.ajax({
                type: 'post',
                url: "{{ url('/system/calendar/event/add') }}",
                data: {
                    'service_user_id': service_user_id,
                    'event_type': event_type,
                    'event_id': event_id,
                    'event_date': event_date,
                    '_token': token
                },
                dataType: 'json',
                success: function(resp) {
                    var response = resp['response'];
                    var calendar_id = resp['calendar_id'];

                    if (response == true) {

                        if (isAuthenticated(response) == false) {
                            return false;
                        }

                        // we need to copy it, so that multiple events don't have a reference to the same object
                        var copiedEventObject = $.extend({}, originalEventObject);

                        // assign it the date that was reported
                        copiedEventObject.start = date;
                        copiedEventObject.allDay = allDay;
                        copiedEventObject.calendar_id = calendar_id;

                        // render the event on the calendar
                        // the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
                        $('#calendar').fullCalendar('renderEvent', copiedEventObject, true);

                        // is the "remove after drop" checkbox checked?
                        //if ($('#drop-remove').is(':checked')) {
                        // if so, remove the element from the "Draggable Events" list
                        selected_event.remove();
                        //}
                        $('.loader').hide();
                    } else {
                        $('.loader').hide();
                        alert('Sorry, You are not authorized to drop this event.');
                    }
                }
            });
            return false;
        },

        eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc) {
            //console.log(event);
            // console.log(event.start);
            // console.log(event.start.getDate());
            //alert(event.title + " was dropped on " + event.start.format());
            //alert(event.title + " was dropped on " + event.start);

            if (confirm("Are you sure about this change?")) {
                $('.loader').show();
                var event_calendar_id = event.calendar_id;
                var token = "{{ csrf_token() }}";
                var event_year = event.start.getFullYear();
                var event_month = event.start.getMonth() + 1;
                var event_day = event.start.getDate();
                var event_date = event_year + '-' + event_month + '-' + event_day;
                $.ajax({
                    type: 'post',
                    url: "{{ url('/system/calendar/event/move') }}",
                    data: {
                        'event_calendar_id': event_calendar_id,
                        '_token': token,
                        'event_date': event_date
                    },
                    success: function(resp) {
                        // {   if(isAuthenticated(resp) == false){
                        //         return false;
                        //     }
                        if (resp == 'false') {
                            revertFunc();
                            $('.ajax-alert-err').show();
                            $('.ajax-alert-err .msg').text("{{ COMMON_ERROR }}");
                            setTimeout(function() {
                                $(".notification-box").fadeOut()
                            }, 5000);
                        }
                        $('.loader').hide();
                    }
                });
            } else {
                revertFunc();
            }
        },
        events: [


            <?php
            if (!empty($service_users)) {
                foreach ($service_users as $su_key => $service_user) {
                    $su_name = $service_user['name'];
                    if (isset($service_user['health_records'])) {
                        foreach ($service_user['health_records'] as $key => $value) {
                            if (!empty($value['calendar_id'])) {
                                $event_day   = date('d', strtotime($value['event_date']));
                                $event_mon   = date('m', strtotime($value['event_date'])) - 1;
                                $event_year  = date('Y', strtotime($value['event_date']));

                                $event_id    = $value['health_record_id'];
                                $event_type  = $value['event_type'];
                                $calendar_id = $value['calendar_id'];
            ?> {
                                    title: '{{ $su_name.' - '.$value["title"] }}',
                                    start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day }}'),
                                    color: '#1fb5ad',
                                    event_id: '{{ $event_id }}',
                                    event_type: '{{ $event_type }}',
                                    calendar_id: '{{ $calendar_id }}'
                                },

            <?php }
                        }
                    }
                }
            } ?>


            <?php
            if (!empty($service_users)) {
                foreach ($service_users as $su_key => $service_user) {
                    $su_name = $service_user['name'];
                    if (isset($service_user['daily_records'])) {
                        foreach ($service_user['daily_records'] as $key => $value) {
                            if (!empty($value['calendar_id'])) {
                                $event_day   = date('d', strtotime($value['event_date']));
                                $event_mon   = date('m', strtotime($value['event_date'])) - 1;
                                $event_year  = date('Y', strtotime($value['event_date']));

                                $event_id    = $value['su_daily_record_id'];
                                $event_type  = $value['event_type'];
                                $calendar_id = $value['calendar_id'];
            ?> {
                                    title: '{{ $su_name.' - '.$value["description"] }}',
                                    start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day}}'),
                                    color: '#99CBE2',
                                    event_id: '{{ $value["su_daily_record_id"] }}',
                                    event_type: '{{ $event_type }}',
                                    calendar_id: '{{ $calendar_id}}'
                                },
            <?php }
                        }
                    }
                }
            } ?>

            <?php
            if (!empty($service_users)) {
                foreach ($service_users as $su_key => $service_user) {
                    $su_name = $service_user['name'];
                    if (isset($service_user['living_skills'])) {
                        foreach ($service_user['living_skills'] as $key => $value) {
                            if (!empty($value['calendar_id'])) {
                                $event_day   = date('d', strtotime($value['event_date']));
                                $event_mon   = date('m', strtotime($value['event_date'])) - 1;
                                $event_year  = date('Y', strtotime($value['event_date']));

                                $event_id    = $value['su_living_skill_id'];
                                $event_type  = $value['event_type'];
                                $calendar_id = $value['calendar_id'];
            ?> {
                                    title: '{{ $su_name.' - '.$value["description"] }}',
                                    start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day}}'),
                                    color: '#A9D86E',
                                    event_id: '{{ $value["su_living_skill_id"] }}',
                                    event_type: '{{ $event_type }}',
                                    calendar_id: '{{ $calendar_id}}'
                                },
            <?php }
                        }
                    }
                }
            } ?>

            <?php
            if (!empty($service_users)) {
                foreach ($service_users as $su_key => $service_user) {
                    $su_name = $service_user['name'];
                    if (isset($service_user['education_records'])) {
                        foreach ($service_user['education_records'] as $key => $value) {
                            if (!empty($value['calendar_id'])) {
                                $event_day   = date('d', strtotime($value['event_date']));
                                $event_mon   = date('m', strtotime($value['event_date'])) - 1;
                                $event_year  = date('Y', strtotime($value['event_date']));

                                $event_id    = $value['su_education_record_id'];
                                $event_type  = $value['event_type'];
                                $calendar_id = $value['calendar_id'];
            ?> {
                                    title: '{{ $su_name.' - '.$value["description"] }}',
                                    start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day}}'),
                                    color: '#344860',
                                    event_id: '{{ $value["su_education_record_id"] }}',
                                    event_type: '{{ $event_type }}',
                                    calendar_id: '{{ $calendar_id}}'
                                },
            <?php }
                        }
                    }
                }
            } ?>

            <?php
            if (!empty($service_users)) {
                foreach ($service_users as $su_key => $service_user) {
                    $su_name = $service_user['name'];
                    if (isset($service_user['earn_incentives'])) {
                        foreach ($service_user['earn_incentives'] as $key => $value) {
                            if (!empty($value['calendar_id'])) {
                                $event_day   = date('d', strtotime($value['event_date']));
                                $event_mon   = date('m', strtotime($value['event_date'])) - 1;
                                $event_year  = date('Y', strtotime($value['event_date']));

                                $event_id    = $value['su_ern_inc_id'];
                                $event_type  = $value['event_type'];
                                $calendar_id = $value['calendar_id'];
            ?> {
                                    title: '{{ $su_name.' - '.$value["name"] }}',
                                    start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day}}'),
                                    color: '#9566B6',
                                    event_id: '{{ $value["su_ern_inc_id"] }}',
                                    event_type: '{{ $event_type }}',
                                    calendar_id: '{{ $calendar_id}}'
                                },
            <?php }
                        }
                    }
                }
            } ?>

            <?php
            if (!empty($service_users)) {
                foreach ($service_users as $key => $service_user) {
                    $su_name = $service_user['name'];
                    if (isset($service_user['event_records'])) {
                        foreach ($service_user['event_records'] as $key => $value) {
                            if (!empty($value['calendar_id'])) {
                                $event_day  = date('d', strtotime($value['event_date']));
                                $event_mon  = date('m', strtotime($value['event_date'])) - 1;
                                $event_year = date('Y', strtotime($value['event_date']));

                                $event_id   = $value['su_calendar_event_id'];
                                $event_type = $value['event_type'];
                                $calendar_id = $value['calendar_id'];
            ?> {
                                    title: '{{ $su_name.' - '.$value["title"] }}',
                                    start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day }}'),
                                    color: '#3398CC',
                                    event_id: '{{ $value["su_calendar_event_id"] }}',
                                    event_type: '{{ $event_type }}',
                                    calendar_id: '{{ $calendar_id }}'
                                },
            <?php }
                        }
                    }
                }
            } ?>

            <?php
            if (!empty($service_users)) {
                foreach ($service_users as $su_key => $service_user) {
                    $su_name = $service_user['name'];
                    if (isset($service_user['calender_notes'])) {
                        foreach ($service_user['calender_notes'] as $key => $value) {
                            if (!empty($value['calendar_id'])) {
                                $event_day  = date('d', strtotime($value['event_date']));
                                $event_mon  = date('m', strtotime($value['event_date'])) - 1;
                                $event_year = date('Y', strtotime($value['event_date']));

                                $event_id   = $value['id'];
                                $event_type = $value['event_type'];
                                $calendar_id = $value['calendar_id'];
            ?> {
                                    title: '{{ $su_name.' - '.$value["note_title"] }}',
                                    start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day }}'),
                                    color: '#999999',
                                    event_id: '{{ $value["id"] }}',
                                    event_type: '{{ $event_type }}',
                                    calendar_id: '{{ $calendar_id }}'
                                },
            <?php }
                        }
                    }
                }
            } ?>

            <?php
            if (!empty($staff['annual_leaves'])) {
                foreach ($staff['annual_leaves'] as $key => $annual_leave) {
                    $user_name = $annual_leave['staff_name'];
                    if (!empty($annual_leave['calendar_id'])) {
                        $event_day  = date('d', strtotime($annual_leave['event_date']));
                        $event_mon  = date('m', strtotime($annual_leave['event_date'])) - 1;
                        $event_year = date('Y', strtotime($annual_leave['event_date']));

                        $event_id   = $annual_leave['id'];
                        $event_type = $annual_leave['event_type'];
                        $calendar_id = $annual_leave['calendar_id'];
            ?> {
                            title: '{{ $user_name.' - '.$annual_leave["annual_title"] }}',
                            start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day }}'),
                            color: '#FA8564',
                            event_id: '{{ $annual_leave["id"] }}',
                            event_type: '{{ $event_type }}',
                            calendar_id: '{{ $calendar_id }}'
                        },
            <?php }
                }
            } ?>

            <?php
            if (!empty($staff['sick_leaves'])) { //$test = array();
                foreach ($staff['sick_leaves'] as $key => $sick_leave) { //$test[] = $staff_sick_leaves;
                    $user_name = $sick_leave['staff_name'];
                    if (!empty($sick_leave['calendar_id'])) {

                        $event_day  = date('d', strtotime($sick_leave['event_date']));
                        $event_mon  = date('m', strtotime($sick_leave['event_date'])) - 1;
                        $event_year = date('Y', strtotime($sick_leave['event_date']));

                        $event_id   = $sick_leave['id'];
                        $event_type = $sick_leave['event_type'];
                        $calendar_id = $sick_leave['calendar_id'];
            ?> {
                            title: '{{ $user_name.' - '.$sick_leave["sick_title"] }}',
                            start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day }}'),
                            color: '#B43426',
                            event_id: '{{ $sick_leave["id"] }}',
                            event_type: '{{ $event_type }}',
                            calendar_id: '{{ $calendar_id }}'
                        },
            <?php }
                }
            } ?>

            <?php
            if (!empty($staff['task_allocs'])) {
                foreach ($staff['task_allocs'] as $key => $task_alloc) {
                    $user_name = $task_alloc['staff_name'];
                    if (!empty($task_alloc['calendar_id'])) {

                        $event_day  = date('d', strtotime($task_alloc['event_date']));
                        $event_mon  = date('m', strtotime($task_alloc['event_date'])) - 1;
                        $event_year = date('Y', strtotime($task_alloc['event_date']));

                        $event_id   = $task_alloc['id'];
                        $event_type = $task_alloc['event_type'];
                        $calendar_id = $task_alloc['calendar_id'];
            ?> {
                            title: '{{ $user_name.' - '.$task_alloc["task_title"] }}',
                            start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day }}'),
                            color: '#FF9900',
                            event_id: '{{ $task_alloc["id"] }}',
                            event_type: '{{ $event_type }}',
                            calendar_id: '{{ $calendar_id }}'
                        },
            <?php }
                }
            } ?>



            <?php
            if (!empty($service_users)) {
                if (isset($ml_record)) {
                    foreach ($ml_record as  $value) {
                        if (!empty($value['calendar_id'])) {
                            $event_day  = date('d', strtotime($value['event_date']));
                            $event_mon  = date('m', strtotime($value['event_date'])) - 1;
                            $event_year = date('Y', strtotime($value['event_date']));
                            $service_user = App\ServiceUser::where('id', $value['su_id'])->where('home_id', Auth::user()->home_id)->value('name');
                            $event_id   = $value['mandatory_leaves_id'];
                            $event_type = $value['event_type'];
                            $calendar_id = $value['calendar_id'];
            ?> {
                                title: '{{ $service_user.' - '.$value["title"] }}',
                                start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day }}'),
                                color: '#7f2222b0',
                                event_id: '{{ $value["mandatory_leaves_id"] }}',
                                event_type: '{{ $event_type }}',
                                calendar_id: '{{ $calendar_id }}'
                            },
            <?php                         }
                    }
                }
            } ?>


            <?php
            if (!empty($log['log_book'])) {
                foreach ($log['log_book'] as $key => $logbook) {
                    if (!empty($logbook['calendar_id'])) {
                        $event_day  = date('d', strtotime($logbook['event_date']));
                        $event_mon  = date('m', strtotime($logbook['event_date'])) - 1;
                        $event_year = date('Y', strtotime($logbook['event_date']));

                        $event_id   = $logbook['id'];
                        $event_type = $logbook['event_type'];
                        $calendar_id = $logbook['calendar_id'];
            ?> {
                            title: '{{ $logbook["title"] }}',
                            start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day }}'),
                            color: '#0275d8',
                            event_id: '{{ $logbook["id"] }}',
                            event_type: '{{ $event_type }}',
                            calendar_id: '{{ $calendar_id }}'
                        },
            <?php }
                }
            } ?>

        ],

        eventClick: function(event) {

            //console.log(event);
            var event_calendar_id = event.calendar_id;
            var event_type = event.event_type;
            var event_id = event.event_id;
            var remove_url = "{{ $remove }}" + '/' + event_calendar_id;

            $('.loader').show();
            $('body').addClass('body-overflow');
            $.ajax({
                type: 'get',
                url: "{{ url('/system/calendar/event/view') }}",
                data: {
                    'event_type': event_type,
                    'event_id': event_id,
                    'calendar_id': event_calendar_id
                },
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }

                    $('.calendar-event-details').html(resp);
                    $('.remove-cal-event').attr('href', remove_url);
                    $('#calndrViewEvent').modal('show');

                    setTimeout(function() {
                        autosize($("textarea"));
                    }, 200);

                    $('.edit_cal_event').removeClass('active');

                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;

            /*if (event.url) {
                window.open(event.url);
                return false;
            }*/
        }
    });
</script>

<script>
    $(document).on('change', '.sel_users', function() {
        $('#select_user').submit();
    });

    $(document).ready(function() {
        $(document).on('change', '#sel_user_type', function() {

            var user_type = $(this).val();
            console.log(user_type); //return false;

            if (user_type == 'ALL') {
                window.location.href = "{{ url('/system/calendar') }}";
            }

            var _token = "{{ csrf_token() }}";
            $('.loader').show();
            $.ajax({
                method: 'post',
                url: "{{ url('/system/calendar/select/member') }}",
                data: {
                    'user_type': user_type,
                    '_token': _token
                },
                success: function(resp) {
                    console.log(resp);
                    $('.sel_users').html(resp);
                    $('.sel_user_type').val(user_type);
                    $('.loader').hide();
                }
            });
        });
    });
</script>

<script type="text/javascript">
    $(document).on('click', '.del_hel_rec, .del_daily_rec, .del_skill_rec, .del_edu_rec, .del_incentive_rec, .del_event_rec, .del_cal_rec, .del_annual_lev_rec, .del_sick_lev_rec, .del_task_rec, .del_log_bok_rec, .del_mandatory_leave_rec', function() {
        var ths = $(this);
        var length = $(ths).parent().length;

        // console.log(length);
        var record_type = $(this).parent().attr('rec_type');
        // console.log(record_type);
        var event_id = $(this).parent().attr('event_id');
        // console.log(event_id); 
        var _token = "{{ csrf_token()}}";
        $.ajax({
            type: 'post',
            url: "{{ url('/delete/calendar/event') }}" + '/' + event_id,
            data: {
                'record_type': record_type,
                '_token': _token
            },
            success: function(resp) {
                // console.log(resp);
                if (resp == '1') {
                    var leng = Number(length) - 1;
                    // console.log(l);
                    if (leng == '0') {
                        $(ths).closest('.cal_evnt_scroller').next('.bordr-div').remove();
                    }
                    $(ths).parent().remove();

                }
            }
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {

        $(".dele").on("click", function() {
            return confirm("Do you want to delete it ?");
        });

    });
</script>

<!--
Note: here in calender month is one number in minus
 -->

@endsection