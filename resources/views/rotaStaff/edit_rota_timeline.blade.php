@extends('frontEnd.layouts.master')

@include('rotaStaff.components.header')
<style>
    .shift_detail_modal_body {
        max-height: 60vh;
        overflow-y: auto;
    }

    .employees-info .save-btn {
        background-color: #e10078;
        font-weight: 600;
    }

    .employee-name {
        max-height: 38vh;
        overflow-y: auto;
    }

    .modal {
        top: 5%;
    }

    .backIcon i {
        color: #1f88b5;
        font-size: 20px;
    }

    .backIcon {
        text-align: right;
        margin-bottom: 25px;
    }
</style>
@foreach($rota as $rota_data)
<div class="row">
    <div class="col-md-12">
        <div class="backIcon"> <a href="{{ url('/rota') }}"><i class="fa fa-arrow-right" aria-hidden="true"></i></a> </div>
    </div>
</div>
<!-- Top Bar Info Section End Here -->

<section class="wrapper">
    <div class="panel">
        <header class="panel-heading">
              <h4><a href="{{ url('/rota') }}"> <span class="rota-link"> All Rota</span> </a> > {{ $rota_data->rota_name}}</h4>
        </header>
        <div class="panel-body" id="timeline">
            <div class="row">
                <div class="col-md-12">
                    <div class="rota_name">
                        <!-- <p class="breadcrum"> <a href="{{ url('/rota') }}"> <span class="rota-link">
                                    All Rota</span> </a> > {{ $rota_data->rota_name}} </p> -->
                        <!-- <h3>{{ $rota_data->rota_name}}</h3> -->
                    </div>
                    <input type="hidden" value="{{ $rota_data->id }}" id="new_rota">
                    <div class="d-flex justify-content-between">
                        <div class="date-info">
                            <p>{{ date("D j M", strtotime($rota_data->rota_start_date)) }} - {{ date("D j M", strtotime($rota_data->rota_end_date)) }} | {{ $rota_data->rota_duration }} days | <span id="shift_count1">0</span> staff members</p>
                        </div>
                        <div class="shift-patern d-none">
                            <div class="select-shift">
                                Show shifts for:
                                <input type="radio" class="input_custom_btn" id="everyone"
                                    name="select">
                                <div class="label">
                                    <label for="everyone" class="label_custom every">Everyone</label>
                                </div>
                                <input type="radio" id="me" class="input_custom_btn" name="select">
                                <div class="label">
                                    <label for="me" class="label_custom me">Me</label>
                                </div>
                                <input type="radio" id="specific" class="input_custom_btn"
                                    name="select">
                                <div class="label">
                                    <label for="specific" class="label_custom specific">Specific
                                        people...</label>
                                </div>
                            </div>
                            <div class="select-yes-no">
                                Show notes:
                                <input type="radio" class="input_custom_btn" id="yes" name="yes_no">
                                <div class="label">
                                    <label for="yes" class="label_custom yes">Yes</label>
                                </div>
                                <input type="radio" id="no" class="input_custom_btn" name="yes_no">
                                <div class="label">
                                    <label for="no" class="label_custom no">No</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div class="d-none">
                            <p>Once a shift has been added you can publish this rota.</p>
                        </div>
                        <div class="d-flex print_and_publish_button">
                            <div>
                                <!-- Button trigger modal -->
                                @if($rota_data->status === 1)
                                <button type="button" onclick="renamedata(<?= $rota_data->id ?>,'<?= $rota_data->rota_name ?>',<?= $rota_data->status ?>)" class="publish_btn">
                                    Unpublish
                                </button>
                                @endif
                                @if($rota_data->status === 0)
                                <button type="button" onclick="renamedata(<?= $rota_data->id ?>,'<?= $rota_data->rota_name ?>',<?= $rota_data->status ?>)" class="publish_btn">
                                    Publish
                                </button>
                                @endif

                                <!-- Modal -->
                                <div class="modal fade" id="exampleModalPublish" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="exampleModalLabel">Are you sure you want to @if($rota_data->status === 0)
                                                    Publish
                                                    @endif
                                                    @if($rota_data->status === 1)
                                                    Unpublish
                                                    @endif this rota?</h1>
                                                <button type="button" class="modal_close_btn" data-bs-dismiss="modal" aria-label="Close"> &#10006; </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="publish_modal">
                                                    <input type="hidden" id="rota_id">
                                                    <input type="hidden" id="rota_status">
                                                    <div class="content_publish_rota">This will show <span id="rota_name_model"></span> to your employees. They will be able to see their shifts and absence conflicts.</div>
                                                </div>
                                            </div>
                                            <div class="modal-footer justify-content-between">
                                                <button type="button" class="cancel_btn" data-bs-dismiss="modal">Close</button>
                                                <button type="button" class="publish_btn_footer" id="publish_unpublish_btn">
                                                    @if($rota_data->status === 0)
                                                    Publish
                                                    @endif
                                                    @if($rota_data->status === 1)
                                                    Unpublish
                                                    @endif
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <button type="button" class="print_btn" onclick="window.print()">Print rota</button>
                            </div>
                        </div>
                        <div class="d-flex align-items-center drag-table-link d-none">
                            <a href="#"><i class="fa fa-clone"></i> Drag and drop view</a>
                            <a href="#"><i class="fa fa-calendar"></i> Table view</a>
                        </div>
                    </div>
                    <div class="d-flex time-for-shift">
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(161, 169, 179);">
                            0:00
                        </div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(232, 234, 236);">
                        </div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(232, 234, 236);">
                        </div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(232, 234, 236);">
                        </div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(161, 169, 179);">
                            4:00</div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(232, 234, 236);">
                        </div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(232, 234, 236);">
                        </div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(232, 234, 236);">
                        </div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(161, 169, 179);">
                            8:00</div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(232, 234, 236);">
                        </div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(232, 234, 236);">
                        </div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(232, 234, 236);">
                        </div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(161, 169, 179);">
                            12:00</div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(232, 234, 236);">
                        </div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(232, 234, 236);">
                        </div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(232, 234, 236);">
                        </div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(161, 169, 179);">
                            16:00</div>
                        <div class="add-color"
                            style="border-bottom: 1px solid #e8eaec; width: calc(4.16667%); border-left: 1px solid rgb(232, 234, 236);">
                        </div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(232, 234, 236);">
                        </div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(232, 234, 236);">
                        </div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(161, 169, 179);">
                            20:00</div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(232, 234, 236);">
                        </div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(232, 234, 236);">
                        </div>
                        <div class="add-color"
                            style="border-bottom: 1px solid rgb(232, 234, 236); width: calc(4.16667%); border-left: 1px solid rgb(232, 234, 236);">
                        </div>
                    </div>
                    <div class="accordion" id="accordionPanelsStayOpenExample">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="panelsStayOpen-headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true"
                                    aria-controls="panelsStayOpen-collapseOne">
                                    {{ date("D j M Y", strtotime($rota_data->rota_start_date)) }} - {{ date("D j M Y", strtotime($rota_data->rota_end_date)) }}
                                </button>
                            </h2>
                            <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse"
                                aria-labelledby="panelsStayOpen-headingOne">
                                <div class="accordion-body">
                                    <?php
                                    $period = \Carbon\CarbonPeriod::create(date("Y-m-d", strtotime($rota_data->rota_start_date)), date("Y-m-d", strtotime($rota_data->rota_end_date)));
                                    $i = 1;
                                    foreach ($period as $date) {
                                    ?>
                                        <div class="d-flex">
                                            <div class="date-of-shift">
                                                <strong>{{$date->format('D j M')}}</strong>
                                            </div>
                                            <div class="amount-of-shift">
                                                <p><span id="shift_count"></span> shifts</p>
                                            </div>
                                            <div class="add-shift-btn">
                                                <!-- Button trigger modal -->
                                                <button type="button" class="modal-btn" onclick="view_shift_model('<?= $date->format('l d F') ?>','<?= $date->format('D j M') ?>', <?= $i ?>)"> Add shift </button>

                                                <!-- Modal -->

                                            </div>
                                        </div>
                                        <?php
                                        $rota_shift = App\RotaShift::where('rota_id', $rota_data->id)->where('rota_day_date', $date->format('Y-m-d'))->where('status', 1)->get();
                                        $userdata = array();
                                        foreach ($rota_shift as $rota_shifts) {
                                            $shift_id = $rota_shifts->id;
                                            $shift_start = $rota_shifts->shift_start_time;
                                            $shift_end = $rota_shifts->shift_end_time;
                                            $description = $rota_shifts->description;

                                            $list_emp = App\RotaAssignEmployee::where('rota_id', $rota_data->id)->where('shift_id', $shift_id)->where('status', 1)->get();
                                            foreach ($list_emp as $emp_ids) {
                                                $userdata[] = App\ServiceUser::where('id', $emp_ids->emp_id)->get();
                                            }
                                        }
                                        foreach ($userdata as $user_data) {
                                        ?>
                                            <div class="w_full">
                                                <?php for ($count = 0; $count < 24; $count++) {
                                                    echo  "<div class='hour_box' style='width: calc(4.16667%);'>
                                                                          </div>";
                                                } ?>
                                                <!-- Button shift modal -->
                                                <button type="button" class="shift_timing_btn" onclick="view_user_data(<?php echo $shift_id; ?>,`<?php echo $user_data[0]['id']; ?>`)" style="width: `+hours*4.16667+`%;  left: `+hours*4.16667+`%;" style="sdisplay: none;" data-testid="Shift card" style="width: 33.3333%; left: 37.5%;">
                                                    <div class="d-flex align-items-center">
                                                        <div class="">
                                                            <div class="name_of_member">
                                                                <div class="">
                                                                    <?php
                                                                    $str = str_split($user_data[0]['name']);
                                                                    echo strtoupper($str[0]);
                                                                    $whatIWant = substr($user_data[0]['name'], strpos($user_data[0]['name'], " ") + 1);
                                                                    $str1 =  str_split($whatIWant);
                                                                    echo strtoupper($str1[0]);
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="">
                                                            <div class="d-flex align-items-center">
                                                                <div class="name_of_person">{{ $user_data[0]['name'] }}</div>
                                                                <div class="shift_timeing_duration">{{ \Carbon\Carbon::parse($shift_start)->format('h:i') }} - {{ \Carbon\Carbon::parse($shift_end) ->format('h:i') }}</div>
                                                            </div>
                                                            <div class="">{{ $description }}</div>
                                                        </div>
                                                    </div>
                                                </button>
                                                <!-- Modal -->
                                            </div>

                                        <?php  } ?>
                                        <div id="show_user_record<?= $i ?>">
                                        </div>
                                    <?php $i++;
                                    }  //$dates = $period->toArray();
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  
    @endforeach
