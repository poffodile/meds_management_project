@extends('frontEnd.layouts.master')
@section('title','Care Task')
@section('content')
@include('frontEnd.roster.common.roster_header')
<main class="page-content">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-lg-10">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex gap-3 align-items-center">
                            <div>
                                <button class="borderBtn" onclick="history.back()"><i class='bx  bx-arrow-left f18'></i>Back</button>
                            </div>
                            <div>
                                <h1 class="mainTitlep mb-0">Add Care Task</h1>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt20">
                    <div class="col-lg-12">
                        <form id="careTaskForm">
                            <div class="emergencyMain p-4 mb25">
                                <h5 class="h5Head">Task Basics</h5>

                                <div class="carer-form">
                                    <div class="row mb-4 mt20">
                                        <div class="col-md-12">
                                            <label>Task Title *</label>
                                            <input type="text" class="form-control checkCareTask" placeholder="e.g. Monthly Supervision - John Smith" id="task_title" name="task_title" value="<?php if(!empty($clientCareTask) && $clientCareTask->task_title !=''){ echo $clientCareTask->task_title;} ?>">
                                        </div>
                                        <div class="col-lg-6  m-t-10">
                                            <label>Task Type *</label>
                                            <select class="form-control checkCareTask" id="task_type_id" name="task_type_id">
                                            @foreach($task_type as $taskVal)
                                                <option value="{{$taskVal->id}}" <?php if(!empty($clientCareTask) && $clientCareTask->task_type_id == $taskVal->id){echo 'selected';}?>>{{$taskVal->title}}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-6  m-t-10">
                                            <label>Task Category *</label>
                                            <select class="form-control checkCareTask" id="task_category_id" name="task_category_id">
                                            @foreach($task_category as $categoryVal)
                                                <option value="{{$categoryVal->id}}" <?php if(!empty($clientCareTask) && $clientCareTask->task_category_id == $categoryVal->id){echo 'selected';}?>>{{$categoryVal->title}}</option>
                                            @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-12 m-t-10">
                                            <label> Priority Level *</label>
                                            <select class="form-control checkCareTask" id="priority" name="priority">
                                                <option value="Low" <?php if(!empty($clientCareTask) && $clientCareTask->priority === 'Low'){echo 'selected';}?>>Low</option>
                                                <option value="Medium" <?php if(!empty($clientCareTask) && $clientCareTask->priority === 'Medium'){echo 'selected';}else{if(empty($clientCareTask)){echo 'selected';}}?>>Medium</option>
                                                <option value="High" <?php if(!empty($clientCareTask) && $clientCareTask->priority === 'High'){echo 'selected';}?>>High</option>
                                                <option value="Critical" <?php if(!empty($clientCareTask) && $clientCareTask->priority === 'Critical'){echo 'selected';}?>>Critical</option>
                                            </select>
                                        </div>

                                    </div>


                                </div>

                            </div>
                            <div class="emergencyMain p-4 mb25">
                                <h5 class="h5Head">Client & Care Plan</h5>
                                <div class="carer-form">
                                    <div class="row mb-4 mt20">

                                        <div class="col-lg-12">
                                            <label>Client*</label>
                                            <select class="form-control checkCareTask" id="client_id" name="client_id">
                                            @foreach($child as $childVal)
                                                <option value="{{$childVal->id}}" <?php if(!empty($clientCareTask) && $clientCareTask->client_id == $childVal->id){echo 'selected';}else{if($childVal->id == $client_id){echo 'selected';}}?>>{{$childVal->name}}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-12  m-t-10">
                                            <label>Care Plan *</label>
                                            <select class="form-control checkCareTask" id="care_plan_id" name="care_plan_id">
                                                <option value="1" <?php if(!empty($clientCareTask) && $clientCareTask->care_plan_id == 1){echo 'selected';}?>>Personal Care</option>
                                                <option value="2" <?php if(!empty($clientCareTask) && $clientCareTask->care_plan_id == 2){echo 'selected';}?>>Spot Check</option>
                                            </select>
                                        </div>
                                    </div>

                                </div>

                            </div>
                            <div class="emergencyMain p-4 mb25">
                                <h5 class="h5Head">Scheduling
                                </h5>
                                <div class="carer-form">
                                    <div class="row mb-4 mt20">

                                        <div class="col-lg-6 ">
                                            <label>Frequency *</label>
                                            <select class="form-control checkCareTask" id="frequency" name="frequency">
                                                <option value="Once" <?php if(!empty($clientCareTask) && $clientCareTask->frequency === 'Once'){echo 'selected';}?>>Once</option>
                                                <option value="Daily" <?php if(!empty($clientCareTask) && $clientCareTask->frequency === 'Daily'){echo 'selected';}?>>Daily</option>
                                                <option value="Twice Daily" <?php if(!empty($clientCareTask) && $clientCareTask->frequency === 'Twice Daily'){echo 'selected';}?>>Twice Daily</option>
                                                <option value="Three Times Daily" <?php if(!empty($clientCareTask) && $clientCareTask->frequency === 'Three Times Daily'){echo 'selected';}?>>Three Times Daily</option>
                                                <option value="Four Times Daily" <?php if(!empty($clientCareTask) && $clientCareTask->frequency === 'Four Times Daily'){echo 'selected';}?>>Four Times Daily</option>
                                                <option value="Weekly" <?php if(!empty($clientCareTask) && $clientCareTask->frequency === 'Weekly'){echo 'selected';}?>>Weekly</option>
                                                <option value="Custom" <?php if(!empty($clientCareTask) && $clientCareTask->frequency === 'Custom'){echo 'selected';}?>>Custom</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-6">
                                            <label>Location</label>
                                            <select class="form-control" id="location" name="location">
                                                <option value="1" <?php if(!empty($clientCareTask) && $clientCareTask->location == 1){echo 'selected';}?>>Home</option>
                                                <option value="2" <?php if(!empty($clientCareTask) && $clientCareTask->location == 2){echo 'selected';}?>>Community</option>
                                                <option value="3" <?php if(!empty($clientCareTask) && $clientCareTask->location == 3){echo 'selected';}?>>Facility</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-4  m-t-10">
                                            <label>Scheduled Date *</label>
                                            <input type="date" class="form-control checkCareTask" id="scheduled_date" name="scheduled_date" value="<?php if(!empty($clientCareTask) && $clientCareTask->scheduled_date != ''){echo date('Y-m-d',strtotime($clientCareTask->scheduled_date));}?>">
                                        </div>
                                        <div class="col-lg-4  m-t-10">
                                            <label>Scheduled Time</label>
                                            <input type="time" class="form-control" id="scheduled_time" name="scheduled_time" value="<?php if(!empty($clientCareTask) && $clientCareTask->scheduled_time != ''){echo date('H:i',strtotime($clientCareTask->scheduled_time));}?>">
                                        </div>
                                        <div class="col-lg-4  m-t-10">
                                            <label>Duration (minutes)</label>
                                            <input type="number" class="form-control" placeholder="30"id="duration" name="duration" value="<?php if(!empty($clientCareTask) && $clientCareTask->duration != ''){echo $clientCareTask->duration;}?>">
                                        </div>
                                    </div>

                                </div>

                            </div>
                            <div class="emergencyMain p-4 mb25">
                                <h5 class="h5Head">Assignment</h5>
                                <div class="carer-form">
                                    <div class="row mb-4 mt20">

                                        <div class="col-lg-12">

                                            <label>Assigned Carer</label>
                                            <select class="form-control" id="carer_id" name="carer_id">
                                                <option value="0" selected disabled>Please select Carer</option>
                                                @foreach($carer as $carerVal)
                                                <option value="{{$carerVal->id}}" <?php if(!empty($clientCareTask) && $clientCareTask->carer_id == $carerVal->id){echo 'selected';}?>>{{$carerVal->name}}</option>
                                                @endforeach
                                            </select>
                                            <small class="formIns">Only active carers are shown </small>


                                        </div>
                                        <!-- <div class="col-lg-12  m-t-10">

                                            <label>Link to Visit (Time-Specific)</label>
                                            <select class="form-control" id="visit_id" name="visit_id">
                                                <option value="1" <?php if(!empty($clientCareTask) && $clientCareTask->visit_id == 1){echo 'selected';}?>>Personal Care</option>
                                                <option value="2" <?php if(!empty($clientCareTask) && $clientCareTask->visit_id == 2){echo 'selected';}?>>Spot Check</option>
                                            </select>
                                            <small class="formIns">Task will appear for carer during this visit </small>

                                        </div> -->
                                        <div class="col-lg-12  m-t-10">

                                            <label>Link to Shift</label>
                                            <select class="form-control" id="shift_id" name="shift_id">
                                            
                                            </select>
                                            <small class="formIns">Task will appear for carer during this shift </small>
                                        </div>
                                        <a href="{{url('/roster/schedule-shift')}}" class="btn blackBtn" style="color:fff; float: right; margin-inline-end: 15px;" target="_blank">Add Shift</a>
                                    </div>

                                </div>

                            </div>
                            <!-- <div class="emergencyMain p-4 mb25">
                                <h5 class="h5Head"> <i class="careRiskIcon bx  bx-alert-triangle me-2"></i> Risk & Safeguarding</h5>
                                <div class="carer-form">
                                    <div class="row mb-4 mt20">

                                        <div class="col-lg-12">
                                            <label>Risk Level</label>
                                            <select class="form-control" id="risk_level_id" name="risk_level_id">
                                                <option value="1" <?php if(!empty($clientCareTask) && $clientCareTask->risk_level_id == 1){echo 'selected';}?>>Assessment</option>
                                                <option value="2" <?php if(!empty($clientCareTask) && $clientCareTask->risk_level_id == 2){echo 'selected';}?>>Spot Check</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-12 m-t-10">
                                            <div class="checkboxp">
                                                <input type="checkbox" class="careTaskCheckBox" id="safeguarding" name="safeguarding" <?php if(!empty($clientCareTask) && $clientCareTask->safeguarding == 1){echo 'value="1" checked';}else{echo 'value="0"';}?>>
                                                <label for="safeguarding">
                                                    Safeguarding Risk (Will notify Registered Manager)
                                                </label>
                                            </div>

                                            <div class="checkboxp">
                                                <input type="checkbox" class="careTaskCheckBox" id="twoPerson" name="two_person" <?php if(!empty($clientCareTask) && $clientCareTask->two_person == 1){echo 'value="1" checked';}else{echo 'value="0"';}?>>
                                                <label for="twoPerson">
                                                    Requires Two Person Support
                                                </label>
                                            </div>

                                            <div class="checkboxp mb-0">
                                                <input type="checkbox" class="careTaskCheckBox" id="ppeRequired" name="ppe_required" <?php if(!empty($clientCareTask) && $clientCareTask->ppe_required == 1){echo 'value="1" checked';}else{echo 'value="0"';}?>>
                                                <label for="ppeRequired">
                                                    PPE Required
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-lg-12  m-t-10">
                                            <label>Risk Mitigation Notes</label>
                                            <textarea class="form-control" id="risk_notes" name="risk_notes" rows="3" cols="20" placeholder="Provide detailed instructions for completing this task..."><?php if(!empty($clientCareTask) && $clientCareTask->risk_notes != ''){echo $clientCareTask->risk_notes;}?></textarea>
                                        </div>

                                    </div>

                                </div>

                            </div> -->
                            <div class="emergencyMain p-4 mb25">
                                <h5 class="h5Head">Task Details</h5>
                                <div class="carer-form">
                                    <div class="row mb-4 mt20">
                                        <div class="col-lg-12">
                                            <label>Task Description</label>
                                            <textarea class="form-control" id="task_description" name="task_description" rows="3" cols="20" placeholder="Provide detailed instructions for completing this task..."><?php if(!empty($clientCareTask) && $clientCareTask->task_description != ''){echo $clientCareTask->task_description;}?></textarea>
                                        </div>

                                    </div>

                                </div>

                            </div>
                            <input type="hidden" name="id" value="<?php if(!empty($clientCareTask) && $clientCareTask->id != ''){echo $clientCareTask->id;}?>">
                            <div class="col-lg-12">
                                <div class="d-flex justify-content-end gap-3">
                                    <button class="borderBtn">Cancel</button>
                                    <button class="bgBtn blackBtn saveCareTask" type="button"><i class='bx  bx-save f18'></i> Create Task</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script src="{{ url('public/js/roster/client/client_details.js')}}" defer></script>
