@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Staff Timesheet')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
<style>
    .approve {
        border-radius: 17px !important;
        width: 100px;
        height: 25px;
        margin-top: 10px;
        line-height: 10px !important;
    }
</style>
@section('content')


<!--main content start-->
<?php 
$action_url=url('rota-absence?manager='.base64_encode($user_id));
if($user_id_key == 'staff'){
    $action_url=url('rota-absence?staff='.base64_encode($user_id));
}
  function checkLeavType($leave_type){
    if($leave_type == 1){
        return $leave_type = 'Annual leave';
    }else if($leave_type == 2){
        return $leave_type = 'Sickness';
    }else if($leave_type == 3){
        return $leave_type = 'Lateness';
    }else{
        return $leave_type = 'Other';
    }
  }
?>
<section class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 p-0">
                <div class="panel">
                    <header class="panel-heading px-5">
                        <h4>Absence</h4>
                    </header>
                    <div class="panel-body">
                        <div class="absenceFiler">
                            <div>
                                <label>Filter absences</label>
                                <div>
                                    <select id="absenceFilter" class="form-select form-control">
                                        <option value="1">All absences</option>
                                        <option value="2">Annual leave</option>
                                        <option value="3">Lateness</option>
                                        <option value="4">Sickness</option>
                                        <!-- <option value="furloughs">Furloughs</option> -->
                                        <option value="5">Other absences</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label>Leave year</label>
                                <form action="{{$action_url}}" method="post" id="yearForm">
                                    @csrf
                                    <div>
                                        <select class="form-select form-control" id="year" onchange="year_select()" name="year">
                                            <?php foreach($years as $yearVal){?>
                                                <option value="{{$yearVal}}" <?php if($reqyear == $yearVal){echo 'selected';}?>>01 Jan {{$yearVal}} - 31 Dec {{$yearVal}}</option>
                                            <?php }?>
                                        </select>
                                    </div>
                                </form>
                            </div>

                        </div>
                        <div class="allAbsences">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class=" text-center">
                                        <h3>All absences</h3>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="absenceAdd m-t-20">
                                        <label>Annual leave to take</label>
                                        <div class="timeHrsMinuts m-t-20 m-b-20">
                                            <div class="timelist">
                                                <strong><?php echo $renaming_hour;?></strong>
                                                <span>hrs</span>
                                            </div>
                                            <div class="timelist">
                                                <strong><?php echo $renaming_min;?></strong>
                                                <span>mins</span>
                                            </div>
                                            <div class="timelist">
                                                <strong>/</strong>
                                            </div>
                                           
                                            <div class="timelist">
                                                <strong>{{$allowance_hour}}</strong>
                                                <span>hrs</span>
                                            </div>
                                            <div class="timelist">
                                                <strong>{{$allowance_min}}</strong>
                                                <span>mins</span>
                                            </div>
                                        </div>
                                        <!-- <p>(Approx 24 / 32 days) <a href="#!"><i class="fa fa-info-o"></i> </a> </p> -->
                                        <div class="m-t-20">
                                            <a href="{{url('absence/type=1?'.$user_id_key.'='.$user_id)}}" type="button" class="btn btn-warning">Add annual leave</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="absenceAdd borderLeftRight m-t-20">
                                        <label>Sickness</label>
                                        <div class="timeHrsMinuts m-t-20 m-b-20">
                                            <div class="timelist">
                                                <strong id="seekness_occurrences">0</strong>
                                                <span>occurrences</span>
                                            </div>

                                        </div>
                                        <!-- <p>(....?) <a href="#!"><i class="fa fa-info-o"></i> </a> </p> -->
                                        <div class="m-t-20">
                                            <a href="{{url('absence/type=2?'.$user_id_key.'='.$user_id)}}" type="button" class="btn btn-warning">Add</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="absenceAdd m-t-20">
                                        <label>Lateness</label>
                                        <div class="timeHrsMinuts m-t-20 m-b-20">
                                            <div class="timelist">
                                                <strong id="lateness_occurrences">0</strong>
                                                <span>occurrences</span>
                                            </div>

                                        </div>
                                        <!-- <p>(....?) <a href="#!"><i class="fa fa-info-o"></i> </a> </p> -->
                                        <div class="m-t-20">
                                            <a href="{{url('absence/type=3?'.$user_id_key.'='.$user_id)}}" type="button" class="btn btn-warning">Add</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="absenceHistory m-t-40">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3 class="absencehistoryHeading">Absence History</h3>
                                        <div class="absenceAccordion">
                                            <div class="panel-group" id="accordion-absence">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <h4 class="panel-title">
                                                            <a data-toggle="collapse" data-parent="#accordion-absence" href="#collapseOne">Current & future ({{count($current_future)}})</a>
                                                        </h4>
                                                    </div>
                                                    <div id="collapseOne" class="panel-collapse collapse">
                                                        <div class="panel-body">
                                                            <div class="col-md-12">
                                                                <?php 
                                                                $annual_cf=0;
                                                                $seekness_occurrences=0;
                                                                $lateness_occurrences=0;
                                                                $other_cf=0;
                                                                foreach($current_future as $cfVal){
                                                                    $leave_typeAll_cfVal=checkLeavType($cfVal->leave_type);
                                                                    if($cfVal->leave_type == 1){
                                                                        $annual_cf=$annual_cf+1;
                                                                    }if($cfVal->leave_type == 2){
                                                                        $seekness_occurrences=$seekness_occurrences+1;
                                                                    }if($cfVal->leave_type == 3){
                                                                        $lateness_occurrences=$lateness_occurrences+1;
                                                                    }if($cfVal->leave_type == 4){
                                                                        $other_cf=$other_cf+1;
                                                                    }
                                                                ?>
                                                                <div class="row publicHoliday">
                                                                    <div class="col-md-2">
                                                                        <div class="sunIcon">
                                                                            <i class="fa fa-certificate"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-8">
                                                                        <div class="holidayTitle">
                                                                            <h4>{{$leave_typeAll_cfVal}}</h4>
                                                                            <p><strong><?php echo date('D d M', strtotime($cfVal->start_date)) . ' - ' . date('D d M Y',strtotime($cfVal->end_date));?></strong> (0 hrs)</p>
                                                                            <p><b>logged</b> on <?php echo date('D d M Y', strtotime($cfVal->created_at));?> by <?php echo Auth::user()->name; ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="sunIcon">
                                                                            <a href="{{url('absence/type='.$cfVal->leave_type.'?'.$user_id_key.'='.$user_id.'&leave_id='.$cfVal->id)}}"><i class="fa fa-pencil-square-o"></i></a>
                                                                            <a href="{{url('absence/type='.$cfVal->leave_type.'?'.$user_id_key.'='.$user_id.'&leave_id='.$cfVal->id)}}"><i class="fa fa-trash-o"></i></a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <?php }?>
                                                                <input type="hidden" id="seekness_occurrences_value" value="{{$seekness_occurrences}}">
                                                                <input type="hidden" id="lateness_occurrences_value" value="{{$lateness_occurrences}}">
                                                                <!-- <div class="row publicHoliday m-t-15">
                                                                    <div class="col-md-2">
                                                                        <div class="sunIcon">
                                                                            <i class="fa fa-certificate"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-8">
                                                                        <div class="holidayTitle">
                                                                            <h4>Public Holiday</h4>
                                                                            <p><strong>Mon 01 Jan 2018</strong> (7 hrs)</p>
                                                                            <p>New Year's Day</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="sunIcon">
                                                                            <a href="#!"><i class="fa fa-pencil-square-o"></i></a>
                                                                            <a href="#!"><i class="fa fa-trash-o"></i></a>
                                                                        </div>
                                                                    </div>
                                                                </div> -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <h4 class="panel-title">
                                                            <a data-toggle="collapse" data-parent="#accordion-absence" href="#collapseTwo">Absence history ({{count($history)}})</a>
                                                        </h4>
                                                    </div>
                                                    <div id="collapseTwo" class="panel-collapse collapse">
                                                        <div class="panel-body">
                                                            <!-- <div class="row">
                                                                <div class="col-md-12">
                                                                    Shank fatback pastrami turkey ham hock. Pastrami ball tip brisket pig salami kevin tri-tip sausage venison jowl spare ribs short loin pork chop. Shank pork chop burgdoggen shankle flank. Turducken cow salami venison, biltong ham ball tip meatloaf drumstick bacon jowl kielbasa.
                                                                </div>
                                                            </div> -->
                                                            <?php 
                                                            $annual_ah=0;
                                                            $seeckness_ah=0;
                                                            $lateness_ah=0;
                                                            $other_ah=0;
                                                            foreach($history as $all_histroy){
                                                                $leave_typeAll_histroy=checkLeavType($all_histroy->leave_type);
                                                                if($all_histroy->leave_type == 1){
                                                                    $annual_ah=$annual_ah+1;
                                                                }else if($all_histroy->leave_type == 2){
                                                                    $seeckness_ah=$seeckness_ah+1;
                                                                }else if($all_histroy->leave_type == 3){
                                                                    $lateness_ah=$lateness_ah+1;
                                                                }else if($all_histroy->leave_type == 4){
                                                                    $other_ah=$other_ah+1;
                                                                }
                                                                ?>
                                                            <div class="row publicHoliday m-t-15">
                                                                <div class="col-md-2">
                                                                    <div class="sunIcon">
                                                                        <i class="fa fa-certificate"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-8">
                                                                    <div class="holidayTitle">
                                                                        <!-- <h4>Public Holiday</h4> -->
                                                                            <h4>{{$leave_typeAll_histroy}}</h4>
                                                                        <?php if($all_histroy->leave_type == 4){?>
                                                                            <p>Other Leave</p>
                                                                        <?php }?>
                                                                            <p><strong><?php echo date('D d M', strtotime($all_histroy->start_date)) . ' - ' . date('D d M Y',strtotime($all_histroy->end_date));?></strong> (0 hrs)</p>
                                                                            <!-- <p>New Year's Day</p> -->
                                                                            <p><button type="button" class="btn btn-warning approve">APPROVED</button> on <?php echo date('D d M Y', strtotime($all_histroy->created_at));?> by <?php echo Auth::user()->name; ?></p>
                                                                    </div>
                                                                </div>
                                                                <!-- <div class="col-md-2">
                                                                    <div class="sunIcon">
                                                                        <a href="#!"><i class="fa fa-pencil-square-o"></i></a>
                                                                        <a href="#!"><i class="fa fa-trash-o"></i></a>
                                                                    </div>
                                                                </div> -->
                                                            </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- all Absence  -->

                        <div class="annualLeave">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class=" text-center">
                                        <h3>Annual leave</h3>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="absenceAdd borderLeftRight m-t-20">
                                        <label>Remaining</label>
                                        <div class="timeHrsMinuts m-t-20 m-b-20">
                                            <div class="timelist">
                                                <strong><?php echo $renaming_hour;?></strong>
                                                <span>hrs</span>
                                            </div>
                                            <div class="timelist">
                                                <strong><?php echo $renaming_min;?></strong>
                                                <span>mins</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="absenceAdd m-t-20">
                                        <label>Allowance</label>
                                        <div class="timeHrsMinuts m-t-20 m-b-20">
                                            <div class="timelist">
                                                <strong>{{$allowance_hour}}</strong>
                                                <span>hrs</span>
                                            </div>
                                            <div class="timelist">
                                                <strong>{{$allowance_min}}</strong>
                                                <span>mins</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 m-t-20 text-center">
                                    <label>Craig has taken {{$allowance_hour}} hrs of annual leave.</label>
                                    <a href="{{url('absence/type=1?'.$user_id_key.'='.$user_id)}}" type="button" class="btn btn-warning m-t-20">Add annual leave</a>
                                </div>
                            </div>
                            <div class="absenceHistory m-t-40">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3 class="absencehistoryHeading">Absence History</h3>
                                        <div class="absenceAccordion">
                                            <div class="panel-group" id="accordionAbsence">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <h4 class="panel-title">
                                                            <a data-toggle="collapse" data-parent="#accordionAbsence" href="#collapseThree">Current & future ({{$annual_cf}})</a>
                                                        </h4>
                                                    </div>
                                                    <div id="collapseThree" class="panel-collapse collapse">
                                                        <div class="panel-body">
                                                            <div class="col-md-12">
                                                                <?php foreach($current_future as $cfVal){
                                                                    $leave_typeAll_cfVal=checkLeavType($cfVal->leave_type);
                                                                ?>
                                                                <div class="row publicHoliday">
                                                                    <div class="col-md-2">
                                                                        <div class="sunIcon">
                                                                            <i class="fa fa-certificate"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-8">
                                                                        <div class="holidayTitle">
                                                                            <h4>{{$leave_typeAll_cfVal}}</h4>
                                                                            <p><strong><?php echo date('D d M', strtotime($cfVal->start_date)) . ' - ' . date('D d M Y',strtotime($cfVal->end_date));?></strong> (0 hrs)</p>
                                                                            <p><b>logged</b> on <?php echo date('D d M Y', strtotime($cfVal->created_at));?> by <?php echo Auth::user()->name; ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="sunIcon">
                                                                            <a href="#!"><i class="fa fa-pencil-square-o"></i></a>
                                                                            <a href="#!"><i class="fa fa-trash-o"></i></a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <?php }?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <h4 class="panel-title">
                                                            <a data-toggle="collapse" data-parent="#accordionAbsence" href="#collapseFour">Absence history ({{$annual_ah}})</a>
                                                        </h4>
                                                    </div>
                                                    <div id="collapseFour" class="panel-collapse collapse">
                                                        <div class="panel-body">
                                                            <!-- <div class="row">
                                                                <div class="col-md-12">
                                                                    Shank fatback pastrami turkey ham hock. Pastrami ball tip brisket pig salami kevin tri-tip sausage venison jowl spare ribs short loin pork chop. Shank pork chop burgdoggen shankle flank. Turducken cow salami venison, biltong ham ball tip meatloaf drumstick bacon jowl kielbasa.
                                                                </div>
                                                            </div> -->
                                                             <?php 
                                                            foreach($history as $all_histroy){
                                                                $leave_typeAll_histroy=checkLeavType($all_histroy->leave_type);
                                                                ?>
                                                            <div class="row publicHoliday m-t-15">
                                                                <div class="col-md-2">
                                                                    <div class="sunIcon">
                                                                        <i class="fa fa-certificate"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-8">
                                                                    <div class="holidayTitle">
                                                                        <!-- <h4>Public Holiday</h4> -->
                                                                            <h4>{{$leave_typeAll_histroy}}</h4>
                                                                        <?php if($all_histroy->leave_type == 4){?>
                                                                            <p>Other Leave</p>
                                                                        <?php }?>
                                                                            <p><strong><?php echo date('D d M', strtotime($all_histroy->start_date)) . ' - ' . date('D d M Y',strtotime($all_histroy->end_date));?></strong> (0 hrs)</p>
                                                                            <!-- <p>New Year's Day</p> -->
                                                                            <p><button type="button" class="btn btn-warning approve">APPROVED</button> on <?php echo date('D d M Y', strtotime($all_histroy->created_at));?> by <?php echo Auth::user()->name; ?></p>
                                                                    </div>
                                                                </div>
                                                                <!-- <div class="col-md-2">
                                                                    <div class="sunIcon">
                                                                        <a href="#!"><i class="fa fa-pencil-square-o"></i></a>
                                                                        <a href="#!"><i class="fa fa-trash-o"></i></a>
                                                                    </div>
                                                                </div> -->
                                                            </div>
                                                            <?php } ?>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="lateness">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class=" text-center">
                                        <h3>Lateness</h3>
                                    </div>
                                </div>
                                <div class="col-md-6 p-0">
                                    <div class="absenceAdd borderLeftRight m-t-20">
                                        <label>Logged</label>
                                        <div class="timeHrsMinuts m-t-20 m-b-20">
                                            <div class="timelist">
                                                <strong>{{count($lateness)}}</strong>
                                                <span>occurrences</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                $lateness_hour=0;
                                $lateness_min=0;
                                foreach($lateness as $lateVal){
                                    if (!empty($lateVal->late_by)) {
                                        $lateParts = explode('::', $lateVal->late_by);
                                        $lateHour = isset($lateParts[0]) ? (int)$lateParts[0] : 0;
                                        $lateMin = isset($lateParts[1]) ? (int)$lateParts[1] : 0;

                                        $lateness_hour += $lateHour;
                                        $lateness_min += $lateMin;
                                    }
                                }

                                $extraHoursLate = floor($lateness_min / 60);
                                $allowanceLate_hour = $lateness_hour+$extraHoursLate;
                                $allowanceLate_min = $lateness_min % 60;
                                ?>
                                <div class="col-md-6">
                                    <div class="absenceAdd m-t-20">
                                        <label>Total</label>
                                        <div class="timeHrsMinuts m-t-20 m-b-20">
                                            <div class="timelist">
                                                <strong>{{$allowanceLate_hour}}</strong>
                                                <span>hrs</span>
                                            </div>
                                            <div class="timelist">
                                                <strong>{{$allowanceLate_min}}</strong>
                                                <span>mins</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 m-t-20 text-center">
                                    <a href="{{url('absence/type=3?'.$user_id_key.'='.$user_id)}}" type="button" class="btn btn-warning">Add lateness</a>
                                </div>
                            </div>
                            <div class="absenceHistory m-t-40">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3 class="absencehistoryHeading">Absence History</h3>
                                        <div class="absenceAccordion">
                                            <div class="panel-group" id="accordionLateness">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <h4 class="panel-title">
                                                            <a data-toggle="collapse" data-parent="#accordionLateness" href="#collapseLateness">Lateness history ({{$lateness_ah}})</a>
                                                        </h4>
                                                    </div>
                                                    <div id="collapseLateness" class="panel-collapse collapse">
                                                        <div class="panel-body">
                                                            <div class="col-md-12">
                                                                <?php foreach($history as $all_histroy){
                                                                    if($all_histroy->leave_type == 3){
                                                                        $leave_typeAll_histroy=checkLeavType($all_histroy->leave_type);
                                                                ?>
                                                            <div class="row publicHoliday m-t-15">
                                                                <div class="col-md-2">
                                                                    <div class="sunIcon">
                                                                        <i class="fa fa-certificate"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-8">
                                                                    <div class="holidayTitle">
                                                                            <h4>{{$leave_typeAll_histroy}}</h4>
                                                                            <p><strong><?php echo date('D d M', strtotime($all_histroy->start_date)) . ' - ' . date('D d M Y',strtotime($all_histroy->end_date));?></strong> (0 hrs)</p>
                                                                            <p><button type="button" class="btn btn-warning approve">APPROVED</button> on <?php echo date('D d M Y', strtotime($all_histroy->created_at));?> by <?php echo Auth::user()->name; ?></p>
                                                                    </div>
                                                                </div>
                                                                <!-- <div class="col-md-2">
                                                                    <div class="sunIcon">
                                                                        <a href="#!"><i class="fa fa-pencil-square-o"></i></a>
                                                                        <a href="#!"><i class="fa fa-trash-o"></i></a>
                                                                    </div>
                                                                </div> -->
                                                            </div>
                                                            <?php }} ?>
                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sickness">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class=" text-center">
                                        <h3>Sickness</h3>
                                    </div>
                                </div>
                                <div class="col-md-6 p-0">
                                    <div class="absenceAdd borderLeftRight m-t-20">
                                        <label>Logged</label>
                                        <div class="timeHrsMinuts m-t-20 m-b-20">
                                            <div class="timelist">
                                                <strong>{{count($sickness)}}</strong>
                                                <span>occurrences</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="absenceAdd m-t-20">
                                        <label>Total</label>
                                        <?php 
                                        $sickCount=0;
                                        foreach($sickness as $sickVal){
                                            $sickCount=$sickCount+$sickVal->days ?? 1;
                                        }?>
                                        <div class="timeHrsMinuts m-t-20 m-b-20">
                                            <div class="timelist">
                                                <strong>{{$sickCount}}</strong>
                                                <span>days</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 m-t-20 text-center">
                                    <a href="{{url('absence/type=2?'.$user_id_key.'='.$user_id)}}" type="button" class="btn btn-warning">Add Sickness</a>
                                </div>
                            </div>
                            <div class="absenceHistory m-t-40">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3 class="absencehistoryHeading">Absence History</h3>
                                        <div class="absenceAccordion">
                                            <div class="panel-group" id="accordionSickness">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <h4 class="panel-title">
                                                            <a data-toggle="collapse" data-parent="#accordionSickness" href="#collapseSickness">Sickness history ({{$seeckness_ah}})</a>
                                                        </h4>
                                                    </div>
                                                    <div id="collapseSickness" class="panel-collapse collapse">
                                                        <div class="panel-body">
                                                            <div class="col-md-12">
                                                                <?php foreach($history as $all_histroy){
                                                                    if($all_histroy->leave_type == 2){
                                                                        $leave_typeAll_histroy=checkLeavType($all_histroy->leave_type);
                                                                ?>
                                                                <div class="row publicHoliday m-t-15">
                                                                    <div class="col-md-2">
                                                                        <div class="sunIcon">
                                                                            <i class="fa fa-certificate"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-8">
                                                                        <div class="holidayTitle">
                                                                                <h4>{{$leave_typeAll_histroy}}</h4>
                                                                                <p><strong><?php echo date('D d M', strtotime($all_histroy->start_date)) . ' - ' . date('D d M Y',strtotime($all_histroy->end_date));?></strong> (0 hrs)</p>
                                                                                <p><button type="button" class="btn btn-warning approve">APPROVED</button> on <?php echo date('D d M Y', strtotime($all_histroy->created_at));?> by <?php echo Auth::user()->name; ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <!-- <div class="col-md-2">
                                                                        <div class="sunIcon">
                                                                            <a href="#!"><i class="fa fa-pencil-square-o"></i></a>
                                                                            <a href="#!"><i class="fa fa-trash-o"></i></a>
                                                                        </div>
                                                                    </div> -->
                                                                </div>
                                                                <?php }} ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="furloughs">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class=" text-center">
                                        <h3>Furloughs</h3>
                                    </div>
                                </div>
                                <div class="col-md-12 m-t-20 text-center">
                                    <a href="#!" type="button" class="btn btn-warning">Add furlough</a>
                                </div>
                            </div>
                            <div class="absenceHistory m-t-40">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3 class="absencehistoryHeading">Absence History</h3>
                                        <div class="absenceAccordion">
                                            <div class="panel-group" id="accordionfurloughs">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <h4 class="panel-title">
                                                            <a data-toggle="collapse" data-parent="#accordionfurloughs" href="#collapsefurloughs">Furloughs current & future (0)</a>
                                                        </h4>
                                                    </div>
                                                    <div id="collapsefurloughs" class="panel-collapse collapse">
                                                        <div class="panel-body">
                                                            <div class="col-md-12">
                                                                <div class="row publicHoliday">
                                                                    <div class="col-md-2">
                                                                        <div class="sunIcon">
                                                                            <i class="fa fa-certificate"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-8">
                                                                        <div class="holidayTitle">
                                                                            <h4>Public Holiday</h4>
                                                                            <p><strong>Mon 01 Jan 2018</strong> (7 hrs)</p>
                                                                            <p>New Year's Day</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="sunIcon">
                                                                            <a href="#!"><i class="fa fa-pencil-square-o"></i></a>
                                                                            <a href="#!"><i class="fa fa-trash-o"></i></a>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row publicHoliday m-t-15">
                                                                    <div class="col-md-2">
                                                                        <div class="sunIcon">
                                                                            <i class="fa fa-certificate"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-8">
                                                                        <div class="holidayTitle">
                                                                            <h4>Public Holiday</h4>
                                                                            <p><strong>Mon 01 Jan 2018</strong> (7 hrs)</p>
                                                                            <p>New Year's Day</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="sunIcon">
                                                                            <a href="#!"><i class="fa fa-pencil-square-o"></i></a>
                                                                            <a href="#!"><i class="fa fa-trash-o"></i></a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="otherAbsences">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class=" text-center">
                                        <h3>Other Absences</h3>
                                    </div>
                                </div>
                                <div class="col-md-6 p-0">
                                    <div class="absenceAdd borderLeftRight m-t-20">
                                        <label>Logged</label>
                                        <div class="timeHrsMinuts m-t-20 m-b-20">
                                            <div class="timelist">
                                                <strong>{{count($other)}}</strong>
                                                <span>occurrences</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="absenceAdd m-t-20">
                                        <label>Total</label>
                                        <div class="timeHrsMinuts m-t-20 m-b-20">
                                            <div class="timelist">
                                                <strong>{{$allowance_Otherhour}}</strong>
                                                <span>hrs</span>
                                            </div>
                                            <div class="timelist">
                                                <strong>{{$allowanceOther_min}}</strong>
                                                <span>min</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 m-t-20 text-center">
                                    <a href="{{url('absence/type=4?'.$user_id_key.'='.$user_id)}}" type="button" class="btn btn-warning">Request other absence</a>
                                </div>
                            </div>
                            <div class="absenceHistory m-t-40">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3 class="absencehistoryHeading">Absence History</h3>
                                        <div class="absenceAccordion">
                                            <div class="panel-group" id="accordionOther">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <h4 class="panel-title">
                                                            <a data-toggle="collapse" data-parent="#accordionOther" href="#collapseOthercf">Other absence current & future ({{$other_cf}})</a>
                                                        </h4>
                                                    </div>
                                                    <div id="collapseOthercf" class="panel-collapse collapse">
                                                        <div class="panel-body">
                                                            <div class="col-md-12">
                                                                <?php 
                                                                foreach($current_future as $cfVal){
                                                                if($cfVal->leave_type == 4){
                                                                    $leave_typeAll_cfVal=checkLeavType($cfVal->leave_type);
                                                                    ?>
                                                                <div class="row publicHoliday">
                                                                    <div class="col-md-2">
                                                                        <div class="sunIcon">
                                                                            <i class="fa fa-certificate"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-8">
                                                                        <div class="holidayTitle">
                                                                            <h4>{{$leave_typeAll_cfVal}}</h4>
                                                                            <p><strong><?php echo date('D d M', strtotime($cfVal->start_date)) . ' - ' . date('D d M Y',strtotime($cfVal->end_date));?></strong> (0 hrs)</p>
                                                                            <p><b>logged</b> on <?php echo date('D d M Y', strtotime($cfVal->created_at));?> by <?php echo Auth::user()->name; ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="sunIcon">
                                                                            <a href="#!"><i class="fa fa-pencil-square-o"></i></a>
                                                                            <a href="#!"><i class="fa fa-trash-o"></i></a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <?php }}?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <h4 class="panel-title">
                                                            <a data-toggle="collapse" data-parent="#accordionOther" href="#collapseOther">Other absence history ({{$other_ah}})</a>
                                                        </h4>
                                                    </div>
                                                    <div id="collapseOther" class="panel-collapse collapse">
                                                        <div class="panel-body">
                                                            <div class="col-md-12">
                                                                <?php foreach($history as $all_histroy){
                                                                    if($all_histroy->leave_type == 4){
                                                                        $leave_typeAll_histroy=checkLeavType($all_histroy->leave_type);
                                                                ?>
                                                            <div class="row publicHoliday m-t-15">
                                                                <div class="col-md-2">
                                                                    <div class="sunIcon">
                                                                        <i class="fa fa-certificate"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-8">
                                                                    <div class="holidayTitle">
                                                                            <h4>{{$leave_typeAll_histroy}}</h4>
                                                                            <p><strong><?php echo date('D d M', strtotime($all_histroy->start_date)) . ' - ' . date('D d M Y',strtotime($all_histroy->end_date));?></strong> (0 hrs)</p>
                                                                            <p><button type="button" class="btn btn-warning approve">APPROVED</button> on <?php echo date('D d M Y', strtotime($all_histroy->created_at));?> by <?php echo Auth::user()->name; ?></p>
                                                                    </div>
                                                                </div>
                                                                <!-- <div class="col-md-2">
                                                                    <div class="sunIcon">
                                                                        <a href="#!"><i class="fa fa-pencil-square-o"></i></a>
                                                                        <a href="#!"><i class="fa fa-trash-o"></i></a>
                                                                    </div>
                                                                </div> -->
                                                            </div>
                                                            <?php }} ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var seekness_occurrences_value=document.getElementById('seekness_occurrences_value').value;
        document.getElementById('seekness_occurrences').innerHTML=seekness_occurrences_value;
        var lateness_occurrences_value=document.getElementById('lateness_occurrences_value').value;
        document.getElementById('lateness_occurrences').innerHTML=lateness_occurrences_value;
        const selectBox = document.getElementById("absenceFilter");
        const sections={1:'.allAbsences',2:'.annualLeave',3:'.lateness',4:'.sickness',5:'.otherAbsences',6:'.furloughs'};
        // console.log(sections[1]);
        // const sections = document.querySelectorAll(
        //     ".allAbsences, .annualLeave, .lateness, .sickness, .furloughs, .otherAbsences"
        // );
        default_display(sections);
        // sections.forEach(div => div.style.display = "none");
        document.querySelector(".allAbsences").style.display = "block";

        selectBox.addEventListener("change", function() {
            const value = this.value;
            // console.log(sections);
            // sections.forEach(div => div.style.display = "none");
            default_display(sections);
            // const selectedDiv = document.querySelector("." + value);
            const selectedDiv = document.querySelector(sections[value]);
            if (selectedDiv) {
                selectedDiv.style.display = "block";
            }
        });
    });
    function default_display(sections){
        for (let key in sections) {
            // console.log(`Key: ${key}, Value: ${sections[key]}`);
             const div = document.querySelector(sections[key]);
            if (div) {
                div.style.display = "none";
            }
        }
    }
    function year_select(){
        var yearVal=$('#year').val();
        if(yearVal){
            $("#yearForm").submit();
        }
    }
</script>
@endsection