</section>
<!-- Modal -->
<div class="modal fade addShift" id="exampleModalAddShift" tabindex="-1" aria-labelledby="exampleModalLabel" style="display: none;" aria-hidden="true">
    <div class="modal-dialog" style="display: block; max-width: 50rem;">
        <div class="modal-content">
            <div class="modal-body">
                <div class="container remove-padding M-right" id="multiForm" style="display: block;">
                    <form action="" method="post" id="select">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="follow-step">

                                    <ul class="employees-detail d-flex">
                                        <li class="Employees-details detail">
                                            <?php
                                            if (isset($pass_rota_id)) {

                                                echo "<input type='hidden' id='edit_rota_id' value=" . $pass_rota_id . ">";
                                            }
                                            ?>

                                            <span class="s-font">Create shift</span>
                                        </li>
                                        <li class="Address-details detail" id="bg1" style="color: rgb(153, 153, 153); background-color: rgb(255, 255, 255);">
                                            <span class="s-font">Assign
                                                Childs</span>
                                        </li>
                                        <li class="Employment-details detail" id="bg2" style="color: rgb(153, 153, 153); background-color: rgb(255, 255, 255);">
                                            <span class="s-font">Summary</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="row" id="form1" style="display: block;">
                                    <div class="col-md-12">
                                        <div class="employees-info">
                                            <div class="add-margin col-md-12">
                                                <h4>Create new shift</h4>
                                                <span class="date-of-weak-shift" id="rota_shift_day_show"></span>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12" id="showDiv">
                                                    <form class="employees-data" onsubmit="return validateform()">
                                                        <div class="row">
                                                            <input type="hidden" id="rota_shift_day_date">
                                                            <label for="firstName" class="col-sm-2 col-form-label">Shift time</label>
                                                            <!-- <div class="col-sm-5"> -->
                                                            <div class="col-auto mb-3">
                                                                <input type="datetime-local" class="col-sm-2 form-control" id="start_time" aria-describedby="emailHelp" value="<?php echo date('Y-m-d'); ?> 09:00" placeholder="">
                                                            </div>
                                                            <div class="col-auto">
                                                                <span class="btew-time">to</span>
                                                            </div>
                                                            <div class="col-auto mb-3">
                                                                <input type="datetime-local" class="col-sm-2 form-control" id="end_time" value="<?php echo date('Y-m-d'); ?> 17:00" aria-describedby="emailHelp" placeholder="">
                                                            </div>
                                                            <!-- </div> -->
                                                        </div>
                                                        <div class="row">
                                                            <label for="lastName" class="col-sm-2 col-form-label">Break duration</label>
                                                            <div class="col-auto">
                                                                <input type="number" id="break_time" class="form-control" placeholder="Minutes">
                                                                <p id="lastNamError">
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <label for="emailAdd" class="col-sm-2 col-form-label">Add a note</label>
                                                            <div class="col-md-4">
                                                                <textarea name="" class="form-control" id="shift_notes" cols="40" rows="5"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <p>This shift is <strong><span id="Shift_duration_show"></span></strong></p>
                                                        </div>
                                                        <div class="form-group col-md-12 d-flex justify-content-end">
                                                            <button type="button" id="next1" onclick="next({id:'next',form:'form',count:1})" class="save-btn nxt1">Next
                                                                step</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="container remove-padding">
                                    <div class="row" id="form2" style="display: none;">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h4>Assign employees to <span id="assign_emp_date"></span>, <span id="shift_time_show"></span></h4>
                                                    <span class="date-of-weak-shift">Find an Childs or a team</span>
                                                </div>
                                                <div class="col-sm-3 col-md-4">
                                                    <select class="form-select form-control" aria-label="Default select example">
                                                        <option selected="" value="1">Search by name
                                                        </option>
                                                        <option value="2">Search by
                                                            group</option>
                                                    </select>
                                                </div>
                                                <div class="col-sm-3 col-md-4">
                                                    <input type="text" class="form-control" onkeyup="search_emp(this)" placeholder="Enter name">
                                                </div>
                                                <div class="col-md-4 d-flex justify-content-end">
                                                    <button type="button" onclick="SelectAll();" class="select-employee">Select
                                                        all</button>
                                                    <button type="button" onclick="DeselectAll();" class="select-employee">Deselect
                                                        all</button>
                                                </div>
                                            </div>
                                            <div class="row employee-name my-4" id="employee_list">
                                            </div>

                                            <div class="row">
                                                <div id="emp_count"></div>
                                            </div>
                                            <div class="address-detail">
                                                <div class="row">
                                                    <div class="col-md-12 d-flex justify-content-end">
                                                        <div class="previous ms-2">
                                                            <button type="button" id="back1" onclick="back({id:'back',form:'form',count:2})" class="previous_btn">Back</button>
                                                        </div>
                                                        <div class="continue_save ms-2">
                                                            <button type="button" class="continue_btn" id="next2" onclick="next({id:'next',form:'form',count:2})">Next
                                                                step
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <form action="" class="row contract-detaial" style="display: none;">
                                                        <div class="form-group col-md-2">
                                                            <label for="exampleInputEmail1">Name</label>
                                                            <div class="name">
                                                                <span>Sagar
                                                                    &nbsp;
                                                                    Pawar</span>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-md-2 my-3">
                                                            <label for="exampleInputEmail1">Entitlement
                                                                unit
                                                                in</label>
                                                            <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="">
                                                        </div>
                                                        <div class="form-group col-md-2 my-3">
                                                            <label for="exampleInputLastname1" style="margin-bottom: 20px;">Full
                                                                time
                                                                annual
                                                                leave
                                                                entitlement
                                                                equivalent</label>
                                                            <input type="text" class="form-control" id="exampleInputLastname1" placeholder="">
                                                        </div>
                                                        <div class="form-group col-md-2 my-3">
                                                            <label for="staticEmail">Leave
                                                                year
                                                                start
                                                                date</label>
                                                            <input type="email" placeholder="" class="form-control" id="staticEmail" value="">
                                                        </div>
                                                        <div class="form-group col-md-1">
                                                            <label class="form-check-label">Copy</label>
                                                            <p class="folder-icon">
                                                                <i class="fa fa-folder-o" aria-hidden="true"></i>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-2 d-flex align-items-center justify-content-center">
                                                            <button class="save-btn">save</button>
                                                            <button class="cancel-btn">cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="container remove-padding">
                                    <div class="row" id="form3" style="display: none;">
                                        <div class="col-md-12">
                                            <div class="employment-details">
                                                <div class="row my-2">
                                                    <div class="col-md-12">
                                                        <h4>Shift summary</h4>
                                                        <p>Shift will be created for <strong> <span id="show_shift_date"></span>, <span id="show_shift_time"></span>.</strong></p>
                                                    </div>
                                                    <div class="col-md-12 d-flex justify-content-end">
                                                        <div class="previous m-2">
                                                            <button type="button" id="back2" onclick="back({id:'back',form:'form',count:3})" class="previous_btn">Back</button>
                                                        </div>
                                                        <div class="continue_save m-2   ">
                                                            <input type="hidden" id="shiftmodelid" />
                                                            <button type="button" class="continue_btn" id="next4" onclick="next({id:'next',form:'form',count:3})">Add shift</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer justify-content-start">
                <button type="button" class="btn-footer-modal" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- shift  Modal start here-->
