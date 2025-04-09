@extends('frontEnd.layouts.master')
@section('title','Staff Rota')
@section('content')
<style type="text/css">
    .form-horizontal .form-group.triger-dt {
        margin-left: 0px;
        margin-right: 0px;
    }
    .btn-print {
        background: #1f88b5 none repeat scroll 0 0;
        border: 1px solid #1f88b5;
        border-radius: 4px;
        color: #fff;
        padding: 7px 10px;
        text-align: left;
    }
    #copyRotaModal .modal-header, #copyRotaModal .modal-body, #copyRotaModal .modal-footer {
      float: left;
      width: 100%;
    }
    #copyRotaModal .modal-body {
      background: #fff none repeat scroll 0 0;
    }
</style>

<section id="main-content">
    <section class="wrapper">
        <section class="panel cus-calendar" >
            <header class="panel-heading">
                    Staff rota       
            </header>
                <div class="panel-body">
                    <div class="row m-t-20">
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <div class="sche-dates">
                                <ul type="none">
                                    <?php
                                        // $date = date('M d',strtotime('+'.$i.' days'));
                                        // $day = date('D',strtotime('+'.$i.' days'));
                                        
                                        //echo '<th>'.$date.' ('.$day.')</th>'; 
                                        
                                        $range_startdate = date('d M', strtotime($start_day));
                                        $range_enddate   = date('d M',strtotime('+6 days', strtotime($start_day)));

                                        $back_date_val   = date('Y-m-d',strtotime('-7 days', strtotime($start_day)));
                                        $next_date_val   = date('Y-m-d',strtotime('+7 days', strtotime($start_day)));
                                        
                                    ?>

                                    <li><a href="{{ url('/staff/rota/view?start-date='.$back_date_val) }}"><span><i class="fa fa-angle-double-left"></i></span></a></li>

                                    <li>{{ $range_startdate }} - {{ $range_enddate }}</li>
                                    <li><a href="{{ url('/staff/rota/view?start-date='.$next_date_val) }}">
                                        <span><i class="fa fa-angle-double-right"></i></span></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!-- <div class="col-md-2 col-sm-6 col-xs-12">
                            <form method="post" action="{{ url('/staff/rota/time-format') }}" id="records_per_time_format">
                                <div class="select-style" style="width:70%; float: right">
                                    <select name="rota_sel_time_format" class="select_time">
                                        <option value="12" {{ $rota_time_format == '12' ? 'selected': '' }}> 12 Hours </option>
                                        <option value="24" {{ $rota_time_format == '24' ? 'selected': '' }}> 24 Hours </option>
                                    </select>
                                </div>
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            </form>
                        </div> -->

                        <div class="col-md-6 col-sm-6 col-xs-12" style="float:right;">
                            <div class="add-schedule">
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <form method="post" action="{{ url('/staff/rota/view') }}" id="records_per_page_form">
                                        <div class="select-style" style="width:70%; float: right">
                                            <select name="rota_sel_access_level" class="select_limit">
                                                <option value="0" {{ ($rota_access_level == '0') ? 'selected': '' }}> All Access Levels </option>
                                                @foreach($access_level_name as $name)
                                                <option value="{{ $name->id }}" {{ ($rota_access_level == $name->id) ? 'selected': '' }}>{{ $name->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    </form>
                                </div>
                                <div class="col-md-3 col-sm-5 col-xs-12" >
                                    <a href="javascript:void(0)" class="btn sche-btn" data-target="#addshiftmodal" data-toggle="modal"> <i class="fa fa-plus"></i> Add More </a>
                                </div>
                                <div class="col-md-2 col-sm-5 col-xs-12" >
                                    <a href="javascript:void(0)" class="btn sche-btn" data-target="#copyRotaModal" data-toggle="modal"> <i class="fa fa-plus"></i> Copy </a>
                                </div>   
                                <div class="col-md-1 col-sm-1 col-xs-12" >
                                    <a href="{{ url('/staff/rota/print?start-date='.$start_date) }}">
                                        <button class="btn-print">
                                            <i class="fa fa-print"></i>
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="col-md-6 col-sm-6 col-xs-12">
                            <div class="select-style" style="width:50%; float: left">
                                <select name="access_level_name">
                                    <option value="0"> Select Access  </option>
                                    @foreach($access_level_name as $name)
                                    <option value="{{ $name->id }}">{{ $name->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="add-schedule">
                                <a href="javascript:void(0)" class="btn sche-btn" data-target="#addshiftmodal" data-toggle="modal"> <i class="fa fa-plus"></i> Add More </a>
                            </div>
                        </div> -->
                  
                        <div class="schedule-table" ng-app="myApp" ng-controller="myCtrl">
                            @if($shift_types->isEmpty())
                            <p class="m-l-15" style="color:#e10078;">Note: Your rota shifts are not set. Please contact admin for setting.</p>
                            @endif
                            <div class="table-responsive" id="calendar" style="margin:0px 15px;">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr class="rota-th">
                                          <th>Dates  </th>
                                            <?php for($i=0; $i<=6; $i++) {
                                                $date = date('M d',strtotime('+'.$i.' days', strtotime($start_day)));
                                                $day = date('D',strtotime('+'.$i.' days', strtotime($start_day)));
                                                
                                                echo '<th>'.$date.' ('.$day.')</th>'; 
                                            } ?>
                                          <th>Weekly Hours  </th>

                                        </tr>

                                        <?php
                                        if(empty($staff)){
                                            echo '<tr><td colspan="8"><p style="font-weight:500">No Records Found.</p></td></tr>';
                                        } else{
                                        foreach($staff as $staff_member) { //for rows
                                            // echo "<pre>"; print_r($staff_member); die;
                                            $duration = 0;
                                            $user_image = '';
                                            if(isset($staff_member['image'])){
                                                $user_image = $staff_member['image'];
                                            }
                                            if(empty($user_image)){
                                                $user_image = 'default_user.jpg';
                                            }
                                        ?>
                                        <tr>
                                            <th>
                                                <img src="{{ userProfileImagePath.'/'.$user_image }}" class="img-responsive" />
                                                <span class="employee-name" id="{{ $staff_member['id'] }}">{{ ucfirst($staff_member['name']) }}</span>
                                            </th>

                                            <?php foreach($staff_member['rota'] as $key => $rota) { //for columns

                                                if(!empty($rota)){
                                                    if($rota_time_format == '12') {  
                                                        $start_time = App\StaffRota::timeFormat($rota['start_time']);
                                                        $end_time   = App\StaffRota::timeFormat($rota['end_time']);
                                                    } else {
                                                        $start_time = $rota['start_time'];
                                                        $end_time   = $rota['end_time'];
                                                        if(strlen($rota['start_time']) == 1) {
                                                            $start_time = '0'.$start_time;
                                                        }
                                                        if(strlen($rota['end_time']) == 1) {
                                                            $end_time = '0'.$end_time;
                                                        }
                                                    }

                                                    $start_hr = $rota['start_time'];
                                                    $end_hr   = $rota['end_time'];
                                                    $duration += $end_hr - $start_hr;
                                                ?>
                                                    <td>
                                                        <div class="{{ $rota['color'] }}-div cross-top edit_rota_shift" rota_id="{{ $rota['id'] }}" date="{{ $key }}">{{ $rota['name'] }} 
                                                            <span class="cross-icon delete_rota_shift">
                                                                <i class="fa fa-times"></i>
                                                            </span> 
                                                            <p class="time">{{ $start_time }} - {{ $end_time }}</p>
                                                        </div>
                                                    </td>
                                                <?php } else { ?>
                                                    <td>
                                                        <div class="empty-div"><i class="fa fa-plus"></i>
                                                            <ul type="none" class="empty-contnt">
                                                                <?php foreach($shift_types as $shift_type) {
                                                                    $start_time = App\StaffRota::timeFormat($shift_type->start_time);
                                                                    $end_time = App\StaffRota::timeFormat($shift_type->start_time);
                                                                ?>
                                                                
                                                                <li><div class="{{ $shift_type->color }}-div" date="{{ $key }}" start_time="{{ $start_time }}" end_time="{{ $end_time }}" type_id="{{ $shift_type->type_id }}">{{ $shift_type->name }}</div></li>
                                                                
                                                                <?php } ?>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                <?php  } 
                                                }?>
                                                <th style="width:10%">
                                                    <p class="p-t-5 duration-{{$staff_member['id']}}">{{ $duration }} Hr</p>
                                                </th>
                                         <?php   }
                                        } 
                                        ?>
                                    </tbody>
                            </table>
                        </div>
                    </div>
                       <!--  </div> -->
                   <!--  </div>
                    </div> -->
                </div>
            <!-- </section> -->
        </section>
    </section>
</section>

<!-- add shift Modal -->
<div class="modal fade" id="addshiftmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Add Rota</h4>
            </div>
            <div class="modal-body">
                <div class="add-shifts">
                    <form class="form-horizontal" action="{{ url('/staff/rota/add-rota') }}" method="post">
                        <div class="col-md-6 col-sm-6 col-xs-12 form-group triger-dt">
                            <label class="col-md-5 col-sm-5 col-xs-12 control-label p-0">Book From</label>
                            <div class="col-md-7 col-sm-7 col-xs-12 input-group">
                                <input name="from_date" required class="form-control datetime-picker change_date trans" type="text" value="" autocomplete="off" maxlength="10" readonly="" />
                               <!--  <input type="" name="" class="form-control"> -->
                                <span class="input-group-addon new-trigr"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12 form-group triger-dt">
                            <label class="col-md-5 col-sm-5 col-xs-12 control-label">To</label>
                            <div class="col-md-7 col-sm-7 col-xs-12 input-group">
                                <input name="to_date" required class="form-control trans" id="datetime-picker1" type="text" value="" autocomplete="off" maxlength="10" readonly="" />
                                <!-- <input type="" name="" class="form-control"> -->
                                <span class="input-group-addon out-triger"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 form-group triger-dt p-r-0">
                            <label class="col-md-2 col-sm-3 col-xs-12 p-0 control-label">Time Zone : </label>
                            <div class="col-md-10 col-sm-9 col-xs-12">
                                <div class="select-style">
                                    <select name="shift_type_id">
                                        <option value="0"> Select Shift </option>
                                        @foreach($shift_types as $shift_type)
                                        <option value="{{ $shift_type->type_id }}">{{ $shift_type->name }}</option>
                                        @endforeach
                                       <!--  <option> Morning </option>
                                        <option> Evening </option>
                                        <option> Fullday </option>
                                        <option> Holiday </option> -->
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 form-group triger-dt p-r-0">
                            <label class="col-md-2 col-sm-3 col-xs-12 p-0 control-label">Staff Members: </label>
                            <div class="col-md-10 col-sm-9 col-xs-12">
                                <div class="select-style select-bi" style="width:100%;float:left;">
                                    <select name="staff_id[]" class="js-example-placeholder-single form-control" multiple="multiple" style="width:100%;">
                                        <option value=""></option>
                                        @foreach($staff as $value)
                                         <option value="{{ $value['id'] }}">{{ $value['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <!--   <select class="js-example-basic-multiple form-control" multiple="multiple" style="width:100%;">
                                        <option> John</option>
                                        <option> Shawn </option>
                                        <option> Micheal </option>
                                        <option> Harry </option>
                                    </select> -->
                                </div>
                            </div>
                        </div>
                        <div class="shift-note">
                            <p><strong>Note :</strong> The previous Shifts will be overlapped by the new shifts if present</p>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary sbt_rota_form">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
           
        </div>
    </div>
</div>

<!-- edit shift Modal -->
<div class="modal fade" id="editShiftModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="float: left; width: 100%">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Edit Shift</h4>
            </div>
            <div class="modal-body">
                <div class="add-shifts">
                    <form class="form-horizontal" action="{{ url('/staff/rota/shift/edit') }}" method="post">

                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label class="col-md-2 col-sm-3 col-xs-12 p-0 control-label">Name : </label>
                            <div class="col-md-10 col-sm-9 col-xs-12">
                                <div class="input-group popovr" style="float: left; width: 100%">
                                  <input name="e_staff_name" value="" class="form-control" maxlength="255" type="text" disabled="">
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label class="col-md-2 col-sm-3 col-xs-12 p-0 control-label">Shift Type : </label>
                            <div class="col-md-10 col-sm-9 col-xs-12">
                                <div class="input-group popovr" style="float: left; width: 100%">
                                  <input name="e_shift_name" value="" class="form-control" maxlength="255" type="text" disabled="">
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-lg-6 col-md-6 col-sm-12 col-xs-12 p-r-0">
                            <label class="col-lg-5 col-md-2 col-sm-3 col-xs-12 p-0 control-label pt-0 ">Start Time : </label>
                            <div class="col-lg-7 col-md-10 col-sm-9 col-xs-12 p-0">
                                <div class="select-style">
                                    <select name="e_start_time" class="e_start_time">
                                    @if($rota_time_format == '12')
                                        <option value="0">Select Start Time </option>
                                        <?php 
                                            for ($i=1; $i <= 24; $i++) { 
                                                if($i <=12) { ?>
                                                    <option value="{{ $i }}">{{ $i }} am</option>
                                         <?php  } else { $time = $i-12; ?> 
                                        <option value="{{ $i }}">{{ $time }} pm</option>
                                        <?php } } ?>
                                    @endif
                                    @if($rota_time_format == '24')
                                        <option value="0">Select Start Time </option>
                                        <?php 
                                            for ($i=1; $i <= 24; $i++) { ?>
                                                <option value="{{ $i }}">{{ $i }}</option>
                                         <?php  }  ?> 
                                    @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="edit_end_date_div">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 p-r-0">
                                <label class="col-lg-5 col-md-2 col-sm-3 col-xs-12 p-0 control-label">End Time : </label>
                                <div class="col-lg-7 col-md-10 col-sm-9 col-xs-12 p-0">
                                    <div class="select-style">
                                        <select name="e_end_time" class="e_end_time">
                                            <!-- end date option comes dyamic -->
                                        @if($rota_time_format == '12')
                                            <option value="0">Select End Time </option>
                                            <?php 
                                            for ($i=1; $i <= 24; $i++) { 
                                                    if($i <=12) { ?>
                                                        <option value="{{ $i }}">{{ $i }} am</option>
                                             <?php  } else { $time = $i-12; ?> 
                                            <option value="{{ $i }}">{{ $time }} pm</option>
                                            <?php } } ?>
                                        @endif
                                        @if($rota_time_format == '24')
                                            <option value="0">Select End Time </option>
                                            <?php 
                                                for ($i=1; $i <= 24; $i++) { ?>
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                             <?php  }  ?> 
                                        @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- end comes dyamic -->
                        </div>
                        <div class="modal-footer modal-bttm m-b-10" style="float: left; width: 100%; background-color: #fff;">
                            <input type="hidden" name="e_rota_id" value="">
                            <input type="hidden" name="e_user_id" value="">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary edit_rota_shift_form">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
           
        </div>
    </div>
</div>

<!-- Copy Rota Modal -->
<div class="modal fade" id="copyRotaModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Copy Rota</h4>
            </div>
            <div class="modal-body">
                <div class="add-shifts">

                    <form class="form-horizontal" action="{{ url('/staff/rota/copy') }}" method="get">
                        
                        <div class="col-md-12 col-sm-12 col-xs-12 form-group triger-dt p-r-0">
                            <label class="col-md-2 col-sm-3 col-xs-12 p-0 control-label">Select Week : </label>
                            <div class="col-md-10 col-sm-9 col-xs-12">
                                <div class="select-style">
                                    <select name="copy_start_date">
                                        <option value=""> Select Week </option>
                                        <?php 
                                            foreach($weeks as $week) {
                                                $week_start_date = date('d M', strtotime($week['start_date']));
                                                $week_end_date   = date('d M', strtotime($week['end_date'])); 
                                        ?>
                                        <option value="{{ $week['start_date'] }}">{{ $week_start_date.' - '.$week_end_date }}</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="shift-note">
                            <p><strong>Note :</strong> The previous Shifts will be overlapped by the new shifts if present</p>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" name="start-date" value="{{ $start_date }}">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
           
        </div>
    </div>
</div>

<script>
    //$('.empty-contnt').hide();
    //$('.empty-div').on('click',function(){ alert('m');

    $(document).on('click', '.empty-div', function(){    
        var shift_types = "{{ $shift_types}}";
        if(shift_types == '[]') {
            alert('Your rota shifts are not set. Please contact admin for setting.');
        } else {
            // alert(2);
            $(this).closest('td').siblings('td').find('.empty-contnt').hide();
            $(this).closest('tr').siblings('tr').find('.empty-contnt').hide();
            $(this).find('.empty-contnt').toggle();
        }
        // $(this).closest('td').siblings('td').find('.empty-contnt').hide();
        // $(this).closest('tr').siblings('tr').find('.empty-contnt').hide();
        // $(this).find('.empty-contnt').toggle();
    });

    //$('.empty-contnt li').on('click',function(){
    $(document).on('click','.empty-contnt li', function(){
        var shift      = $(this); 
        var type_id    = shift.children().attr('type_id');
        var date       = shift.children().attr('date');
        var staff_id   = shift.closest('tr').find('.employee-name').attr('id');
        var start_day  = "{{ $start_day }}";

        // var duration   = $('.duration').text();
        // alert(duration);
        
        $('.loader').show();
        $('body').addClass('body-overflow');

        $.ajax({
            type:'post',
            dataType : "json",
            data : { 'shift_type_id':type_id, 'date':date, 'staff_id':staff_id, 'start_day': start_day },
            url: "{{ url('staff/rota/add-shift') }}",
            success:function(resp){
                
                if(isAuthenticated(resp) == false){
                    return false;
                }
                console.log(resp);

                if(resp.response == true) {
                    var class_name = shift.children().attr('class');
                    var shift_name = shift.children().text();

                    var start_time = resp.start_time;
                    var end_time   = resp.end_time;
                    var rota_id    = resp.rota_id;
                    var date       = resp.date;
                    var hrs       = resp.hrs;

                    var content = '<div class="'+class_name+' cross-top edit_rota_shift" rota_id="'+rota_id+'" date="'+date+'">'+shift_name+'<span class="cross-icon delete_rota_shift"> <i class="fa fa-times"></i> </span> <p class="time">'+start_time+' - '+end_time+' </p> </div>';
                    
                    shift.closest('td').html(content);
                    
                   // var duration  = $();

                    // shift.find('.duration').text(hrs+' Hr');
                   $('.duration-'+staff_id).text(hrs+' Hr');

                    alert('Shift added successfully');

                } else if(resp.response == 'already_exists') {
                    alert("This shift is already booked.");                    
                } else if(resp.response == 'not_exist_shift_time') {
                    alert("Please set rota shift timing from Admin.");
                }
                else{
                    alert("{{ COMMON_ERROR }}");                    
                }
                $('.loader').hide();
                $('body').removeClass('body-overflow');

                $('.empty-contnt').hide();
                return false;
            }
        });

    });
</script>
    
<script>
    //delete rota shift
    $(document).ready(function(){

        $(".delete_rota_shift").on("click", function(){ 
            return confirm("Do you want to delete it ?");
        });

        $(document).on('click','.delete_rota_shift', function(){

            var del_btn = $(this);
            var rota_id = $(this).parent().attr('rota_id');
            var date    = $(this).parent().attr('date');
            var start_day  = "{{ $start_day }}";
            // $('.loader').show();
            // $('body').addClass('body-overflow');

            $.ajax({
                type : 'get',
                url  : "{{ url('/staff/rota/delete-shift/') }}"+'/'+rota_id+'?start_day='+start_day,
                success: function(resp) {
                    console.log(resp);
                    if(isAuthenticated(resp) == false){
                        return false;
                    }
                    if(resp.response == true) {

                        var hrs      = resp.hrs;
                        var staff_id = resp.staff_id;
                        $('.duration-'+staff_id).text(hrs+' Hr');
                        alert('Shift Deleted successfully.');

                        var content = '<div class="empty-div"><i class="fa fa-plus"></i> <ul type="none" class="empty-contnt">';
                            <?php foreach($shift_types as $shift_type) {
                                $start_time = App\StaffRota::timeFormat($shift_type->start_time);
                                $end_time   = App\StaffRota::timeFormat($shift_type->start_time);
                            ?>
                            
                            content += '<li><div class="{{ $shift_type->color }}-div" date="'+date+'" start_time="{{ $start_time }}" end_time="{{ $end_time }}" type_id="{{ $shift_type->type_id }}">{{ $shift_type->name }}</div></li>';
                            
                            <?php } ?>
                        content += '</ul> </div>';                   

                        del_btn.closest('td').html(content);

                    } else {
                        alert("{{ COMMON_ERROR }}");  
                    }
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });
    });
</script>

<script >
    //select options 
    $(document).ready(function(){
        $(".js-example-placeholder-single").select2({
              dropdownParent: $('#addshiftmodal'),
              placeholder: "Select shift"
        });
     });
</script>

<script>
    $(document).ready(function() {

        today  = new Date; 
        $('.datetime-picker').datetimepicker({
           // format: 'dd-mm-yyyy',
            format: 'dd-mm-yyyy',
            startDate: today,
            minView : 2
        });
        $('.change_date').datetimepicker({
           format: 'dd-mm-yyyy',
            //format: 'yyyy-mm-dd',
            startDate: today,
            minView : 2
        });

        $('#datetime-picker1').datetimepicker({
            format: 'dd-mm-yyyy',

            //format: 'yyyy-mm-dd',
            startDate: today,
            minView : 2,

        });

        $('#datetime-picker1').on('click', function(){
            $('#datetime-picker1').datetimepicker('show');
        });
        $(document).on('click','.out-triger', function () {
          $('#datetime-picker1').trigger('click');
        });

        $('.datetime-picker').on('click', function () {
            $('.datetime-picker').datetimepicker('show');
        });
        $(document).on('click','.new-trigr', function () {
          $('.datetime-picker').trigger('click');
        });

        $('#datetime-picker1').on('change', function(){
            $('#datetime-picker1').datetimepicker('hide');
        });

        // $('#datetime-picker1').datepicker()
        // .on('changeDate', function(ev){
        //     if (ev.date.valueOf() < startDate.valueOf()){
        //         $('.alert').show().find('strong').text('The end date can not be less then the start date');
        //     } else {
        //         $('.alert').hide();
        //         endDate = new Date(ev.date);
        //         $('.endDate').text($('#datetime-picker1').data('date'));
        //     }
        //     $('#datetime-picker1').datepicker('hide');
        // });

        

    });
</script>

<script>
    //validation for add rota shift form
    $(document).ready(function(){
        $(document).on('click','.sbt_rota_form', function(){
            var from_date = $('input[name=\'from_date\']').val();

            var date= from_date;
            var d=new Date(date.split("-").reverse().join("-"));
            var dd=d.getDate();
            var mm=d.getMonth()+1;
            var yy=d.getFullYear();
            var newdate = yy+"-"+mm+"-"+dd;
        
            var to_date   = $('input[name=\'to_date\']').val();

            var date= to_date;
            var d=new Date(date.split("-").reverse().join("-"));
            var dd=d.getDate();
            var mm=d.getMonth()+1;
            var yy=d.getFullYear();
            var todate=yy+"-"+mm+"-"+dd;

            var shift_type_id = $('select[name=\'shift_type_id\']').val();
            var staff_id  = $('select[name=\'staff_id[]\']').val();
            var error = 0;
            // todate = parseInt(todate);
            // newdate = parseInt(newdate);
            //alert(todate); alert(newdate);
            if(todate < newdate) {
                alert('To date less then from date, Please change it.');
                error = 1;
                //return false;
            } //else {
            //     alert('okk')
            // }
            // return false;

            if(from_date == '') {
                $('input[name=\'from_date\']').parent().addClass('red_border');
                error = 1;
            } else {
                $('input[name=\'from_date\']').parent().removeClass('red_border');
            }
            if(to_date == '') {
                $('input[name=\'to_date\']').parent().addClass('red_border');
                error = 1;

            } else {
                $('input[name=\'to_date\']').parent().removeClass('red_border');
            }
            if(shift_type_id == 0) {
                $('select[name=\'shift_type_id\']').addClass('red_border');
                error = 1;
            } else {
                $('select[name=\'shift_type_id\']').removeClass('red_border');
            }
             if(staff_id == null) {
                $('select[name=\'staff_id[]\']').parent().addClass('red_border');
                error = 1;
            } else {
                $('select[name=\'staff_id[]\']').parent().removeClass('red_border');
            }
            if(error == 1) {
                return false;
            }   
           // return false;
        });
    });
</script>


<script>
    //Selecting Access Level
    $(document).ready(function(){
        $('.select_limit').change(function(){
            $('#records_per_page_form').submit();
        });
        $('.select_time').change(function(){
            $('#records_per_time_format').submit();
        });
    });
</script>


<script>
    //view rota shift record
    $(document).on('click','.edit_rota_shift', function(){

        var time = $(this);
        var rota_id = $(this).attr('rota_id');

        $('.loader').show();
        $('body').addClass('body-overflow');
 
        $.ajax({
            type : 'get', 
            url  : "{{ url('/staff/rota/shift/view/') }}"+'/'+rota_id,
            dataType : 'json',
            success : function(resp) {
                if(isAuthenticated(resp) == false) {
                    return false;
                }
                var response = resp['response'];
                if(response == true) {
                    var rota_id    = resp['rota_id'];
                    var user_id    = resp['user_id'];
                    var staff_name  = resp['staff_name'];
                    var shift_name = resp['shift_name'];
                    var start_time = resp['start_time'];
                    var end_time   = resp['end_time']

                    // var content = '<option value="0">Select End Time </option>';
                    //     for (var i=1; i <= 24; i++) { 
                            
                    //         if(i == end_time) {
                    //             selected = 'selected';
                    //         } else {
                    //             selected = '';
                    //         }
                            
                    //         if(i < start_time) {
                    //             disabled = 'disabled';
                    //         }else {
                    //             disabled = '';
                    //         }

                    //     if(i<=12) { 
                    //     content+= '<option value="'+ i +'" '+ selected +' '+ disabled +' >'+ i +' am</option>';
                    //             } else { var time = i-12; 
                    //     content+= '<option value="'+ i +'" '+ selected +' '+ disabled +'>'+ time +' pm</option>';
                    //             }   } 

                    $('input[name=\'e_rota_id\']').val(rota_id);
                    $('input[name=\'e_user_id\']').val(user_id);
                    $('input[name=\'e_staff_name\']').val(staff_name);
                    $('input[name=\'e_shift_name\']').val(shift_name);
                    $('select[name=\'e_start_time\']').val(start_time);
                    $('select[name=\'e_end_time\']').val(end_time);

                    // $('.e_end_time').html(content);

                    $('#editShiftModal').modal('show');
                } else {
                    $('.ajax-alert-err').find('.msg').text('Some error occured,Plese try again later.');
                    $('.ajax-alert-err').show();
                    setTimeout(function(){$(".ajax-alert-err").fadeOut()}, 5000);
                }
                $('.loader').hide();
                $('body').removeClass('body-overflow');
            }
        });
        return false;   

    });
</script>

<script>
    // $(document).on('change','.e_start_time',function(){
        
    //     var start_time = $(this).val();
        
    //     var content = '';
    //     for (var i=1; i <= 24; i++) { 
            
    //         if(i < start_time) {
    //             disabled = 'disabled';
    //         }else {
    //             disabled = '';
    //         }

    //         if(i<=12) { 
               
    //             content += '<option value="'+ i +'" '+disabled+'>'+ i +' am</option>';

    //         } else { 
    //             var time = i-12;
    //             content += '<option value="'+ i +'" '+disabled+'>'+ time +' pm</option>';
    //         }
    //     }

    //     $('.e_end_time').html(content);

    // });           
</script>

<script>
    //validation for edit rota form
    $(document).ready(function(){
        $(document).on('click','.edit_rota_shift_form', function(){
            var e_start_time = $('select[name=\'e_start_time\']').val();
            var e_end_time   = $('select[name=\'e_end_time\']').val();
           
            var error = 0;
            // e_start_time = parseInt(e_start_time);
            // e_end_time   = parseInt(e_end_time);

            // if(e_start_time > e_end_time) {
            //     alert('End time less then Start time, Please change it.');
            //     error = 1; 
            // } else {
            //     //alert('ok');
            //     //return true;
            // }
            
            if(e_start_time == 0) {
                $('select[name=\'e_start_time\']').addClass('red_border');
                error = 1;
            } else {
                $('select[name=\'e_start_time\']').removeClass('red_border');
            }
            if(e_end_time == 0) {
                $('select[name=\'e_end_time\']').addClass('red_border');
                error = 1;
            } else {
                $('select[name=\'e_end_time\']').removeClass('red_border');
            }
            if(error == 1) {
                return false;
            }

        });
    });
</script>

@endsection