<script>
    var careTaskFormSaveUrl = "{{url('roster/care-task-save')}}";
</script>
<script>
    $(document).ready(function(){
        var taskcarer_id = $("#carer_id option:selected").val();
        var selctedShift_id = 0;
        <?php if(!empty($clientCareTask) && $clientCareTask->shift_id !=''){?>
            selctedShift_id = '{{$clientCareTask->shift_id}}';
        <?php }?>
        if(taskcarer_id != 0){
            show_shift(taskcarer_id,selctedShift_id);
        }
    });
    $(document).on('change',"#carer_id",function(){
        show_shift($("#carer_id option:selected").val());
    });
    function show_shift(carer_id,selctedShift_id){
        $.ajax({
            type: "POST",
            url: "{{url('roster/get-carer-shifts')}}",
            data: {carer_id:carer_id,_token:'{{csrf_token()}}'},
            success: function (response) {
                console.log(response);
                if (typeof isAuthenticated === "function") {
                    if (isAuthenticated(response) == false) {
                        return false;
                    }
                } 
                if(response.success === true){
                    var data = response.data;
                    $("#shift_id").html('');
                    data.forEach(function(val){
                        let start_date = moment(val.start_date).format('MMM DD, YYYY');

                        let start_time = moment(val.start_time, "HH:mm:ss").format('HH:mm');

                        let end_time = moment(val.end_time, "HH:mm:ss").format('HH:mm');
                        var selected = '';
                        if(selctedShift_id){
                            selected = 'selected';
                        }
                        $("#shift_id").append(
                            `<option value="${val.id}" ${selected}>${start_date} - ${start_time} - ${end_time}</option>`
                        );
                    });
                }
            },
            error: function (xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + error);
            }
        });
    }
</script>
    @endsection

</main>