<div class="modal fade" id="exampleModalShiftModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Shift details</h1>
                <button type="button" class="btn_close" data-bs-dismiss="modal" aria-label="Close">&#10006;</button>
            </div>
            <div class="modal-body shift_detail_modal_body">
                <div class="container remove-padding M-right" id="multiForm" style="display: block;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row" id="form1" style="display: block;">
                                <div class="col-md-12">
                                    <div class="employees-info">
                                        <div class="row">
                                            <div class="col-md-12" id="showDiv">
                                                <form class="employees-data" onsubmit="return validateform()">
                                                    <input type="hidden" id="edit_rota_id">
                                                    <input type="hidden" id="edit_shift_id">
                                                    <input type="hidden" id="edit_user_id">
                                                    <input type="hidden" id="assigned_user_id">
                                                    <input type="hidden" id="rota_shift_id">
                                                    <div class="row my-2">
                                                        <label for="assign_work" class="col-sm-3 col-form-label">Assigned worker</label>
                                                        <div class="col-sm-3 col-md-5 position-rel">
                                                            <div class="position-rel" style="position: relative;">
                                                                <input type="hidden" id="users_update_id">
                                                                <input type="" id="show_emp_name" onclick="showSelect(event)" placeholder="Select employee" class="form-control select_employee_btn"
                                                                    id="staticEmail" value="">
                                                                <span class="position-abs" style="position: absolute;right: 20px; top: 8px; color: #a1a9b3; font-size: 20px;"><i class="fa fa-search" aria-hidden="true"></i></span>
                                                                <ul class="customSelect" id="selectDropdown">
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row my-2">
                                                        <label for="date_of_shift" class="col-sm-3 col-form-label">Shift day</label>
                                                        <div class="col-md-4">
                                                            <input type="date" class="form-control" id="date_of_shift">
                                                        </div>
                                                    </div>
                                                    <div class="row my-2">
                                                        <label for="edit_start_time" class="col-sm-3 col-form-label">Shift time</label>
                                                        <!-- <div class="col-sm-5"> -->
                                                        <div class="col-auto">
                                                            <input type="datetime-local" class="col-sm-2 form-control" id="edit_start_time" aria-describedby="emailHelp" value="<?php echo date('Y-m-d'); ?> 09:00" placeholder="">
                                                        </div>
                                                        <div class="col-auto">
                                                            <span class="btew-time">to</span>
                                                        </div>
                                                        <div class="col-auto">
                                                            <input type="datetime-local" class="col-sm-2 form-control" id="edit_end_time" value="<?php echo date('Y-m-d'); ?> 17:00" aria-describedby="emailHelp" placeholder="">
                                                        </div>
                                                        <!-- </div> -->
                                                    </div>
                                                    <div class="row my-2">
                                                        <label for="edit_break_time" class="col-sm-3 col-form-label">Break duration</label>
                                                        <div class="col-auto">
                                                            <input type="number" class="form-control" id="edit_break_time" placeholder="Last name" min="1" max="60">
                                                            <p id="lastNamError">
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <label for="emailAdd" class="col-sm-12">Add a note</label>
                                                        <div class="col-md-12">
                                                            <textarea name="" class="form-control" id="description" cols="40" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <small class="my-2">Notes are visible to everyone</small>
                                                        <div class="d-flex col-md-4 align-items-center my-3">
                                                            <b>On this day</b>
                                                        </div>
                                                        <div class="col-md-4 my-3" style="opacity: 0.7; font-weight: 500;">No event on this day</div>
                                                        <p>This shift is <strong><span id="duration_of_shift"></span> </strong></p>
                                                    </div>
                                                    <div class="form-group col-md-12 d-flex justify-content-end">
                                                        <button type="button" class="delete_modal_btn">Delete</button>
                                                        <button type="button" id="update_shift" onclick="next({id:'next',form:'form',count:1})" class="save-btn">Update shift</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-start">
                <button type="button" class="close_modal_btn" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- shift modal end here -->
@include('rotaStaff.components.footer')
<script>
    function SelectAll() {
        $('.custom_checkbox').prop('checked', true);
        countEmp();
    }

    function DeselectAll() {
        $('.custom_checkbox').prop('checked', false);
        countEmp();
    }

    function search_emp(input_value) {
        var token = "<?= csrf_token() ?>";
        var rota_id = $('#new_rota').val();
        var search_data = input_value.value;
        $.ajax({
            url: "{{ url('/get-all-users-search') }}",
            type: "Post",
            dataType: 'json',
            data: {
                rota_id: rota_id,
                search_data: search_data,
                _token: token
            },
            success: function(result) {
                console.log(result);
                document.getElementById('employee_list').innerHTML = "";
                var j = 1;
                leave_txt = "";
                for (i = 0; i < result.users.length; i++) {
                    for (k = 0; k < result.leave.length; k++) {
                        for (l = 0; l < result.leave[k].length; l++) {
                            if (result.users[i]['id'] === result.leave[k][l]['user_id']) {
                                console.log("match");
                                if (result.leave[k][l]['leave_type'] === 1) {
                                    var leave_txt = "Annual leave" + " " + result.leave[k][l]['start_date'] + ' - ' + result.leave[k][l]['end_date'];
                                }
                                if (result.leave[k][l]['leave_type'] === 2) {
                                    var leave_txt = "Sickness leave" + " " + result.leave[k][l]['start_date'] + ' - ' + result.leave[k][l]['end_date'];
                                }
                                if (result.leave[k][l]['leave_type'] === 3) {
                                    var leave_txt = "Latness leave" + " " + result.leave[k][l]['start_date'] + ' - ' + result.leave[k][l]['end_date'];
                                }
                                if (result.leave[k][l]['leave_type'] === 4) {
                                    var leave_txt = "Other leave" + " " + result.leave[k][l]['start_date'] + ' - ' + result.leave[k][l]['end_date'];
                                }

                            } else {
                                leave_txt = "";
                            }

                        }
                    }
                    console.log(leave_txt);

                    document.querySelector('#employee_list').insertAdjacentHTML(
                        'afterbegin',

                        `<div class="col-md-6">
                                        <input type="checkbox" class="custom_checkbox" name="mycheckboxes" value="${result.users[i]['id']}" id="select_checkbox_${j}" onclick="countEmp(${result.users.length})">
                                        <label for="select_checkbox_${j}" class="name">

                                            <p>${result.users[i]['name']}</p>
                                            <p>${leave_txt}</p>
                                            
                                          <p class="select_tick"><span class="right_tick"><i class="fa fa-check" aria-hidden="true"></i></span></p>

                                        </label>

                                    </div>`
                    );
                    j++;

                }
            }

        });
    }

    function renamedata(id, name, status) {
        $('#rota_name_model').text(name);
        $('#rota_id').val(id);
        $('#rota_status').val(status);
        $('#exampleModalPublish').modal('show');
    }

    function hasWhiteSpace(s) {
        return /\s/g.test(s);
    }

    function showEmployeeName(id, name) {
        document.getElementById('users_update_id').value = id;
        document.getElementById('show_emp_name').value = name;
    }

    function view_user_data(shift_id, user_id) {
        var token = "<?= csrf_token() ?>";
        var rota_id = $('#new_rota').val();
        $.ajax({
            url: "{{ url('/get-all-users') }}",
            type: "Post",
            data: {
                rota_id: rota_id,
                _token: token
            },
            dataType: 'json',
            success: function(result) {
                console.log(result.users);
                for (i = 0; i < result.users.length; i++) {
                    var fullName = result.users[i]['name'];
                    var get_name = hasWhiteSpace(fullName);
                    console.log(get_name);
                    if (get_name == true) {
                        var names = fullName.split(' ');
                        var fname = fullName[0].charAt(0);
                        var lname = fullName[1].charAt(0);
                        shortname = fname + lname;
                    }
                    if (get_name == false) {
                        fname = fullName.charAt(0);
                        shortname = fname;
                    }

                    if (result.users[i]['id'] == user_id) {
                        document.getElementById('users_update_id').value = result.users[i]['id'];
                        document.getElementById('show_emp_name').value = result.users[i]['name'];
                    }
                    document.querySelector('#selectDropdown').insertAdjacentHTML(
                        'afterbegin',
                        `<li class="d-flex customSelectItem">
                                    <input id="employee1" class="d-none" type="radio" name="selectEmployee">
                                    <div class="labelElement p-2">
                                        <label for="employee1" class="d-flex align-items-center" onclick="showEmployeeName(${result.users[i]['id']} , '${result.users[i]['name']}');">
                                            <div class="firstLastLetter">${shortname.toUpperCase()}</div>
                                            <div class="fullName" id="fullName">${result.users[i]['name']}</div>
                                            <div>
                                                <span class="customRadio"><span class="dot"></span></span>
                                            </div>
                                        </label>
                                    </div>
                                </li>`
                    );
                }
            }
        });
        $.ajax({
            url: "{{ url('/edit_shift_data_get') }}",
            type: "post",
            dataType: 'json',
            data: {
                rota_id: rota_id,
                user_id: user_id,
                shift_id: shift_id,
                _token: token
            },
            success: function(result) {
                console.log(result);
                console.log(result[0]['rota_day_date']);
                $('#edit_rota_id').val(rota_id);
                $('#edit_shift_id').val(shift_id);
                $('#edit_user_id').val(user_id);
                $('#assigned_user_id').val(result[0]['assigned_id']);
                $('#rota_shift_id').val(result[0]['rota_shift_id']);
                $('#date_of_shift').val(result[0]['rota_day_date']);
                $('#edit_start_time').val(result[0]['shift_start_time']);
                $('#edit_end_time').val(result[0]['shift_end_time']);
                $('#edit_break_time').val(result[0]['break']);
                $('#description').val(result[0]['description']);

                var start = moment(result[0]['shift_start_time'], "HH:mm:ss").format("HH:mm");
                var end = moment(result[0]['shift_end_time'], "HH:mm:ss").format("HH:mm");

                var startTime = moment(result[0]['shift_start_time']);
                var endTime = moment(result[0]['shift_end_time']);
                var duration = moment.duration(endTime.diff(startTime));
                console.log(duration);
                var hours_for_edit = parseInt(duration.asHours());
                document.getElementById('duration_of_shift').innerHTML = hours_for_edit + " hour with " + result[0]['break'] + " mins break";
            }
        });
        $('#exampleModalShiftModal').modal('show');
    }

    function view_shift_model(date1, date2, id) {

        document.getElementById('emp_count').innerHTML = "";
        document.getElementById('employee_list').innerHTML = "";
        var token = "<?= csrf_token() ?>";
        var rota_id = $('#new_rota').val();
        $.ajax({
            url: "{{ url('/get-all-users') }}",
            type: "Post",
            data: {
                rota_id: rota_id,
                _token: token
            },
            dataType: 'json',
            success: function(result) {
                console.log(result);
                var j = 1;
                leave_txt = "";
                for (i = 0; i < result.users.length; i++) {
                    for (k = 0; k < result.leave.length; k++) {
                        for (l = 0; l < result.leave[k].length; l++) {
                            if (result.users[i]['id'] === result.leave[k][l]['user_id']) {
                                console.log("match");
                                if (result.leave[k][l]['leave_type'] === 1) {
                                    var leave_txt = "Annual leave" + " " + result.leave[k][l]['start_date'] + ' - ' + result.leave[k][l]['end_date'];
                                }
                                if (result.leave[k][l]['leave_type'] === 2) {
                                    var leave_txt = "Sickness leave" + " " + result.leave[k][l]['start_date'] + ' - ' + result.leave[k][l]['end_date'];
                                }
                                if (result.leave[k][l]['leave_type'] === 3) {
                                    var leave_txt = "Latness leave" + " " + result.leave[k][l]['start_date'] + ' - ' + result.leave[k][l]['end_date'];
                                }
                                if (result.leave[k][l]['leave_type'] === 4) {
                                    var leave_txt = "Other leave" + " " + result.leave[k][l]['start_date'] + ' - ' + result.leave[k][l]['end_date'];
                                }

                            } else {
                                leave_txt = "";
                            }

                        }
                    }
                    console.log(leave_txt);

                    document.querySelector('#employee_list').insertAdjacentHTML(
                        'afterbegin',
                        `<div class="col-md-6">
                                        <input type="checkbox" class="custom_checkbox" name="mycheckboxes" value="${result.users[i]['id']}" id="select_checkbox_${j}" onclick="countEmp(${result.users.length})">
                                        <label for="select_checkbox_${j}" class="name">
                                            <p>${result.users[i]['name']}</p>
                                            <p>${leave_txt}</p>  
                                        <p class="select_tick"><span class="right_tick"><i class="fa fa-check" aria-hidden="true"></i></span></p>

                                        </label>
                                    </div>`
                    );
                    j++;

                }
            }
        });
        $("#form1").css("display", "block")
        $('#form1select').trigger("reset");
        document.getElementById('rota_shift_day_show').innerHTML = date1;
        document.getElementById('assign_emp_date').innerHTML = date2;
        document.getElementById('show_shift_date').innerHTML = date2;
        $('#shiftmodelid').val(id);
        $('#rota_shift_day_date').val(date1);
        $('#exampleModalAddShift').modal('show');
        // $('#exampleModalAddShift').modal('show');
    }
    $(document).ready(function() {

        const selectElementEdit = document.getElementById('edit_break_time');
        const duration_of_shift = document.getElementById('duration_of_shift');
        duration_of_shift.innerHTML = "8 hour with no break";
        selectElementEdit.addEventListener('keyup', (event) => {
            var show_shift_start_time_edit = document.getElementById('edit_start_time').value;
            var show_shift_end_time_edit = document.getElementById('edit_end_time').value;
            var show_break_time_edit = document.getElementById('edit_break_time').value;
            a = moment(show_shift_start_time_edit);
            b = moment(show_shift_end_time_edit);
            var show_hour_edit = a.diff(b, 'hours');
            console.log(a.diff(b, 'hours')) // 745
            duration_of_shift.innerHTML = "";
            duration_of_shift.innerHTML = `${Math.abs(show_hour_edit)} hours incl. ${show_break_time_edit} mins break`;
        });

        const selectStartEdit = document.getElementById('edit_start_time');
        duration_of_shift.innerHTML = "8 hour with no break";
        selectStartEdit.addEventListener('change', (event) => {
            var show_shift_start_time_edit = document.getElementById('edit_start_time').value;
            var show_shift_end_time_edit = document.getElementById('edit_end_time').value;
            var show_break_time_edit = document.getElementById('edit_break_time').value;
            a = moment(show_shift_start_time_edit);
            b = moment(show_shift_end_time_edit);
            var show_hour_edit = a.diff(b, 'hours');
            console.log(a.diff(b, 'hours')) // 745
            duration_of_shift.innerHTML = "";
            if (show_break_time_edit == "") {
                duration_of_shift.innerHTML = `${Math.abs(show_hour_edit)} hours incl. no break`;
            } else {
                duration_of_shift.innerHTML = `${Math.abs(show_hour_edit)} hours incl. ${show_break_time_edit} mins break`;
            }
        });

        const selectEndEdit = document.getElementById('edit_end_time');
        duration_of_shift.innerHTML = "8 hour with no break";
        selectEndEdit.addEventListener('change', (event) => {
            var show_shift_start_time_edit = document.getElementById('edit_start_time').value;
            var show_shift_end_time_edit = document.getElementById('edit_end_time').value;
            var show_break_time_edit = document.getElementById('edit_break_time').value;
            a = moment(show_shift_start_time_edit);
            b = moment(show_shift_end_time_edit);
            var show_hour_edit = a.diff(b, 'hours');
            console.log(a.diff(b, 'hours')) // 745
            duration_of_shift.innerHTML = "";
            if (show_break_time_edit == "") {
                duration_of_shift.innerHTML = `${Math.abs(show_hour_edit)} hours incl. no break`;
            } else {
                duration_of_shift.innerHTML = `${Math.abs(show_hour_edit)} hours incl. ${show_break_time_edit} mins break`;
            }
        });

        const selectElement = document.getElementById('break_time');
        const result = document.getElementById('Shift_duration_show');
        result.innerHTML = "8 hour with no break";
        selectElement.addEventListener('keyup', (event) => {
            var show_shift_start_time = document.getElementById('start_time').value;
            var show_shift_end_time = document.getElementById('end_time').value;
            var show_break_time = document.getElementById('break_time').value;
            a = moment(show_shift_start_time);
            b = moment(show_shift_end_time);
            var show_hour = a.diff(b, 'hours');
            console.log(a.diff(b, 'hours')) // 745
            result.innerHTML = "";
            result.innerHTML = `${Math.abs(show_hour)} hours incl. ${show_break_time} mins break`;
        });
        const selectStart = document.getElementById('start_time');
        selectStart.addEventListener('change', (event) => {
            var show_shift_start_time = document.getElementById('start_time').value;
            var show_shift_end_time = document.getElementById('end_time').value;
            var show_break_time = document.getElementById('break_time').value;
            a = moment(show_shift_start_time);
            b = moment(show_shift_end_time);
            var show_hour = a.diff(b, 'hours');
            console.log(a.diff(b, 'hours')) // 745
            result.innerHTML = "";
            if (show_break_time == "") {
                result.innerHTML = `${Math.abs(show_hour)} hours incl. no break`;
            } else {
                result.innerHTML = `${Math.abs(show_hour)} hours incl. ${show_break_time} mins break`;
            }

        });

        const selectEnd = document.getElementById('end_time');
        selectEnd.addEventListener('change', (event) => {
            var show_shift_start_time = document.getElementById('start_time').value;
            var show_shift_end_time = document.getElementById('end_time').value;
            var show_break_time = document.getElementById('break_time').value;
            a = moment(show_shift_start_time);
            b = moment(show_shift_end_time);
            var show_hour = a.diff(b, 'hours');
            console.log(a.diff(b, 'hours')) // 745
            result.innerHTML = "";
            if (show_break_time == "") {
                result.innerHTML = `${Math.abs(show_hour)} hours incl. no break`;
            } else {
                result.innerHTML = `${Math.abs(show_hour)} hours incl. ${show_break_time} mins break`;
            }
        });

        var first_btn = document.getElementById("next1");
        var second_btn = document.getElementById("next2");

        var shift_start_time = document.getElementById('start_time').value;
        shift_start_time = moment(shift_start_time).format("HH:mm")

        var shift_end_time = document.getElementById('end_time').value;
        shift_end_time = moment(shift_end_time).format("HH:mm")

        first_btn.addEventListener('click', (event) => {
            document.getElementById('shift_time_show').innerHTML = shift_start_time + '-' + shift_end_time;
        });

        second_btn.addEventListener('click', (event) => {
            document.getElementById('show_shift_time').innerHTML = shift_start_time + '-' + shift_end_time;
        });


        $('#next4').on('click', function() {
            var rota_id = $('#new_rota').val();
            var rota_shift_day_date = $('#rota_shift_day_date').val();
            var start_date = $("#start_time").val();
            var end_date = $("#end_time").val();
            var break_time = $("#break_time").val();
            var shift_notes = $("#shift_notes").val();
            var edit_rota_id = $('#edit_rota_id').val();
            var shiftmodelid = $('#shiftmodelid').val();
            let user_ids = new Array();
            var checkedValue = null;
            var inputElements = document.getElementsByClassName('custom_checkbox');
            for (var i = 0; inputElements[i]; ++i) {
                if (inputElements[i].checked) {
                    checkedValue = inputElements[i].value;
                    user_ids.push(checkedValue);
                }
            }
            console.log("emp_id=>" + user_ids);
            var token = "<?= csrf_token() ?>";
            $.ajax({
                url: "{{ url('/assign_rota_users') }}",
                type: "post",
                dataType: 'json',
                data: {
                    rota_id: rota_id,
                    user_ids: user_ids,
                    rota_shift_day_date: rota_shift_day_date,
                    start_date: start_date,
                    end_date: end_date,
                    break_time: break_time,
                    shift_notes: shift_notes,
                    edit_rota_id: edit_rota_id,
                    _token: token
                },
                success: function(result) {
                    console.log(result.rotaShift);
                    console.log(result.user_name);
                    console.log(result.user_name.length);
                    for (i = 0; i < (result.user_name).length; i++) {

                        var start = moment(result.rotaShift[0]['shift_start_time']).format("HH");

                        var end = moment(result.rotaShift[0]['shift_end_time']).format("HH");


                        let a = moment(result.rotaShift[0]['shift_start_time']).format("DDMMYYYY");

                        let b = moment(result.rotaShift[0]['shift_end_time']).format("DDMMYYYY");

                        console.log(a);

                        console.log('time check', a == b, typeof a)

                        console.log('end, start', end, start)

                        if (a === b) {

                            result.user_name[i][0]['hours'] = parseInt(end - start);

                            result.user_name[i][0]['start'] = start;

                        } else {

                            let newUser;

                            if (result.user_name[i].length === 1) {

                                result.user_name[i][0]['start'] = start;

                                result.user_name[i][0]['hours'] = 24 - start;

                                newUser = JSON.parse(JSON.stringify(result.user_name[i][0]));

                            } else {

                                result.user_name[i][1]['start'] = start;

                                result.user_name[i][1]['hours'] = 24 - start;

                                newUser = JSON.parse(JSON.stringify(result.user_name[i][1]));

                            }

                            console.log(result.user_name[i][0])

                            newUser['start'] = 0;

                            newUser['hours'] = end;



                            if (result.user_name.length === i + 1) {

                                result.user_name[i + 1].push(newUser)

                            } else {

                                result.user_name[i + 1].unshift(newUser);

                            }

                            console.log(result.user_name)

                        }

                        var name = result.user_name[i][0]['name'];

                        var get_name = hasWhiteSpace(name);



                        console.log(get_name);

                        if (get_name == true) {

                            var names = name.split(' ');

                            var fname = names[0].charAt(0);

                            var lname = names[1].charAt(0);

                            shortname = fname + lname;

                        }

                        if (get_name == false) {

                            fname = name.charAt(0);
                            shortname = fname;

                        }

                        function loopDataFunction() {

                            let loopData = '';

                            for (let k = 0; k < 24; k++) {

                                loopData += `<div class="hour_box" style="width: calc(4.16667%);"></div>`;

                            }

                            return loopData;

                        }



                        function userTimeData() {

                            let user;
                            result.user_name[i].forEach(oneUser => {

                                console.log(oneUser.hours, typeof oneUser.hours)

                                user = `<button type="button" class="shift_timing_btn" onclick="view_user_data(` + result.rotaShift[0]['id'] + `,` + oneUser['id'] + `) "style="width: ` + oneUser.hours * 4.16667 + `%;  left: ` + oneUser.start * 4.16667 + `%;" data-testid="Shift card">

                                            <div class="d-flex align-items-center">

                                                <div class="">

                                                    <div class="name_of_member">

                                                        <div class="">` + shortname + `</div>

                                                    </div>

                                                </div>

                                            <div class="">

                                                    <div class="d-flex align-items-center">

                                                        <div class="name_of_person">${oneUser.name}</div>

                                                        <div class="shift_timeing_duration">${moment(result.rotaShift[0]['shift_start_time']).format("HH:mm")+'-'+moment(result.rotaShift[0]['shift_end_time']).format("HH:mm")}</div>

                                                        
                                                    </div>

                                                    <div class=""></div>

                                                </div>

                                            </div>

                                        </button>`
                            });
                            return user;

                        }

                        document.querySelector('#show_user_record' + shiftmodelid).insertAdjacentHTML(

                            'afterbegin',

                            `<div class="w_full" style="">

                                        ${loopDataFunction()}

                                        <!-- Button shift modal -->

                                        ${userTimeData()}

                                        <!-- Modal -->

                                    </div>`

                        );
                    }
                    document.getElementById('shift_count').innerHTML = result.user_name.length;
                    document.getElementById('shift_count1').innerHTML = result.user_name.length;

                }
            });
            location.reload();
            // $('#exampleModalAddShift').modal('hide'); 
        });
        $('#publish_unpublish_btn').on('click', function() {
            var rota_id = $('#rota_id').val();
            var rota_status = $('#rota_status').val();
            var token = "<?= csrf_token() ?>";
            $.ajax({
                url: "{{ url('/publish_unpublish_rota') }}",
                type: "post",
                dataType: 'text',
                data: {
                    rota_id: rota_id,
                    rota_status: rota_status,
                    _token: token
                },
                success: function(result) {
                    console.log(result);
                    location.reload();
                }
            });
        });
        var token = "<?= csrf_token() ?>";
        $('#update_shift').on('click', function() {
            var edit_rota_id = $('#edit_rota_id').val();
            var edit_shift_id = $('#edit_shift_id').val();
            var updtate_date_of_shift = $('#date_of_shift').val();
            var update_shift_start_time = $('#edit_start_time').val();
            var update_shift_end_time = $('#edit_end_time').val();
            var update_break = $('#edit_break_time').val();
            var description = $('#description').val();
            var update_user_id = $('#users_update_id').val();
            var assigned_user_id = $('#assigned_user_id').val();
            var rota_shift_id = $('#rota_shift_id').val();
            $.ajax({
                url: "{{ url('/update-shift-data') }}",
                type: "post",
                dataType: 'json',
                data: {
                    edit_rota_id: edit_rota_id,
                    edit_shift_id: edit_shift_id,
                    updtate_date_of_shift: updtate_date_of_shift,
                    update_shift_start_time: update_shift_start_time,
                    update_shift_end_time: update_shift_end_time,
                    update_break: update_break,
                    description: description,
                    update_user_id: update_user_id,
                    rota_shift_id: rota_shift_id,
                    assigned_user_id: assigned_user_id,
                    _token: token
                },
                success: function(result) {
                    console.log(result);
                    if (result === 1) {
                        $('#exampleModalShiftModal').modal('hide');
                        location.reload();
                    }
                }
            });
        });
    });

    function showSelect(ev) {
        ev.stopPropagation();
        document.getElementById('selectDropdown').classList.toggle('show_select');
    }
    window.onclick = function(event) {
        const el = document.getElementById('selectDropdown');
        if (el.classList.contains("show_select")) {
            el.classList.remove("show_select");
        }
    }

    function countEmp(TotalEmp) {
        var inputElement = document.getElementsByClassName('custom_checkbox');
        var countE = "";
        for (var i = 0; inputElement[i]; ++i) {
            if (inputElement[i].checked) {
                countE++;
            }
        }
        var hour_emp = document.getElementById('emp_count');
        var show_shift_start_time = document.getElementById('start_time').value;
        var show_shift_end_time = document.getElementById('end_time').value;
        a = moment(show_shift_start_time);
        b = moment(show_shift_end_time);
        var show_hour = a.diff(b, 'hours');
        var next2 = document.getElementById('next2');
        if (countE == "") {
            hour_emp.innerHTML = "";
            next2.disabled = true;
            next2.classList.add("disable_btn_nxt2");
        } else {
            hour_emp.innerHTML = `<p>This shift has <strong>${countE} Childs </strong>working <strong>${Math.abs(show_hour*countE)} hrs</strong></p>`;

            next2.removeAttribute("disabled");
            next2.classList.remove("disable_btn_nxt2");
        }
    }
</script>